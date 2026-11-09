<?php

namespace App\Support;

use App\Models\Motel;
use Illuminate\Http\Request;

class CurrentMotel
{
    public static function resolve(Request $request): Motel
    {
        $authenticatedMotelId = $request->attributes->get('authenticated_motel_id');
        $isSuperAdmin = (bool) $request->attributes->get('is_super_admin', false);

        if ($authenticatedMotelId && ! $isSuperAdmin) {
            return Motel::where('id_motel', $authenticatedMotelId)
                ->where('activo', true)
                ->firstOrFail();
        }

        if ($isSuperAdmin && $request->filled('id_motel')) {
            return Motel::where('id_motel', $request->integer('id_motel'))->firstOrFail();
        }

        if ($request->filled('motel_slug')) {
            return Motel::where('slug', $request->string('motel_slug'))->firstOrFail();
        }

        if ($authenticatedMotelId) {
            return Motel::where('id_motel', $authenticatedMotelId)->firstOrFail();
        }

        abort(422, 'Debes indicar el motel mediante motel_slug.');
    }

    public static function id(Request $request): int
    {
        return self::resolve($request)->id_motel;
    }
}
