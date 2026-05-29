<?php

namespace App\Services;

use App\Models\Reserva;
use App\Models\Habitacion;
use App\Models\TarifaHabitacion;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    /**
     * CREAR RESERVA
     */
    public function create(array $data)
    {
        $habitacion = Habitacion::findOrFail($data['id_habitacion']);

        $inicio = Carbon::parse($data['hora_inicio']);
        $fin = Carbon::parse($data['hora_fin']);

        // 1. VALIDAR HORAS
        if ($fin <= $inicio) {
            throw ValidationException::withMessages([
                'hora_fin' => 'La hora fin debe ser mayor a la hora inicio'
            ]);
        }

        // 2. VALIDAR DISPONIBILIDAD (SOLAPAMIENTO)
        if ($this->hasOverlap($habitacion->id_habitacion, $inicio, $fin)) {
            throw ValidationException::withMessages([
                'habitacion' => 'La habitación ya está reservada en ese horario'
            ]);
        }

        // 3. CALCULAR TOTAL
        $total = $this->calculateTotal($habitacion, $inicio, $fin);

        // 4. CREAR RESERVA
        return Reserva::create([
            'codigo_reserva' => $this->generateCode(),
            'hora_inicio' => $inicio,
            'hora_fin' => $fin,
            'total' => $total,
            'estado' => 'PENDIENTE',
            'id_habitacion' => $habitacion->id_habitacion,
            'id_cliente' => $data['id_cliente'],
            'id_motel' => $habitacion->id_motel,
        ]);
    }

    /**
     * VALIDAR SOLAPAMIENTO DE RESERVAS
     */
    private function hasOverlap($habitacionId, $inicio, $fin)
    {
        return Reserva::where('id_habitacion', $habitacionId)
            ->whereIn('estado', ['PENDIENTE', 'CONFIRMADA'])
            ->where(function ($query) use ($inicio, $fin) {
                $query->where('hora_inicio', '<', $fin)
                      ->where('hora_fin', '>', $inicio);
            })
            ->exists();
    }

    /**
     * CALCULAR TOTAL DESDE TARIFAS
     */
    private function calculateTotal(Habitacion $habitacion, $inicio, $fin)
    {
        $horas = Carbon::parse($inicio)->diffInHours($fin);

        $tarifas = TarifaHabitacion::where('id_tipo_habitacion', $habitacion->id_tipo_habitacion)
            ->where('activa', 1)
            ->where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now())
            ->orderBy('duracion_horas', 'desc')
            ->get();

        if ($tarifas->isEmpty()) {
            throw new \Exception('No hay tarifas disponibles para esta habitación');
        }

        $tarifa = $tarifas->firstWhere('duracion_horas', '<=', $horas)
            ?? $tarifas->last();

        $multiplicador = ceil($horas / $tarifa->duracion_horas);

        return $multiplicador * $tarifa->precio;
    }

    /**
     * GENERAR CÓDIGO ÚNICO DE RESERVA
     */
    private function generateCode()
    {
        return 'RES-' . strtoupper(uniqid());
    }
}