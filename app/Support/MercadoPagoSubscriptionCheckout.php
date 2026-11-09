<?php

namespace App\Support;

use App\Models\Plan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MercadoPagoSubscriptionCheckout
{
    private const CHECKOUT_LINKS = [
        'BASE' => 'https://www.mercadopago.cl/subscriptions/checkout?preapproval_plan_id=7bd7bb4b9d6f4b889257fd5bf2087501',
        'DISCRET' => 'https://www.mercadopago.cl/subscriptions/checkout?preapproval_plan_id=7e762e68bee04fedbbef5ab98c3b8cca',
    ];

    private const PLAN_IDS = [
        'BASE' => '7bd7bb4b9d6f4b889257fd5bf2087501',
        'DISCRET' => '7e762e68bee04fedbbef5ab98c3b8cca',
    ];

    public function create(Plan $plan, string $reference, string $payerEmail): array
    {
        $planKey = Str::upper($plan->nombre);
        $planId = self::PLAN_IDS[$planKey] ?? null;
        $fallbackUrl = self::CHECKOUT_LINKS[$planKey] ?? null;

        abort_unless($planId && $fallbackUrl, 422, 'Este plan no tiene checkout configurado.');

        $accessToken = config('services.mercadopago.access_token');

        if ($accessToken) {
            try {
                $response = Http::withToken($accessToken)
                    ->acceptJson()
                    ->timeout(15)
                    ->post('https://api.mercadopago.com/preapproval', [
                        'preapproval_plan_id' => $planId,
                        'reason' => "Plan {$plan->nombre} DISCRET",
                        'external_reference' => $reference,
                        'payer_email' => $payerEmail,
                        'back_url' => rtrim(config('app.frontend_url', env('FRONTEND_URL')), '/').'/contratar?resultado=pago',
                        'status' => 'pending',
                    ]);

                if ($response->successful() && $response->json('init_point')) {
                    return [
                        'checkout_url' => $response->json('init_point'),
                        'preapproval_id' => $response->json('id'),
                        'preapproval_plan_id' => $planId,
                        'status' => $response->json('status'),
                        'payload' => $response->json(),
                    ];
                }

                Log::warning('Mercado Pago no pudo crear el checkout de suscripcion.', [
                    'reference' => $reference,
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
            } catch (\Throwable $exception) {
                Log::warning('Mercado Pago no estuvo disponible al crear el checkout.', [
                    'reference' => $reference,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $separator = str_contains($fallbackUrl, '?') ? '&' : '?';

        return [
            'checkout_url' => $fallbackUrl.$separator.http_build_query([
                'external_reference' => $reference,
                'payer_email' => $payerEmail,
            ]),
            'preapproval_id' => null,
            'preapproval_plan_id' => $planId,
            'status' => 'pending',
            'payload' => null,
        ];
    }
}
