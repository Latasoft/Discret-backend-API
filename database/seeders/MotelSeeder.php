<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MotelSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('motel')->insert([
            'nombre' => 'Discret Motel',
            'direccion' => 'Santiago Centro',
            'telefono' => '+56912345678',
            'email' => 'contacto@discret.com',
            'ventana_limpieza' => 30,
            'activo' => 1,
            'slug' => 'discret-motel',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
