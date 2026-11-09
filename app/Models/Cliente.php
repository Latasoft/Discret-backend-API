<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $fillable = [
        'id_motel',
        'nombre',
        'apellido',
        'telefono',
        'correo',
        'fecha_nacimiento',
        'password',
        'mayor_edad_confirmado',
        'activo',
        'ultimo_acceso_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'mayor_edad_confirmado' => 'boolean',
            'activo' => 'boolean',
            'ultimo_acceso_at' => 'datetime',
        ];
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function motel(): BelongsTo
    {
        return $this->belongsTo(Motel::class, 'id_motel');
    }

    public function valoraciones(): HasMany
    {
        return $this->hasMany(Valoracion::class);
    }
}
