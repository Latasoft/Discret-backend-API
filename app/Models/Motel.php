<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Motel extends Model
{
    protected $table = 'motel';

    protected $primaryKey = 'id_motel';

    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'email',
        'ventana_limpieza',
        'activo',
        'slug',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function habitaciones(): HasMany
    {
        return $this->hasMany(Habitacion::class, 'id_motel');
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'id_motel');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'id_motel');
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'id_motel');
    }

    public function tiposHabitacion(): HasMany
    {
        return $this->hasMany(TipoHabitacion::class, 'id_motel');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
