<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarifaHabitacion extends Model
{
    protected $table = 'tarifa_habitacion';

    protected $primaryKey = 'id_tarifa_habitacion';

    public $timestamps = false;

    protected $fillable = [
        'precio',
        'duracion_horas',
        'fecha_inicio',
        'fecha_fin',
        'activa',
        'id_tipo_habitacion',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activa' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function tipoHabitacion(): BelongsTo
    {
        return $this->belongsTo(
            TipoHabitacion::class,
            'id_tipo_habitacion'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopeVigentes($query)
    {
        return $query
            ->whereDate('fecha_inicio', '<=', now())
            ->whereDate('fecha_fin', '>=', now());
    }
}