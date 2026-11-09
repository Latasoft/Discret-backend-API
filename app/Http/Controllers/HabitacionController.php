<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Reserva;
use App\Support\CurrentMotel;
use App\Support\RoomStatusSynchronizer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class HabitacionController extends Controller
{
    public function index(Request $request)
    {
        $idMotel = CurrentMotel::id($request);
        RoomStatusSynchronizer::sync($idMotel);

        return Habitacion::query()
            ->with('tipoHabitacion')
            ->where('id_motel', $idMotel)
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->estado))
            ->when($request->filled('buscar'), function ($query) use ($request) {
                $buscar = $request->buscar;

                $query->where(function ($subQuery) use ($buscar) {
                    $subQuery
                        ->where('numero', 'like', "%{$buscar}%")
                        ->orWhere('nombre', 'like', "%{$buscar}%")
                        ->orWhere('descripcion', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('numero')
            ->get();
    }

    public function store(Request $request)
    {
        $idMotel = CurrentMotel::id($request);

        $data = $request->validate([
            'tipo_habitacion_id' => ['required', Rule::exists('tipo_habitaciones', 'id')->where('id_motel', $idMotel)],
            'numero' => [
                'required',
                'string',
                'max:20',
                Rule::unique('habitaciones', 'numero')->where('id_motel', $idMotel),
            ],
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'integer', 'min:0'],
            'estado' => ['required', Rule::in(['disponible', 'ocupada', 'limpieza', 'mantenimiento', 'bloqueada'])],
            'activa' => ['sometimes', 'boolean'],
        ]);

        $data['id_motel'] = $idMotel;

        return response()->json(Habitacion::create($data)->load('tipoHabitacion'), 201);
    }

    public function show(Habitacion $habitacion)
    {
        return $habitacion->load(['tipoHabitacion', 'reservas']);
    }

    public function update(Request $request, Habitacion $habitacion)
    {
        $idMotel = CurrentMotel::id($request);
        $data = $request->validate([
            'tipo_habitacion_id' => ['sometimes', 'required', Rule::exists('tipo_habitaciones', 'id')->where('id_motel', $idMotel)],
            'numero' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('habitaciones', 'numero')
                    ->where('id_motel', $habitacion->id_motel)
                    ->ignore($habitacion),
            ],
            'nombre' => ['sometimes', 'required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['sometimes', 'required', 'integer', 'min:0'],
            'estado' => ['sometimes', 'required', Rule::in(['disponible', 'ocupada', 'limpieza', 'mantenimiento', 'bloqueada'])],
            'activa' => ['sometimes', 'boolean'],
        ]);

        $habitacion->update($data);

        if (array_key_exists('estado', $data)) {
            $this->cerrarOcupacionActivaSiCorresponde($habitacion->fresh(), $data['estado']);
        }

        return $habitacion->fresh()->load('tipoHabitacion');
    }

    private function cerrarOcupacionActivaSiCorresponde(Habitacion $habitacion, string $estado): void
    {
        if (! in_array($estado, ['disponible', 'limpieza'], true)) {
            return;
        }

        $now = Carbon::now();

        Reserva::query()
            ->where('id_motel', $habitacion->id_motel)
            ->where('habitacion_id', $habitacion->id)
            ->whereIn('estado', ['pendiente', 'confirmada', 'ocupada'])
            ->where('fecha_entrada', '<=', $now)
            ->where('fecha_salida', '>', $now)
            ->update(['estado' => 'finalizada']);
    }

    public function destroy(Habitacion $habitacion)
    {
        $habitacion->update(['activa' => false]);

        return response()->noContent();
    }
}




