<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use App\Models\Cliente;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiActor
{
    public function handle(Request $request, Closure $next, string $requiredActor = 'admin'): Response
    {
        $plainToken = $request->bearerToken();

        if ($requiredActor === 'optional' && ! $plainToken) {
            return $next($request);
        }

        abort_unless($plainToken, 401, 'Debes iniciar sesión para continuar.');

        $token = ApiToken::where('token_hash', hash('sha256', $plainToken))
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        abort_unless($token, 401, 'La sesión expiró o no es válida.');
        abort_if(! in_array($requiredActor, ['any', 'optional'], true) && $token->actor_type !== $requiredActor, 403, 'No tienes permisos para esta operación.');

        $actor = match ($token->actor_type) {
            'admin' => User::find($token->actor_id),
            'client' => Cliente::find($token->actor_id),
            default => null,
        };

        abort_unless($actor, 401, 'La cuenta asociada a la sesión ya no existe.');

        $isSuperAdmin = $actor instanceof User && $actor->role === 'super_admin';

        $request->attributes->set('api_actor', $actor);
        $request->attributes->set('api_actor_type', $token->actor_type);
        $request->attributes->set('authenticated_motel_id', $token->id_motel);
        $request->attributes->set('is_super_admin', $isSuperAdmin);

        $token->forceFill(['last_used_at' => now()])->save();

        $this->guardBoundTenantModels($request, $token->id_motel, $isSuperAdmin);

        return $next($request);
    }

    private function guardBoundTenantModels(Request $request, ?int $idMotel, bool $isSuperAdmin): void
    {
        if ($isSuperAdmin || ! $idMotel) {
            return;
        }

        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if ($parameter instanceof Model && isset($parameter->id_motel)) {
                abort_if((int) $parameter->id_motel !== $idMotel, 404);
            }
        }
    }
}
