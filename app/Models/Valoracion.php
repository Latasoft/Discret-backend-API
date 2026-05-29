<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Valoracion extends Model
{
    protected $table = 'valoracion';

    protected $primaryKey = 'id_valoracion';

    protected $fillable = [
        'puntuacion',
        'comentario',
        'estado',
        'id_reserva',
        'id_cliente',
        'id_habitacion',
        'id_motel',
    ];

    /*
    |---------------------------------------
    | RELACIONES
    |---------------------------------------
    */

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'id_reserva');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class, 'id_habitacion');
    }

    public function motel()
    {
        return $this->belongsTo(Motel::class, 'id_motel');
    }

    /*
    |---------------------------------------
    | SCOPES
    |---------------------------------------
    */

    public function scopeVisibles(Builder $query)
    {
        return $query->where('estado', 'VISIBLE');
    }

    public function scopeBuenas(Builder $query)
    {
        return $query->where('puntuacion', '>=', 4);
    }
}