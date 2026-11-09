<?php

namespace Database\Seeders;

use App\Models\ConfiguracionSistema;
use App\Models\Habitacion;
use App\Models\Motel;
use App\Models\Plan;
use App\Models\TipoHabitacion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DiscretSeeder extends Seeder
{
    public function run(): void
    {
        $motel = Motel::updateOrCreate(
            ['slug' => 'motel-discret'],
            [
                'nombre' => 'Motel Discret',
                'direccion' => 'Direccion demo',
                'telefono' => '000000000',
                'email' => 'motel@demo.com',
                'ventana_limpieza' => 30,
                'activo' => true,
            ],
        );

        $admin = User::where('email', 'admin@discret.cl')
            ->orWhere('email', 'correo@ejemplo.com')
            ->first();

        if ($admin) {
            $admin->forceFill([
                'name' => 'Administrador',
                'email' => 'admin@discret.cl',
                'password' => Hash::make('discret2026#'),
                'id_motel' => $motel->id_motel,
            ])->save();
        } else {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@discret.cl',
                'password' => Hash::make('discret2026#'),
                'id_motel' => $motel->id_motel,
            ]);
        }

        Plan::updateOrCreate(
            ['nombre' => 'BASE'],
            [
                'precio' => 49990,
                'max_habitaciones' => 10,
                'activo' => true,
            ],
        );

        Plan::updateOrCreate(
            ['nombre' => 'DISCRET'],
            [
                'precio' => 59990,
                'max_habitaciones' => 30,
                'activo' => true,
            ],
        );

        $tipos = [
            ['nombre' => 'Habitacion estandar', 'descripcion' => 'Habitacion comoda y privada.', 'precio_base' => 25000],
            ['nombre' => 'Habitacion premium', 'descripcion' => 'Experiencia premium con mayor confort.', 'precio_base' => 35000],
            ['nombre' => 'Suite jacuzzi', 'descripcion' => 'Suite equipada con jacuzzi.', 'precio_base' => 45000],
            ['nombre' => 'Suite tematica', 'descripcion' => 'Suite con ambientacion especial.', 'precio_base' => 55000],
        ];

        foreach ($tipos as $tipo) {
            TipoHabitacion::updateOrCreate(
                [
                    'id_motel' => $motel->id_motel,
                    'nombre' => $tipo['nombre'],
                ],
                [
                    ...$tipo,
                    'id_motel' => $motel->id_motel,
                ],
            );
        }

        $tipoEstandar = TipoHabitacion::where('id_motel', $motel->id_motel)->where('nombre', 'Habitacion estandar')->firstOrFail();
        $tipoPremium = TipoHabitacion::where('id_motel', $motel->id_motel)->where('nombre', 'Habitacion premium')->firstOrFail();
        $tipoJacuzzi = TipoHabitacion::where('id_motel', $motel->id_motel)->where('nombre', 'Suite jacuzzi')->firstOrFail();
        $tipoTematica = TipoHabitacion::where('id_motel', $motel->id_motel)->where('nombre', 'Suite tematica')->firstOrFail();

        $habitaciones = [
            ['numero' => '101', 'tipo_habitacion_id' => $tipoEstandar->id, 'nombre' => 'Habitacion 101', 'descripcion' => 'Suite estandar', 'precio' => 25000, 'estado' => 'disponible'],
            ['numero' => '102', 'tipo_habitacion_id' => $tipoPremium->id, 'nombre' => 'Habitacion 102', 'descripcion' => 'Suite premium', 'precio' => 35000, 'estado' => 'ocupada'],
            ['numero' => '103', 'tipo_habitacion_id' => $tipoJacuzzi->id, 'nombre' => 'Habitacion 103', 'descripcion' => 'Suite jacuzzi', 'precio' => 45000, 'estado' => 'limpieza'],
            ['numero' => '104', 'tipo_habitacion_id' => $tipoTematica->id, 'nombre' => 'Habitacion 104', 'descripcion' => 'Suite tematica', 'precio' => 55000, 'estado' => 'ocupada'],
            ['numero' => '105', 'tipo_habitacion_id' => $tipoEstandar->id, 'nombre' => 'Habitacion 105', 'descripcion' => 'Suite estandar', 'precio' => 25000, 'estado' => 'disponible'],
            ['numero' => '106', 'tipo_habitacion_id' => $tipoPremium->id, 'nombre' => 'Habitacion 106', 'descripcion' => 'Suite premium', 'precio' => 35000, 'estado' => 'disponible'],
            ['numero' => '107', 'tipo_habitacion_id' => $tipoJacuzzi->id, 'nombre' => 'Habitacion 107', 'descripcion' => 'Suite jacuzzi', 'precio' => 45000, 'estado' => 'ocupada'],
            ['numero' => '108', 'tipo_habitacion_id' => $tipoTematica->id, 'nombre' => 'Habitacion 108', 'descripcion' => 'Suite tematica', 'precio' => 55000, 'estado' => 'disponible'],
        ];

        foreach ($habitaciones as $habitacion) {
            Habitacion::updateOrCreate(
                [
                    'id_motel' => $motel->id_motel,
                    'numero' => $habitacion['numero'],
                ],
                [
                    ...$habitacion,
                    'id_motel' => $motel->id_motel,
                ],
            );
        }

        ConfiguracionSistema::updateOrCreate(
            [
                'id_motel' => $motel->id_motel,
                'clave' => 'valoraciones_post_salida',
            ],
            [
                'id_motel' => $motel->id_motel,
                'valor' => [
                    'activo' => true,
                    'enviar_despues_de_minutos' => 30,
                    'canal' => 'correo',
                    'mensaje' => 'Gracias por visitarnos. Queremos conocer tu experiencia en DISCRET.',
                ],
                'descripcion' => 'Regla para enviar valoracion al cliente despues de la salida.',
            ],
        );
    }
}
