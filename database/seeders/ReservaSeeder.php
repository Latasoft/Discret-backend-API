<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReservaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('reserva')->insert([

            [
                'codigo_reserva' => 'RES-001',

                'hora_inicio' => now(),
                'hora_fin' => now()->addHours(3),

                'total' => 25000,

                'estado' => 'CONFIRMADA',

                'comentario' => 'Reserva cliente Juan Pérez',

                'id_habitacion' => 1,
                'id_cliente' => 1,
                'id_motel' => 1,

                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'codigo_reserva' => 'RES-002',

                'hora_inicio' => now()->addHours(4),
                'hora_fin' => now()->addHours(7),

                'total' => 35000,

                'estado' => 'PENDIENTE',

                'comentario' => 'Reserva pendiente confirmación',

                'id_habitacion' => 2,
                'id_cliente' => 1,
                'id_motel' => 1,

                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}