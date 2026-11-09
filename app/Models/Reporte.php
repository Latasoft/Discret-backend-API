<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Reporte extends Model
{
    protected $table = 'reporte';

    protected $primaryKey = 'id_reporte';

    protected $fillable = [
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'total_ingresos',
        'total_reservas',
        'ocupacion_promedio',
        'archivo_url',
        'id_motel',
        'user_id',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'total_ingresos' => 'decimal:2',
        'ocupacion_promedio' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function motel()
    {
        return $this->belongsTo(Motel::class, 'id_motel');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeGenerados(Builder $query)
    {
        return $query->where('estado', 'GENERADO');
    }

    public function scopePendientes(Builder $query)
    {
        return $query->where('estado', 'PENDIENTE');
    }

    public function scopeDiarios(Builder $query)
    {
        return $query->where('tipo', 'DIARIO');
    }

    public function scopeSemanales(Builder $query)
    {
        return $query->where('tipo', 'SEMANAL');
    }

    public function scopeMensuales(Builder $query)
    {
        return $query->where('tipo', 'MENSUAL');
    }
}