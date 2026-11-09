<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Plan;
use App\Models\Suscripcion;
use App\Support\CurrentMotel;
use App\Support\MercadoPagoSubscriptionCheckout;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SuscripcionController extends Controller
{
    public function estado(Request $request)
    {
        return response()->json($this->estadoActual(CurrentMotel::id($request)));
    }

    public function iniciar(Request $request, MercadoPagoSubscriptionCheckout $checkout)
    {
        $idMotel = CurrentMotel::id($request);
        $data = $request->validate(['plan' => ['required', 'string', 'max:50']]);
        $planKey = Str::upper($data['plan']);
        $plan = Plan::whereRaw('UPPER(nombre) = ?', [$planKey])->where('activo', true)->firstOrFail();
        $habitacionesActivas = Habitacion::where('id_motel', $idMotel)->where('activa', true)->count();

        if ($habitacionesActivas > $plan->max_habitaciones) {
            throw ValidationException::withMessages([
                'plan' => ["El plan {$plan->nombre} permite hasta {$plan->max_habitaciones} habitaciones. Actualmente tienes {$habitacionesActivas}."],
            ]);
        }

        $owner = $request->attributes->get('api_actor');
        $payerEmail = Str::lower((string) ($owner?->email ?? ''));
        $suscripcion = Suscripcion::firstOrNew([
            'id_motel' => $idMotel,
            'id_plan' => $plan->id_plan,
            'estado' => 'PENDIENTE',
        ]);

        $suscripcion->fill([
            'fecha_inicio' => $suscripcion->fecha_inicio ?? now(),
            'fecha_fin' => null,
            'metodo_pago' => 'mercado_pago',
            'monto_pagado' => $plan->precio,
            'propietario_id' => $owner?->id,
            'referencia_interna' => $suscripcion->referencia_interna ?: 'DISCRET-'.Str::upper(Str::random(20)),
            'correo_pagador' => $payerEmail,
        ]);
        $suscripcion->save();

        $checkoutData = $checkout->create($plan, $suscripcion->referencia_interna, $payerEmail);
        $suscripcion->update([
            'mp_preapproval_id' => $checkoutData['preapproval_id'] ?: $suscripcion->mp_preapproval_id,
            'mp_preapproval_plan_id' => $checkoutData['preapproval_plan_id'],
            'mp_status' => $checkoutData['status'],
            'mp_checkout_url' => $checkoutData['checkout_url'],
            'mp_payload' => $checkoutData['payload'],
        ]);

        return response()->json([
            'message' => 'Suscripcion pendiente creada. Completa el pago en Mercado Pago.',
            'checkout_url' => $checkoutData['checkout_url'],
            'suscripcion' => $suscripcion->load('plan'),
            'estado' => $this->estadoActual($idMotel),
        ]);
    }

    public function webhook(Request $request)
    {
        if (! $this->firmaValida($request)) {
            return response()->json(['message' => 'Firma invalida.'], 401);
        }

        $payload = $request->all();
        $externalId = data_get($payload, 'data.id') ?? data_get($payload, 'id') ?? $request->query('data_id') ?? $request->query('id');
        $detail = $this->mercadoPagoDetail($externalId) ?? $payload;
        $status = Str::lower((string) (data_get($detail, 'status') ?? data_get($payload, 'data.status') ?? ''));
        $planId = data_get($detail, 'preapproval_plan_id') ?? data_get($payload, 'data.preapproval_plan_id');
        $externalReference = data_get($detail, 'external_reference') ?? data_get($payload, 'external_reference');
        $payerEmail = Str::lower((string) (
            data_get($detail, 'payer_email')
            ?? data_get($detail, 'payer.email')
            ?? data_get($payload, 'payer_email')
            ?? ''
        ));

        $suscripcion = $externalId
            ? Suscripcion::where('mp_preapproval_id', $externalId)->first()
            : null;

        $suscripcion ??= $externalReference
            ? Suscripcion::where('referencia_interna', $externalReference)->first()
            : null;

        if (! $suscripcion && $payerEmail) {
            $suscripcion = Suscripcion::query()
                ->whereRaw('LOWER(correo_pagador) = ?', [$payerEmail])
                ->where('estado', 'PENDIENTE')
                ->when($planId, fn ($query) => $query->where('mp_preapproval_plan_id', $planId))
                ->latest('id_suscripcion')
                ->first();
        }

        if (! $suscripcion) {
            Log::info('Webhook Mercado Pago recibido sin suscripcion local asociada.', [
                'external_id' => $externalId,
                'external_reference' => $externalReference,
                'plan_id' => $planId,
                'payload' => $payload,
            ]);

            return response()->json(['message' => 'Webhook recibido sin suscripcion asociada.']);
        }

        $estado = match ($status) {
            'authorized', 'active', 'approved' => 'ACTIVA',
            'paused', 'cancelled', 'cancelled_by_collector' => 'SUSPENDIDA',
            default => $suscripcion->estado === 'PENDIENTE' ? 'PENDIENTE' : $suscripcion->estado,
        };

        $suscripcion->update([
            'estado' => $estado,
            'mp_preapproval_id' => $externalId ?: $suscripcion->mp_preapproval_id,
            'mp_status' => $status ?: $suscripcion->mp_status,
            'mp_payload' => $detail,
            'fecha_inicio' => $estado === 'ACTIVA' ? ($suscripcion->fecha_inicio ?? now()) : $suscripcion->fecha_inicio,
            'fecha_fin' => $estado === 'ACTIVA' ? Carbon::now()->addMonth() : $suscripcion->fecha_fin,
        ]);

        return response()->json(['message' => 'Webhook procesado correctamente.']);
    }

    private function estadoActual(int $idMotel): array
    {
        $suscripcion = Suscripcion::with('plan')->where('id_motel', $idMotel)->latest('id_suscripcion')->first();

        if (! $suscripcion) {
            return ['estado' => 'SIN_PLAN', 'plan' => null, 'max_habitaciones' => null, 'fecha_fin' => null, 'puede_operar' => false];
        }

        $vencida = $suscripcion->fecha_fin && $suscripcion->fecha_fin->isPast();

        if ($vencida && $suscripcion->estado === 'ACTIVA') {
            $suscripcion->update(['estado' => 'VENCIDA']);
            $suscripcion->estado = 'VENCIDA';
        }

        return [
            'estado' => $suscripcion->estado,
            'plan' => $suscripcion->plan?->nombre,
            'max_habitaciones' => $suscripcion->plan?->max_habitaciones,
            'fecha_fin' => optional($suscripcion->fecha_fin)->toDateString(),
            'puede_operar' => in_array($suscripcion->estado, ['ACTIVA', 'TRIAL'], true) && ! $vencida,
        ];
    }

    private function firmaValida(Request $request): bool
    {
        $secret = config('services.mercadopago.webhook_secret') ?: env('MERCADO_PAGO_WEBHOOK_SECRET');

        if (! $secret) {
            return app()->environment(['local', 'testing']);
        }

        $signatureHeader = (string) $request->header('x-signature');
        $requestId = (string) $request->header('x-request-id');
        $dataId = (string) ($request->query('data.id') ?? $request->query('id') ?? data_get($request->all(), 'data.id'));

        preg_match('/ts=([^,]+)/', $signatureHeader, $tsMatch);
        preg_match('/v1=([^,]+)/', $signatureHeader, $hashMatch);

        $timestamp = $tsMatch[1] ?? '';
        $receivedHash = $hashMatch[1] ?? '';

        if (! $timestamp || ! $receivedHash || ! $requestId) {
            return false;
        }

        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$timestamp};";
        $expectedHash = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expectedHash, $receivedHash);
    }

    private function mercadoPagoDetail(?string $externalId): ?array
    {
        $accessToken = config('services.mercadopago.access_token');

        if (! $externalId || ! $accessToken) {
            return null;
        }

        try {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->timeout(15)
                ->get("https://api.mercadopago.com/preapproval/{$externalId}");

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $exception) {
            Log::warning('No se pudo consultar el detalle de la suscripcion en Mercado Pago.', [
                'preapproval_id' => $externalId,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
