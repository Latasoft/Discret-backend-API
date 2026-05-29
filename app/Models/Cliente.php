<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Cliente extends Model
{
    protected $table = 'cliente';

    protected $primaryKey = 'id_cliente';

    protected $fillable = [
        'nombre',
        'apellido',
        'telefono',
        'email',
        'fecha_nacimiento',
        'id_motel',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |---------------------------------------------------
    | RELACIONES
    |---------------------------------------------------
    */

    public function motel()
    {
        return $this->belongsTo(Motel::class, 'id_motel');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_cliente');
    }

    /*
    |---------------------------------------------------
    | SCOPES
    |---------------------------------------------------
    */

    // Buscar cliente por teléfono
    public function scopeTelefono(Builder $query, $telefono)
    {
        return $query->where('telefono', $telefono);
    }

    // Buscar cliente por email
    public function scopeEmail(Builder $query, $email)
    {
        return $query->where('email', $email);
    }
}