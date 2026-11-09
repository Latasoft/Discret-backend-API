<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\Habitacion;
use App\Models\Motel;
use App\Models\TipoHabitacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_only_sees_rooms_from_its_motel(): void
    {
        [$motelA, $roomA] = $this->createMotelWithRoom('motel-a', '101');
        [, $roomB] = $this->createMotelWithRoom('motel-b', '201');
        $token = $this->createAdminToken($motelA);

        $response = $this->withToken($token)
            ->getJson('/api/habitaciones?motel_slug=motel-b');

        $response->assertOk()
            ->assertJsonFragment(['id' => $roomA->id, 'numero' => '101'])
            ->assertJsonMissing(['id' => $roomB->id, 'numero' => '201']);
    }

    public function test_admin_cannot_update_room_from_another_motel(): void
    {
        [$motelA] = $this->createMotelWithRoom('motel-a', '101');
        [, $roomB] = $this->createMotelWithRoom('motel-b', '201');
        $token = $this->createAdminToken($motelA);

        $this->withToken($token)
            ->patchJson("/api/habitaciones/{$roomB->id}", ['nombre' => 'Intrusion'])
            ->assertNotFound();

        $this->assertDatabaseMissing('habitaciones', [
            'id' => $roomB->id,
            'nombre' => 'Intrusion',
        ]);
    }

    private function createAdminToken(Motel $motel): string
    {
        $admin = User::create([
            'name' => 'Admin '.$motel->slug,
            'email' => 'admin-'.$motel->slug.'@example.com',
            'password' => 'secret123',
            'id_motel' => $motel->id_motel,
            'role' => 'admin',
        ]);

        return ApiToken::issue('admin', $admin->id, $motel->id_motel);
    }

    private function createMotelWithRoom(string $slug, string $number): array
    {
        $motel = Motel::create([
            'nombre' => strtoupper($slug),
            'direccion' => 'Direccion de prueba',
            'telefono' => '123456789',
            'email' => "{$slug}@example.com",
            'ventana_limpieza' => 30,
            'activo' => true,
            'slug' => $slug,
        ]);
        $type = TipoHabitacion::create([
            'id_motel' => $motel->id_motel,
            'nombre' => "Estandar {$slug}",
            'descripcion' => 'Prueba',
            'precio_base' => 25000,
            'activo' => true,
        ]);
        $room = Habitacion::create([
            'id_motel' => $motel->id_motel,
            'tipo_habitacion_id' => $type->id,
            'numero' => $number,
            'nombre' => "Habitacion {$number}",
            'descripcion' => 'Prueba',
            'precio' => 25000,
            'estado' => 'disponible',
            'activa' => true,
        ]);

        return [$motel, $room];
    }
}
