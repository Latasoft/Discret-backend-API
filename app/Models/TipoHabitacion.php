<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoHabitacion extends Model
{
    protected $table = 'tipo_habitaciones';

    protected $fillable = [
        'id_motel',
        'nombre',
        'descripcion',
        'precio_base',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio_base' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function habitaciones(): HasMany
    {
        return $this->hasMany(Habitacion::class);
    }

    public function motel(): BelongsTo
    {
        return $this->belongsTo(Motel::class, 'id_motel');
    }
}
