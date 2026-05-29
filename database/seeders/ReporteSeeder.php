<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReporteSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('reporte')->insert([
            [
                'tipo' => 'MENSUAL',
                'fecha_inicio' => now()->startOfMonth(),
                'fecha_fin' => now()->endOfMonth(),

                'estado' => 'GENERADO',

                'total_ingresos' => 850000,
                'total_reservas' => 42,
                'ocupacion_promedio' => 78.50,

                'archivo_url' => null,

                'id_motel' => 1,
                'id_usuario' => 1,

                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}