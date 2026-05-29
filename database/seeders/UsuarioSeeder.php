<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('usuario')->insert([
            [
                'nombre' => 'Administrador Sistema',
                'email' => 'admin@discret.com',
                'password_hash' => Hash::make('123456'),
                'rol' => 'ADMIN',
                'ultimo_login' => null,
                'activo' => 1,
                'id_motel' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Recepcionista',
                'email' => 'recepcion@discret.com',
                'password_hash' => Hash::make('123456'),
                'rol' => 'RECEPCION',
                'ultimo_login' => null,
                'activo' => 1,
                'id_motel' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Limpieza',
                'email' => 'limpieza@discret.com',
                'password_hash' => Hash::make('123456'),
                'rol' => 'LIMPIEZA',
                'ultimo_login' => null,
                'activo' => 1,
                'id_motel' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}