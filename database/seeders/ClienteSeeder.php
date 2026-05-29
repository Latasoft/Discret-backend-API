<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cliente')->insert([
            [
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'telefono' => '912345678',
                'email' => 'juan@mail.com',
                'fecha_nacimiento' => '1990-01-01',
                'id_motel' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
