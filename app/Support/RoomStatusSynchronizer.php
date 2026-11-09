<?php

namespace App\Support;

use App\Models\Habitacion;
use App\Models\Reserva;
use Illuminate\Support\Carbon;

class RoomStatusSynchronizer
{
    public static function sync(int $idMotel): void
    {
        $now = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $tomorrowStart = $now->copy()->addDay()->startOfDay();

        Reserva::query()
            ->where('id_motel', $idMotel)
            ->whereIn('estado', ['pendiente', 'confirmada', 'ocupada'])
            ->where('fecha_salida', '<=', $now)
            ->update(['estado' => 'finalizada']);

        $activeRoomIds = Reserva::query()
            ->where('id_motel', $idMotel)
            ->whereIn('estado', ['confirmada', 'ocupada'])
            ->whereNotNull('habitacion_id')
            ->where('fecha_entrada', '<=', $now)
            ->where('fecha_salida', '>', $now)
            ->distinct()
            ->pluck('habitacion_id');

        $roomsEndedToday = Reserva::query()
            ->where('id_motel', $idMotel)
            ->where('estado', 'finalizada')
            ->whereNotNull('habitacion_id')
            ->whereBetween('fecha_salida', [$todayStart, $tomorrowStart])
            ->distinct()
            ->pluck('habitacion_id')
            ->diff($activeRoomIds)
            ->values();

        $roomsToAutoRelease = Habitacion::query()
            ->where('id_motel', $idMotel)
            ->where('activa', true)
            ->whereIn('estado', ['ocupada', 'limpieza'])
            ->where('updated_at', '<', $todayStart)
            ->whereNotIn('estado', ['mantenimiento', 'bloqueada'])
            ->whereNotIn('id', $activeRoomIds)
            ->whereNotIn('id', $roomsEndedToday)
            ->pluck('id');

        if ($roomsToAutoRelease->isNotEmpty()) {
            Habitacion::query()
                ->where('id_motel', $idMotel)
                ->whereIn('id', $roomsToAutoRelease)
                ->where('activa', true)
                ->whereNotIn('estado', ['mantenimiento', 'bloqueada'])
                ->update(['estado' => 'disponible']);
        }

        if ($roomsEndedToday->isNotEmpty()) {
            Habitacion::query()
                ->where('id_motel', $idMotel)
                ->whereIn('id', $roomsEndedToday)
                ->where('activa', true)
                ->whereNotIn('estado', ['mantenimiento', 'bloqueada'])
                ->update(['estado' => 'limpieza']);
        }

        if ($activeRoomIds->isNotEmpty()) {
            Reserva::query()
                ->where('id_motel', $idMotel)
                ->whereIn('estado', ['confirmada'])
                ->whereIn('habitacion_id', $activeRoomIds)
                ->where('fecha_entrada', '<=', $now)
                ->where('fecha_salida', '>', $now)
                ->update(['estado' => 'ocupada']);

            Habitacion::query()
                ->where('id_motel', $idMotel)
                ->whereIn('id', $activeRoomIds)
                ->where('activa', true)
                ->whereNotIn('estado', ['mantenimiento', 'bloqueada'])
                ->update(['estado' => 'ocupada']);
        }
    }
}