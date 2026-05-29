<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HabitacionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('habitacion')->insert([
            [
                'numero' => 101,
                'nombre' => 'Habitación 101',
                'estado' => 'LIBRE',
                'activa' => 1,
                'id_tipo_habitacion' => 1,
                'id_motel' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'numero' => 102,
                'nombre' => 'Habitación 102',
                'estado' => 'LIBRE',
                'activa' => 1,
                'id_tipo_habitacion' => 1,
                'id_motel' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'numero' => 103,
                'nombre' => 'Habitación 103',
                'estado' => 'MANTENIMIENTO',
                'activa' => 1,
                'id_tipo_habitacion' => 1,
                'id_motel' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
