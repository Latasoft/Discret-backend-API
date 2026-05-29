<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    protected $table = 'reserva';

    protected $primaryKey = 'id_reserva';

    protected $fillable = [
        'codigo_reserva',
        'hora_inicio',
        'hora_fin',
        'total',
        'estado',
        'comentario',
        'id_habitacion',
        'id_cliente',
        'id_motel',
    ];

    protected $casts = [
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime',
        'total' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class, 'id_habitacion');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function motel(): BelongsTo
    {
        return $this->belongsTo(Motel::class, 'id_motel');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getDuracionHorasAttribute(): int
    {
        return $this->hora_inicio->diffInHours($this->hora_fin);
    }

    /*
    |--------------------------------------------------------------------------
    | Estados
    |--------------------------------------------------------------------------
    */

    public const ESTADO_PENDIENTE = 'PENDIENTE';
    public const ESTADO_CONFIRMADA = 'CONFIRMADA';
    public const ESTADO_FINALIZADA = 'FINALIZADA';
    public const ESTADO_CANCELADA = 'CANCELADA';

    /*
    |--------------------------------------------------------------------------
    | Boot
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($reserva) {

            if (empty($reserva->codigo_reserva)) {
                $reserva->codigo_reserva = 'RES-' . strtoupper(uniqid());
            }

            if (empty($reserva->estado)) {
                $reserva->estado = self::ESTADO_PENDIENTE;
            }
        });
    }
}