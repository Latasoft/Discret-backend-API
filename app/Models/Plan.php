<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Plan extends Model
{
    protected $table = 'plan';

    protected $primaryKey = 'id_plan';

    protected $fillable = [
        'nombre',
        'precio',
        'max_habitaciones',
        'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class, 'id_plan');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActivos(Builder $query)
    {
        return $query->where('activo', true);
    }

    public function scopeInactivos(Builder $query)
    {
        return $query->where('activo', false);
    }
}