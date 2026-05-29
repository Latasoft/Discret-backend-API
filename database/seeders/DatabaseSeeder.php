<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MotelSeeder::class,
            PlanSeeder::class,
            TipoHabitacionSeeder::class,
            HabitacionSeeder::class,
            ClienteSeeder::class,
            UsuarioSeeder::class,
            TarifaHabitacionSeeder::class,
            ReservaSeeder::class,
            ValoracionSeeder::class,
        ]);
    }
}
