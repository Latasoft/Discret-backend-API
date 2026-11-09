<?php

namespace App\Http\Controllers;

use App\Mail\ReservaConfirmadaMail;
use App\Models\EnvioProgramado;
use App\Models\ApiToken;
use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\TipoHabitacion;
use App\Models\Valoracion;
use App\Support\CurrentMotel;
use App\Support\RoomStatusSynchronizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReservaController extends Controller
{
    private array $estadosActivos = ['pendiente', 'confirmada', 'ocupada'];

    public function index(Request $request)
    {
        $idMotel = CurrentMotel::id($request);
        RoomStatusSynchronizer::sync($idMotel);

        return Reserva::query()
            ->with(['cliente', 'habitacion.tipoHabitacion', 'tipoHabitacion', 'motel', 'valoracion', 'enviosProgramados'])
            ->where('id_motel', $idMotel)
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->estado))
            ->when($request->filled('buscar'), function ($query) use ($request) {
                $buscar = $request->buscar;

                $query->where(function ($subQuery) use ($buscar) {
                    $subQuery
                        ->where('codigo_reserva', 'like', "%{$buscar}%")
                        ->orWhere('nombre_cliente', 'like', "%{$buscar}%")
                        ->orWhere('telefono_cliente', 'like', "%{$buscar}%")
                        ->orWhere('correo_cliente', 'like', "%{$buscar}%");
                });
            })
            ->latest()
            ->paginate(20);
    }

    public function mine(Request $request)
    {
        $cliente = $request->attributes->get('api_actor');
        $idMotel = CurrentMotel::id($request);

        return Reserva::query()
            ->with(['habitacion.tipoHabitacion', 'tipoHabitacion', 'valoracion', 'enviosProgramados'])
            ->where('id_motel', $idMotel)
            ->where('cliente_id', $cliente->id)
            ->latest()
            ->paginate(20);
    }

    public function store(Request $request)
    {
        $idMotel = CurrentMotel::id($request);
        $clienteAutenticado = $this->clienteAutenticado($request, $idMotel);

        $data = $request->validate([
            'cliente_id' => ['nullable', Rule::exists('clientes', 'id')->where('id_motel', $idMotel)],
            'habitacion_id' => ['nullable', Rule::exists('habitaciones', 'id')->where('id_motel', $idMotel)],
            'tipo_habitacion_id' => ['nullable', 'required_without:habitacion_id', Rule::exists('tipo_habitaciones', 'id')->where('id_motel', $idMotel)],
            'nombre_cliente' => ['nullable', 'string', 'max:160'],
            'telefono_cliente' => ['nullable', 'string', 'max:40'],
            'correo_cliente' => ['nullable', 'email', 'max:160'],
            'cantidad_personas' => ['required', 'integer', 'min:1', 'max:6'],
            'fecha_entrada' => ['required', 'date'],
            'fecha_salida' => ['required', 'date', 'after:fecha_entrada'],
            'estado' => ['sometimes', Rule::in(['pendiente', 'confirmada', 'ocupada', 'finalizada', 'cancelada'])],
            'tipo_pago' => ['nullable', Rule::in(['efectivo', 'transferencia', 'tarjeta', 'online'])],
            'comentario' => ['nullable', 'string'],
        ]);

        if ($clienteAutenticado) {
            $data['cliente_id'] = $clienteAutenticado->id;
            $data['nombre_cliente'] = trim($clienteAutenticado->nombre.' '.$clienteAutenticado->apellido);
            $data['telefono_cliente'] = $clienteAutenticado->telefono;
            $data['correo_cliente'] = $clienteAutenticado->correo;
        }

        [$habitacion, $tipoHabitacionId] = $this->resolverAsignacion($data, $idMotel);
        $this->asegurarDisponibilidad($idMotel, $tipoHabitacionId, $data['fecha_entrada'], $data['fecha_salida'], $habitacion?->id);

        $data['id_motel'] = $idMotel;
        $data['habitacion_id'] = $habitacion?->id;
        $data['tipo_habitacion_id'] = $tipoHabitacionId;
        $data['codigo_reserva'] = $this->generarCodigoReserva();
        $data['qr_token'] = Str::uuid()->toString();
        $data['estado'] = $data['estado'] ?? 'pendiente';

        $reserva = Reserva::create($data);
        $this->sincronizarEstadoHabitacion($reserva);

        return response()->json($reserva->load(['cliente', 'habitacion.tipoHabitacion', 'tipoHabitacion', 'motel', 'valoracion', 'enviosProgramados']), 201);
    }

    private function clienteAutenticado(Request $request, int $idMotel): ?Cliente
    {
        $plainToken = $request->bearerToken();

        if (! $plainToken) {
            return null;
        }

        $token = ApiToken::query()
            ->where('token_hash', hash('sha256', $plainToken))
            ->where('actor_type', 'client')
            ->where('id_motel', $idMotel)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        abort_unless($token, 401, 'La sesion del cliente no es valida o expiro.');

        return Cliente::where('id', $token->actor_id)
            ->where('id_motel', $idMotel)
            ->where('activo', true)
            ->firstOrFail();
    }

    public function show(Reserva $reserva)
    {
        return $reserva->load(['cliente', 'habitacion.tipoHabitacion', 'tipoHabitacion', 'motel', 'valoracion', 'enviosProgramados']);
    }

    public function update(Request $request, Reserva $reserva)
    {
        $idMotel = CurrentMotel::id($request);
        $data = $request->validate([
            'cliente_id' => ['nullable', Rule::exists('clientes', 'id')->where('id_motel', $idMotel)],
            'habitacion_id' => ['nullable', Rule::exists('habitaciones', 'id')->where('id_motel', $idMotel)],
            'tipo_habitacion_id' => ['nullable', Rule::exists('tipo_habitaciones', 'id')->where('id_motel', $idMotel)],
            'nombre_cliente' => ['nullable', 'string', 'max:160'],
            'telefono_cliente' => ['nullable', 'string', 'max:40'],
            'correo_cliente' => ['nullable', 'email', 'max:160'],
            'cantidad_personas' => ['sometimes', 'required', 'integer', 'min:1', 'max:6'],
            'fecha_entrada' => ['sometimes', 'required', 'date'],
            'fecha_salida' => ['sometimes', 'required', 'date'],
            'estado' => ['sometimes', Rule::in(['pendiente', 'confirmada', 'ocupada', 'finalizada', 'cancelada'])],
            'tipo_pago' => ['nullable', Rule::in(['efectivo', 'transferencia', 'tarjeta', 'online'])],
            'comentario' => ['nullable', 'string'],
        ]);

        $habitacionAnteriorId = $reserva->habitacion_id;
        $estadoAnterior = $reserva->estado;
        $merged = array_merge($reserva->only([
            'habitacion_id',
            'tipo_habitacion_id',
            'fecha_entrada',
            'fecha_salida',
        ]), $data);

        [$habitacion, $tipoHabitacionId] = $this->resolverAsignacion($merged, $reserva->id_motel);

        if ($habitacion || $tipoHabitacionId) {
            $this->asegurarDisponibilidad(
                $reserva->id_motel,
                $tipoHabitacionId,
                $merged['fecha_entrada'],
                $merged['fecha_salida'],
                $habitacion?->id,
                $reserva->id,
            );
        }

        $data['habitacion_id'] = array_key_exists('habitacion_id', $data) ? $habitacion?->id : $reserva->habitacion_id;
        $data['tipo_habitacion_id'] = $tipoHabitacionId ?: $reserva->tipo_habitacion_id;

        $reserva->update($data);
        $reservaActualizada = $reserva->fresh();

        if ($habitacionAnteriorId && $habitacionAnteriorId !== $reservaActualizada->habitacion_id) {
            $this->liberarHabitacionSiNoTieneOcupacion($habitacionAnteriorId, $reservaActualizada->id_motel, $reservaActualizada->id);
        }

        $this->sincronizarEstadoHabitacion($reservaActualizada, $estadoAnterior);

        return $reservaActualizada->load(['cliente', 'habitacion.tipoHabitacion', 'tipoHabitacion', 'motel', 'valoracion', 'enviosProgramados']);
    }

    public function destroy(Reserva $reserva)
    {
        $estadoAnterior = $reserva->estado;
        $reserva->update(['estado' => 'cancelada']);
        $this->sincronizarEstadoHabitacion($reserva->fresh(), $estadoAnterior);

        return response()->noContent();
    }

    public function enviarComprobante(Request $request, Reserva $reserva)
    {
        $actor = $request->attributes->get('api_actor');

        if ($request->attributes->get('api_actor_type') === 'client') {
            abort_if((int) $reserva->cliente_id !== (int) $actor->id, 404);
        }

        $destinatario = $reserva->correo_cliente ?? $reserva->cliente?->correo;

        if (! $destinatario) {
            return response()->json([
                'message' => 'La reserva no tiene un correo asociado.',
            ], 422);
        }

        try {
            Mail::to($destinatario)->send(new ReservaConfirmadaMail($reserva->load(['habitacion.tipoHabitacion', 'tipoHabitacion'])));
        } catch (\Throwable $exception) {
            Log::warning('No se pudo enviar el comprobante de reserva.', [
                'reserva_id' => $reserva->id,
                'destinatario' => $destinatario,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'No se pudo enviar el comprobante. Intenta nuevamente en unos minutos.',
            ], 500);
        }

        return response()->json([
            'message' => 'Comprobante enviado correctamente al correo registrado.',
        ]);
    }

    private function resolverAsignacion(array $data, int $idMotel): array
    {
        $habitacion = null;
        $tipoHabitacionId = $data['tipo_habitacion_id'] ?? null;

        if (! empty($data['habitacion_id'])) {
            $habitacion = Habitacion::where('id', $data['habitacion_id'])
                ->where('id_motel', $idMotel)
                ->where('activa', true)
                ->firstOrFail();

            if (in_array($habitacion->estado, ['bloqueada', 'mantenimiento'], true)) {
                throw ValidationException::withMessages([
                    'habitacion_id' => ['La habitacion seleccionada no esta disponible.'],
                ]);
            }

            $tipoHabitacionId = $habitacion->tipo_habitacion_id;
        }

        if ($tipoHabitacionId) {
            TipoHabitacion::where('id', $tipoHabitacionId)
                ->where('id_motel', $idMotel)
                ->where('activo', true)
                ->firstOrFail();
        }

        return [$habitacion, $tipoHabitacionId];
    }

    private function asegurarDisponibilidad(
        int $idMotel,
        int $tipoHabitacionId,
        string $fechaEntrada,
        string $fechaSalida,
        ?int $habitacionId = null,
        ?int $reservaIgnoradaId = null,
    ): void {
        $habitacionesDelTipo = Habitacion::query()
            ->where('id_motel', $idMotel)
            ->where('tipo_habitacion_id', $tipoHabitacionId)
            ->where('activa', true)
            ->whereNotIn('estado', ['bloqueada', 'mantenimiento'])
            ->pluck('id');

        if ($habitacionesDelTipo->isEmpty()) {
            throw ValidationException::withMessages([
                'tipo_habitacion_id' => ['No hay habitaciones activas para este tipo.'],
            ]);
        }

        if ($habitacionId && ! $habitacionesDelTipo->contains($habitacionId)) {
            throw ValidationException::withMessages([
                'habitacion_id' => ['La habitacion no pertenece al tipo solicitado.'],
            ]);
        }

        $reservasSolapadas = Reserva::query()
            ->where('id_motel', $idMotel)
            ->whereIn('estado', $this->estadosActivos)
            ->where('fecha_entrada', '<', $fechaSalida)
            ->where('fecha_salida', '>', $fechaEntrada)
            ->when($reservaIgnoradaId, fn ($query) => $query->where('id', '!=', $reservaIgnoradaId))
            ->where(function ($query) use ($tipoHabitacionId, $habitacionesDelTipo) {
                $query
                    ->where('tipo_habitacion_id', $tipoHabitacionId)
                    ->orWhereIn('habitacion_id', $habitacionesDelTipo);
            });

        if ($habitacionId && (clone $reservasSolapadas)->where('habitacion_id', $habitacionId)->exists()) {
            throw ValidationException::withMessages([
                'habitacion_id' => ['La habitacion seleccionada ya tiene una reserva en ese horario.'],
            ]);
        }

        if ((clone $reservasSolapadas)->count() >= $habitacionesDelTipo->count()) {
            throw ValidationException::withMessages([
                'tipo_habitacion_id' => ['No quedan cupos disponibles para este tipo de habitacion en ese horario.'],
            ]);
        }
    }

    private function generarCodigoReserva(): string
    {
        $nextId = (Reserva::max('id') ?? 0) + 1;

        return 'DIS-'.now()->format('Y').'-'.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }

    private function programarValoracion(Reserva $reserva): void
    {
        $valoracion = Valoracion::firstOrCreate(
            ['reserva_id' => $reserva->id],
            [
                'cliente_id' => $reserva->cliente_id,
                'id_motel' => $reserva->id_motel,
                'token' => Str::uuid()->toString(),
            ],
        );
        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:5173/discret'), '/');

        EnvioProgramado::firstOrCreate(
            [
                'reserva_id' => $reserva->id,
                'tipo' => 'valoracion_post_salida',
            ],
            [
                'cliente_id' => $reserva->cliente_id,
                'id_motel' => $reserva->id_motel,
                'canal' => 'correo',
                'destinatario' => $reserva->correo_cliente ?? $reserva->cliente?->correo ?? 'pendiente@discret.cl',
                'asunto' => 'Cuentanos como fue tu experiencia en DISCRET',
                'mensaje' => "{$frontendUrl}/valoracion/{$valoracion->token}",
                'programado_para' => $reserva->fecha_salida->copy()->addMinutes(30),
                'estado' => 'pendiente',
            ],
        );
    }

    private function sincronizarEstadoHabitacion(Reserva $reserva, ?string $estadoAnterior = null): void
    {
        if (! $reserva->habitacion_id) {
            return;
        }

        if ($reserva->estado === 'ocupada') {
            Habitacion::where('id', $reserva->habitacion_id)
                ->where('id_motel', $reserva->id_motel)
                ->update(['estado' => 'ocupada']);

            return;
        }

        if ($reserva->estado === 'finalizada') {
            Habitacion::where('id', $reserva->habitacion_id)
                ->where('id_motel', $reserva->id_motel)
                ->update(['estado' => 'limpieza']);

            $this->programarValoracion($reserva);

            return;
        }

        if (
            in_array($reserva->estado, ['cancelada', 'pendiente', 'confirmada'], true)
            && in_array($estadoAnterior, ['ocupada', 'finalizada'], true)
        ) {
            $this->liberarHabitacionSiNoTieneOcupacion($reserva->habitacion_id, $reserva->id_motel, $reserva->id);
        }
    }

    private function liberarHabitacionSiNoTieneOcupacion(int $habitacionId, int $idMotel, ?int $reservaIgnoradaId = null): void
    {
        $tieneReservaOcupada = Reserva::query()
            ->where('id_motel', $idMotel)
            ->where('habitacion_id', $habitacionId)
            ->where('estado', 'ocupada')
            ->when($reservaIgnoradaId, fn ($query) => $query->where('id', '!=', $reservaIgnoradaId))
            ->exists();

        if ($tieneReservaOcupada) {
            return;
        }

        Habitacion::where('id', $habitacionId)
            ->where('id_motel', $idMotel)
            ->whereIn('estado', ['ocupada', 'limpieza'])
            ->update(['estado' => 'disponible']);
    }
}




