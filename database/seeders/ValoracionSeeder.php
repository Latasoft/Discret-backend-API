<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ValoracionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('valoracion')->insert([

            [
                'puntuacion' => 5,
                'comentario' => 'Excelente habitación',

                'estado' => 'VISIBLE',

                'id_reserva' => 1,
                'id_cliente' => 1,
                'id_habitacion' => 1,
                'id_motel' => 1,

                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}