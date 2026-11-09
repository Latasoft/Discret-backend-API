<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Models\Reserva;
use App\Support\CurrentMotel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        return Reporte::where('id_motel', CurrentMotel::id($request))
            ->latest()
            ->paginate(20);
    }

    public function store(Request $request)
    {
        $idMotel = CurrentMotel::id($request);

        $data = $request->validate([
            'tipo' => ['required', Rule::in(['DIARIO', 'SEMANAL', 'MENSUAL'])],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        $reservas = Reserva::where('id_motel', $idMotel)
            ->whereBetween('fecha_entrada', [
                "{$data['fecha_inicio']} 00:00:00",
                "{$data['fecha_fin']} 23:59:59",
            ])
            ->with('habitacion')
            ->get();

        $totalReservas = $reservas->count();
        $totalIngresos = $reservas->sum(fn (Reserva $reserva) => $reserva->habitacion?->precio ?? 0);
        $habitacionesUnicas = max($reservas->pluck('habitacion_id')->unique()->count(), 1);
        $ocupacionPromedio = $totalReservas > 0
            ? min(100, round(($totalReservas / ($habitacionesUnicas * 3)) * 100, 2))
            : 0;

        $reporte = Reporte::create([
            'id_motel' => $idMotel,
            'user_id' => $request->attributes->get('api_actor')->id,
            'tipo' => $data['tipo'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'estado' => 'GENERADO',
            'total_ingresos' => $totalIngresos,
            'total_reservas' => $totalReservas,
            'ocupacion_promedio' => $ocupacionPromedio,
        ]);

        return response()->json($reporte, 201);
    }

    public function show(Reporte $reporte)
    {
        return $reporte;
    }

    public function destroy(Reporte $reporte)
    {
        $reporte->delete();

        return response()->noContent();
    }
}
