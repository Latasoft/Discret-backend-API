<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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
        'propietario_id',
        'referencia_interna',
        'correo_pagador',
        'mp_preapproval_id',
        'mp_preapproval_plan_id',
        'mp_status',
        'mp_checkout_url',
        'mp_payload',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'monto_pagado' => 'decimal:2',
        'mp_payload' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'id_plan');
    }

    public function motel()
    {
        return $this->belongsTo(Motel::class, 'id_motel');
    }

    public function propietario()
    {
        return $this->belongsTo(User::class, 'propietario_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActivas(Builder $query)
    {
        return $query->where('estado', 'ACTIVA');
    }

    public function scopeTrial(Builder $query)
    {
        return $query->where('estado', 'TRIAL');
    }

    public function scopeVencidas(Builder $query)
    {
        return $query->where('estado', 'VENCIDA');
    }
}
