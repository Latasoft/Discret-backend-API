<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'id_usuario',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'total_ingresos' => 'decimal:2',
        'ocupacion_promedio' => 'decimal:2',
    ];

    /*
    |-----------------------------
    | RELACIONES
    |-----------------------------
    */

    public function motel()
    {
        return $this->belongsTo(Motel::class, 'id_motel');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    /*
    |-----------------------------
    | SCOPES
    |-----------------------------
    */

    public function scopeGenerados($query)
    {
        return $query->where('estado', 'GENERADO');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'PENDIENTE');
    }
}