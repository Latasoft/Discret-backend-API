<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'planes';

    protected $primaryKey = 'id_plan';

    protected $fillable = [
        'nombre',
        'precio',
        'max_habitaciones',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'precio' => 'decimal:2',
    ];

    /*
    |-----------------------------
    | SCOPES
    |-----------------------------
    */

    public function scopeActivos($query)
    {
        return $query->where('activo', 1);
    }
}