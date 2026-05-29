<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoHabitacion extends Model
{
    protected $table = 'tipo_habitacion';

    protected $primaryKey = 'id_tipo_habitacion';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activa',
        'id_motel',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function motel(): BelongsTo
    {
        return $this->belongsTo(
            Motel::class,
            'id_motel'
        );
    }

    public function habitaciones(): HasMany
    {
        return $this->hasMany(
            Habitacion::class,
            'id_tipo_habitacion'
        );
    }

    public function tarifas(): HasMany
    {
        return $this->hasMany(
            TarifaHabitacion::class,
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
}