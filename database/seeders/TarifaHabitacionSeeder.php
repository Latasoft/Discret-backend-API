<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TarifaHabitacionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tarifa_habitacion')->insert([
            [
                'precio' => 15000.00,
                'duracion_horas' => 2,
                'fecha_inicio' => now(),
                'fecha_fin' => now()->addYear(),
                'activa' => 1,
                'id_tipo_habitacion' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'precio' => 25000.00,
                'duracion_horas' => 4,
                'fecha_inicio' => now(),
                'fecha_fin' => now()->addYear(),
                'activa' => 1,
                'id_tipo_habitacion' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'precio' => 40000.00,
                'duracion_horas' => 6,
                'fecha_inicio' => now(),
                'fecha_fin' => now()->addYear(),
                'activa' => 1,
                'id_tipo_habitacion' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
