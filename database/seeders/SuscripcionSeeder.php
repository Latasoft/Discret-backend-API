<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuscripcionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('suscripcion')->insert([
            [
                'fecha_inicio' => now(),
                'fecha_fin' => now()->addMonth(),
                'estado' => 'ACTIVA',
                'metodo_pago' => 'TRANSFERENCIA',
                'monto_pagado' => 59990,
                'id_plan' => 2,
                'id_motel' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}