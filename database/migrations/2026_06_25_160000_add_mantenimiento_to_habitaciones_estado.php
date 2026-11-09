<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE habitaciones MODIFY estado ENUM('disponible', 'ocupada', 'limpieza', 'mantenimiento', 'bloqueada') NOT NULL DEFAULT 'disponible'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("UPDATE habitaciones SET estado = 'limpieza' WHERE estado = 'mantenimiento'");
        DB::statement("ALTER TABLE habitaciones MODIFY estado ENUM('disponible', 'ocupada', 'limpieza', 'bloqueada') NOT NULL DEFAULT 'disponible'");
    }
};
