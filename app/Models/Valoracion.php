<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Valoracion extends Model
{
    protected $table = 'valoraciones';

    protected $fillable = [
        'id_motel',
        'reserva_id',
        'cliente_id',
        'puntuacion',
        'etiquetas',
        'comentario',
        'token',
        'enviada_at',
        'respondida_at',
    ];

    protected function casts(): array
    {
        return [
            'puntuacion' => 'integer',
            'etiquetas' => 'array',
            'enviada_at' => 'datetime',
            'respondida_at' => 'datetime',
        ];
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function motel(): BelongsTo
    {
        return $this->belongsTo(Motel::class, 'id_motel');
    }
}
