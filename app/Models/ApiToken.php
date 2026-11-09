<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    protected $fillable = [
        'token_hash',
        'actor_type',
        'actor_id',
        'id_motel',
        'expires_at',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public static function issue(string $actorType, int $actorId, ?int $idMotel): string
    {
        $plainToken = Str::random(80);

        self::create([
            'token_hash' => hash('sha256', $plainToken),
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'id_motel' => $idMotel,
            'expires_at' => now()->addHours(12),
        ]);

        return $plainToken;
    }
}
