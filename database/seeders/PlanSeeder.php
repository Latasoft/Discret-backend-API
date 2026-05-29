<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('planes')->insert([
            [
                'nombre' => 'BASE',
                'precio' => 49990,
                'max_habitaciones' => 10,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'DISCRET',
                'precio' => 59990,
                'max_habitaciones' => 25,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
