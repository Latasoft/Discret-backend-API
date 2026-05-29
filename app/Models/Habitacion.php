<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habitacion extends Model
{
    protected $table = 'habitacion';

    protected $primaryKey = 'id_habitacion';

    public $timestamps = true;

    protected $fillable = [
        'numero',
        'nombre',
        'estado',
        'activa',
        'id_tipo_habitacion',
        'id_motel',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($habitacion) {

            if (empty($habitacion->estado)) {
                $habitacion->estado = 'DISPONIBLE';
            }

            if (!isset($habitacion->activa)) {
                $habitacion->activa = true;
            }
        });
    }

    public function tipoHabitacion(): BelongsTo
    {
        return $this->belongsTo(TipoHabitacion::class, 'id_tipo_habitacion');
    }

    public function motel(): BelongsTo
    {
        return $this->belongsTo(Motel::class, 'id_motel');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'id_habitacion');
    }

    public function scopeLibres($query)
    {
        return $query->where('estado', 'DISPONIBLE');
    }

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }
}