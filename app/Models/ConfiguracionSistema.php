<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionSistema extends Model
{
    protected $table = 'configuracion_sistema';

    protected $fillable = [
        'id_motel',
        'clave',
        'valor',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'array',
        ];
    }

    public function motel(): BelongsTo
    {
        return $this->belongsTo(Motel::class, 'id_motel');
    }
}
