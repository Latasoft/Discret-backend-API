<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\Suscripcion;
use App\Support\CurrentMotel;
use App\Support\RoomStatusSynchronizer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardMetricController extends Controller
{
    public function index(Request $request)
    {
        $idMotel = CurrentMotel::id($request);
        RoomStatusSynchronizer::sync($idMotel);
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $activeRooms = Habitacion::where('id_motel', $idMotel)->where('activa', true)->count();

        $occupiedTodayRooms = Reserva::query()
            ->where('reservas.id_motel', $idMotel)
            ->whereIn('reservas.estado', ['pendiente', 'confirmada', 'ocupada'])
            ->whereDate('reservas.fecha_entrada', '<=', $today)
            ->whereDate('reservas.fecha_salida', '>=', $today)
            ->whereNotNull('reservas.habitacion_id')
            ->distinct('reservas.habitacion_id')
            ->count('reservas.habitacion_id');

        $monthReservations = Reserva::query()
            ->where('reservas.id_motel', $idMotel)
            ->whereBetween('reservas.fecha_entrada', [$monthStart, $monthEnd])
            ->where('reservas.estado', '!=', 'cancelada');

        $reservationsMonthCount = (clone $monthReservations)->count();

        $estimatedRevenue = (clone $monthReservations)
            ->leftJoin('habitaciones', 'reservas.habitacion_id', '=', 'habitaciones.id')
            ->leftJoin('tipo_habitaciones', 'reservas.tipo_habitacion_id', '=', 'tipo_habitaciones.id')
            ->selectRaw('COALESCE(SUM(COALESCE(habitaciones.precio, tipo_habitaciones.precio_base, 0)), 0) as total')
            ->value('total');

        $mostUsedRooms = Reserva::query()
            ->where('reservas.id_motel', $idMotel)
            ->where('reservas.estado', '!=', 'cancelada')
            ->whereNotNull('reservas.habitacion_id')
            ->leftJoin('habitaciones', 'reservas.habitacion_id', '=', 'habitaciones.id')
            ->select('habitaciones.numero', DB::raw('COUNT(*) as total'))
            ->groupBy('habitaciones.numero')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($room) => ['label' => 'Hab. '.$room->numero, 'total' => (int) $room->total]);

        $frequentClients = Reserva::query()
            ->where('reservas.id_motel', $idMotel)
            ->where('reservas.estado', '!=', 'cancelada')
            ->leftJoin('clientes', 'reservas.cliente_id', '=', 'clientes.id')
            ->selectRaw("COALESCE(NULLIF(TRIM(CONCAT(clientes.nombre, ' ', clientes.apellido)), ''), reservas.nombre_cliente, reservas.correo_cliente, 'Cliente sin nombre') as nombre")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('reservas.cliente_id', 'clientes.nombre', 'clientes.apellido', 'reservas.nombre_cliente', 'reservas.correo_cliente')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($client) => ['label' => $client->nombre, 'total' => (int) $client->total]);

        $subscription = Suscripcion::query()
            ->with('plan')
            ->where('id_motel', $idMotel)
            ->latest('id_suscripcion')
            ->first();

        return response()->json([
            'ocupacion_diaria' => [
                'porcentaje' => $activeRooms > 0 ? round(($occupiedTodayRooms / $activeRooms) * 100, 1) : 0,
                'habitaciones_ocupadas' => $occupiedTodayRooms,
                'habitaciones_activas' => $activeRooms,
            ],
            'reservas_mes' => $reservationsMonthCount,
            'ingresos_estimados' => (float) $estimatedRevenue,
            'habitaciones_mas_usadas' => $mostUsedRooms,
            'clientes_frecuentes' => $frequentClients,
            'suscripcion' => $subscription ? [
                'estado' => $subscription->estado,
                'plan' => $subscription->plan?->nombre,
                'max_habitaciones' => $subscription->plan?->max_habitaciones,
                'fecha_fin' => optional($subscription->fecha_fin)->toDateString(),
                'puede_operar' => in_array($subscription->estado, ['ACTIVA', 'TRIAL'], true)
                    && (! $subscription->fecha_fin || $subscription->fecha_fin->isFuture()),
            ] : [
                'estado' => 'SIN_PLAN',
                'plan' => null,
                'max_habitaciones' => null,
                'fecha_fin' => null,
                'puede_operar' => false,
            ],
        ]);
    }
}


