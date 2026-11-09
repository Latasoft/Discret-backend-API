<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habitacion extends Model
{
    protected $table = 'habitaciones';

    protected $fillable = [
        'id_motel',
        'tipo_habitacion_id',
        'numero',
        'nombre',
        'descripcion',
        'precio',
        'estado',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'integer',
            'activa' => 'boolean',
        ];
    }

    public function tipoHabitacion(): BelongsTo
    {
        return $this->belongsTo(TipoHabitacion::class);
    }
    public function motel(): BelongsTo
    {
        return $this->belongsTo(Motel::class, 'id_motel');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }
}
