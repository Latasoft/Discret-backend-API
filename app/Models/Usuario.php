<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Usuario extends Model
{
    protected $table = 'usuario';

    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre',
        'email',
        'password_hash',
        'rol',
        'ultimo_login',
        'activo',
        'id_motel',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'ultimo_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |----------------------------------------------------
    | RELACIÓN
    |----------------------------------------------------
    */
    public function motel()
    {
        return $this->belongsTo(Motel::class, 'id_motel');
    }

    /*
    |----------------------------------------------------
    | SCOPES
    |----------------------------------------------------
    */

    // Solo usuarios activos
    public function scopeActivos(Builder $query)
    {
        return $query->where('activo', 1);
    }

    // Solo administradores
    public function scopeAdmin(Builder $query)
    {
        return $query->where('rol', 'ADMIN');
    }

    // Solo recepción
    public function scopeRecepcion(Builder $query)
    {
        return $query->where('rol', 'RECEPCION');
    }

    // Solo limpieza
    public function scopeLimpieza(Builder $query)
    {
        return $query->where('rol', 'LIMPIEZA');
    }
}