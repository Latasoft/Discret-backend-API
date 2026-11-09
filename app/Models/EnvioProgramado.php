<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvioProgramado extends Model
{
    protected $table = 'envios_programados';

    protected $fillable = [
        'id_motel',
        'reserva_id',
        'cliente_id',
        'tipo',
        'canal',
        'destinatario',
        'asunto',
        'mensaje',
        'programado_para',
        'enviado_at',
        'estado',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'programado_para' => 'datetime',
            'enviado_at' => 'datetime',
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
