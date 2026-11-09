<?php

namespace Tests\Feature;

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.mercadopago.access_token' => null]);
    }

    public function test_owner_can_create_motel_and_pending_subscription(): void
    {
        Plan::create([
            'nombre' => 'BASE',
            'precio' => 49990,
            'max_habitaciones' => 10,
            'activo' => true,
        ]);

        $response = $this->postJson('/api/onboarding/propietario', [
            'plan' => 'BASE',
            'propietario_nombre' => 'Carla Soto',
            'propietario_email' => 'carla@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'motel_nombre' => 'Motel Central',
            'motel_direccion' => 'Avenida Central 123',
            'motel_telefono' => '+56912345678',
            'motel_email' => 'contacto@motelcentral.cl',
            'acepta_terminos' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('admin.role', 'owner')
            ->assertJsonPath('motel.slug', 'motel-central')
            ->assertJsonPath('suscripcion.estado', 'PENDIENTE')
            ->assertJsonStructure(['token', 'checkout_url']);

        $this->assertDatabaseHas('users', [
            'email' => 'carla@example.com',
            'role' => 'owner',
        ]);
        $this->assertDatabaseHas('suscripcion', [
            'estado' => 'PENDIENTE',
            'correo_pagador' => 'carla@example.com',
        ]);

        $reference = $response->json('suscripcion.referencia_interna');

        $this->postJson('/api/webhooks/mercado-pago', [
            'id' => 'preapproval-test-001',
            'external_reference' => $reference,
            'status' => 'authorized',
        ])->assertOk();

        $this->assertDatabaseHas('suscripcion', [
            'referencia_interna' => $reference,
            'estado' => 'ACTIVA',
            'mp_preapproval_id' => 'preapproval-test-001',
        ]);
    }

    public function test_owner_email_cannot_be_registered_twice(): void
    {
        Plan::create([
            'nombre' => 'BASE',
            'precio' => 49990,
            'max_habitaciones' => 10,
            'activo' => true,
        ]);

        $payload = [
            'plan' => 'BASE',
            'propietario_nombre' => 'Carla Soto',
            'propietario_email' => 'carla@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'motel_nombre' => 'Motel Central',
            'motel_direccion' => 'Avenida Central 123',
            'motel_telefono' => '+56912345678',
            'motel_email' => 'contacto@motelcentral.cl',
            'acepta_terminos' => true,
        ];

        $this->postJson('/api/onboarding/propietario', $payload)->assertCreated();
        $this->postJson('/api/onboarding/propietario', [
            ...$payload,
            'motel_nombre' => 'Segundo Motel',
        ])->assertUnprocessable()->assertJsonValidationErrors('propietario_email');
    }
}
