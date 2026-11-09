<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reserva extends Model
{
    protected $fillable = [
        'codigo_reserva',
        'id_motel',
        'cliente_id',
        'habitacion_id',
        'tipo_habitacion_id',
        'creada_por_admin_id',
        'nombre_cliente',
        'telefono_cliente',
        'correo_cliente',
        'cantidad_personas',
        'fecha_entrada',
        'fecha_salida',
        'estado',
        'tipo_pago',
        'comentario',
        'qr_token',
        'qr_enviado_at',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_personas' => 'integer',
            'fecha_entrada' => 'datetime',
            'fecha_salida' => 'datetime',
            'qr_enviado_at' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }


    public function tipoHabitacion(): BelongsTo
    {
        return $this->belongsTo(TipoHabitacion::class, 'tipo_habitacion_id');
    }
    public function valoracion(): HasOne
    {
        return $this->hasOne(Valoracion::class);
    }

    public function enviosProgramados(): HasMany
    {
        return $this->hasMany(EnvioProgramado::class);
    }
    

    public function motel(): BelongsTo
    {
        return $this->belongsTo(Motel::class, 'id_motel');
    }
}
