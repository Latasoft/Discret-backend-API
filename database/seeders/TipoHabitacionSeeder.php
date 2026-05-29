<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoHabitacionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipo_habitacion')->insert([
            [
                'nombre' => 'Estándar',
                'descripcion' => 'Habitación básica con servicios esenciales',
                'activa' => 1,
                'id_motel' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'VIP',
                'descripcion' => 'Habitación premium con jacuzzi y mayor confort',
                'activa' => 1,
                'id_motel' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
