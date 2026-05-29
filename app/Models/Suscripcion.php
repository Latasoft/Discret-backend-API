<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suscripcion extends Model
{
    protected $table = 'suscripcion';

    protected $primaryKey = 'id_suscripcion';

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'metodo_pago',
        'monto_pagado',
        'id_plan',
        'id_motel',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'monto_pagado' => 'decimal:2',
    ];

    /*
    |-----------------------------
    | RELACIONES
    |-----------------------------
    */

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'id_plan');
    }

    public function motel()
    {
        return $this->belongsTo(Motel::class, 'id_motel');
    }

    /*
    |-----------------------------
    | SCOPES
    |-----------------------------
    */

    public function scopeActivas($query)
    {
        return $query->where('estado', 'ACTIVA');
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', 'VENCIDA');
    }
}