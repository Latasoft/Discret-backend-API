<?php

namespace App\Http\Controllers;

use App\Models\Valoracion;
use App\Support\CurrentMotel;
use Illuminate\Http\Request;

class ValoracionController extends Controller
{
    public function index(Request $request)
    {
        return Valoracion::with(['reserva.habitacion', 'cliente'])
            ->where('id_motel', CurrentMotel::id($request))
            ->whereNotNull('respondida_at')
            ->latest('respondida_at')
            ->paginate(20);
    }

    public function showByToken(string $token)
    {
        $valoracion = Valoracion::where('token', $token)
            ->with(['reserva.habitacion.tipoHabitacion'])
            ->firstOrFail();

        return [
            'reserva' => [
                'codigo_reserva' => $valoracion->reserva->codigo_reserva,
                'habitacion' => $valoracion->reserva->habitacion->nombre,
                'fecha_salida' => $valoracion->reserva->fecha_salida,
            ],
            'respondida' => (bool) $valoracion->respondida_at,
        ];
    }

    public function storeByToken(Request $request, string $token)
    {
        $valoracion = Valoracion::where('token', $token)->firstOrFail();

        if ($valoracion->respondida_at) {
            return response()->json([
                'message' => 'Esta valoración ya fue respondida.',
            ], 409);
        }

        $data = $request->validate([
            'puntuacion' => ['required', 'integer', 'min:1', 'max:5'],
            'etiquetas' => ['nullable', 'array'],
            'etiquetas.*' => ['string', 'max:80'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ]);

        $valoracion->update([
            ...$data,
            'respondida_at' => now(),
        ]);

        return $valoracion->fresh()->load(['reserva', 'cliente']);
    }
}
