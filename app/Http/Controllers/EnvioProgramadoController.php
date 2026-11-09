<?php

namespace App\Http\Controllers;

use App\Mail\ValoracionPostSalidaMail;
use App\Models\EnvioProgramado;
use App\Support\CurrentMotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

class EnvioProgramadoController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'estado' => ['nullable', Rule::in(['pendiente', 'enviado', 'fallido'])],
            'canal' => ['nullable', Rule::in(['correo', 'whatsapp'])],
            'tipo' => ['nullable', Rule::in(['confirmacion_reserva', 'qr_porteria', 'valoracion_post_salida'])],
            'buscar' => ['nullable', 'string', 'max:160'],
        ]);

        return EnvioProgramado::query()
            ->with(['reserva.habitacion.tipoHabitacion', 'reserva.valoracion', 'cliente'])
            ->where('id_motel', CurrentMotel::id($request))
            ->when($data['estado'] ?? null, fn ($query, $estado) => $query->where('estado', $estado))
            ->when($data['canal'] ?? null, fn ($query, $canal) => $query->where('canal', $canal))
            ->when($data['tipo'] ?? null, fn ($query, $tipo) => $query->where('tipo', $tipo))
            ->when($data['buscar'] ?? null, function ($query, $buscar) {
                $query->where(function ($subQuery) use ($buscar) {
                    $subQuery
                        ->where('destinatario', 'like', "%{$buscar}%")
                        ->orWhere('asunto', 'like', "%{$buscar}%")
                        ->orWhere('mensaje', 'like', "%{$buscar}%")
                        ->orWhereHas('reserva', fn ($reservaQuery) => $reservaQuery
                            ->where('codigo_reserva', 'like', "%{$buscar}%")
                            ->orWhere('nombre_cliente', 'like', "%{$buscar}%")
                            ->orWhere('correo_cliente', 'like', "%{$buscar}%"));
                });
            })
            ->latest('programado_para')
            ->paginate(30);
    }

    public function reenviarValoracion(Request $request, EnvioProgramado $envioProgramado)
    {
        abort_if($envioProgramado->id_motel !== CurrentMotel::id($request), 404);

        $envioProgramado->loadMissing([
            'reserva.valoracion',
            'reserva.habitacion.tipoHabitacion',
            'cliente',
            'motel',
        ]);

        if ($envioProgramado->tipo !== 'valoracion_post_salida') {
            return response()->json([
                'message' => 'Este envío no corresponde a una valoración post-salida.',
            ], 422);
        }

        if ($envioProgramado->reserva?->valoracion?->respondida_at) {
            return response()->json([
                'message' => 'El cliente ya calificó esta reserva.',
                'envio' => $envioProgramado,
            ], 422);
        }

        try {
            Mail::to($envioProgramado->destinatario)->send(new ValoracionPostSalidaMail($envioProgramado));

            $envioProgramado->update([
                'estado' => 'enviado',
                'enviado_at' => now(),
                'error' => null,
            ]);

            return response()->json([
                'message' => 'Correo de valoración reenviado correctamente.',
                'envio' => $envioProgramado->fresh()->load([
                    'reserva.habitacion.tipoHabitacion',
                    'reserva.valoracion',
                    'cliente',
                ]),
            ]);
        } catch (Throwable $exception) {
            $envioProgramado->update([
                'estado' => 'fallido',
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'No se pudo reenviar el correo de valoración.',
                'envio' => $envioProgramado->fresh()->load([
                    'reserva.habitacion.tipoHabitacion',
                    'reserva.valoracion',
                    'cliente',
                ]),
            ], 500);
        }
    }
}
