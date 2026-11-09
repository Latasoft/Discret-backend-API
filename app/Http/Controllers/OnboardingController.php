<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use App\Models\Motel;
use App\Models\Plan;
use App\Models\Suscripcion;
use App\Models\User;
use App\Support\MercadoPagoSubscriptionCheckout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    public function store(Request $request, MercadoPagoSubscriptionCheckout $checkout)
    {
        $data = $request->validate([
            'plan' => ['required', Rule::in(['BASE', 'DISCRET'])],
            'propietario_nombre' => ['required', 'string', 'max:120'],
            'propietario_email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'motel_nombre' => ['required', 'string', 'max:50'],
            'motel_direccion' => ['required', 'string', 'max:100'],
            'motel_telefono' => ['required', 'string', 'max:50'],
            'motel_email' => ['required', 'email', 'max:100'],
            'acepta_terminos' => ['accepted'],
        ]);

        $plan = Plan::query()
            ->whereRaw('UPPER(nombre) = ?', [$data['plan']])
            ->where('activo', true)
            ->firstOrFail();

        [$motel, $owner, $subscription] = DB::transaction(function () use ($data, $plan) {
            $baseSlug = Str::slug($data['motel_nombre']) ?: 'motel';
            $slug = $baseSlug;
            $suffix = 2;

            while (Motel::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$suffix}";
                $suffix++;
            }

            $motel = Motel::create([
                'nombre' => $data['motel_nombre'],
                'direccion' => $data['motel_direccion'],
                'telefono' => $data['motel_telefono'],
                'email' => $data['motel_email'],
                'ventana_limpieza' => 30,
                'activo' => true,
                'slug' => $slug,
            ]);

            $owner = User::create([
                'name' => $data['propietario_nombre'],
                'email' => Str::lower($data['propietario_email']),
                'password' => $data['password'],
                'id_motel' => $motel->id_motel,
                'role' => 'owner',
            ]);

            $subscription = Suscripcion::create([
                'fecha_inicio' => now(),
                'fecha_fin' => null,
                'estado' => 'PENDIENTE',
                'metodo_pago' => 'mercado_pago',
                'monto_pagado' => $plan->precio,
                'id_plan' => $plan->id_plan,
                'id_motel' => $motel->id_motel,
                'propietario_id' => $owner->id,
                'referencia_interna' => 'DISCRET-'.Str::upper(Str::random(20)),
                'correo_pagador' => Str::lower($data['propietario_email']),
            ]);

            return [$motel, $owner, $subscription];
        });

        $checkoutData = $checkout->create(
            $plan,
            $subscription->referencia_interna,
            $subscription->correo_pagador,
        );

        $subscription->update([
            'mp_preapproval_id' => $checkoutData['preapproval_id'],
            'mp_preapproval_plan_id' => $checkoutData['preapproval_plan_id'],
            'mp_status' => $checkoutData['status'],
            'mp_checkout_url' => $checkoutData['checkout_url'],
            'mp_payload' => $checkoutData['payload'],
        ]);

        return response()->json([
            'message' => 'Tu cuenta propietaria y el motel quedaron creados. Completa el pago para habilitar el panel.',
            'token' => ApiToken::issue('admin', $owner->id, $motel->id_motel),
            'admin' => [
                'id' => $owner->id,
                'id_motel' => $motel->id_motel,
                'name' => $owner->name,
                'email' => $owner->email,
                'is_super_admin' => false,
                'role' => $owner->role,
            ],
            'motel' => $motel,
            'suscripcion' => $subscription->fresh('plan'),
            'checkout_url' => $checkoutData['checkout_url'],
        ], 201);
    }
}
