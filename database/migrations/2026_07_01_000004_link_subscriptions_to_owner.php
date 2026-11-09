<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suscripcion', function (Blueprint $table) {
            $table->foreignId('propietario_id')
                ->nullable()
                ->after('id_motel')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('referencia_interna', 80)->nullable()->unique()->after('propietario_id');
            $table->string('correo_pagador')->nullable()->after('referencia_interna');
        });
    }

    public function down(): void
    {
        Schema::table('suscripcion', function (Blueprint $table) {
            $table->dropForeign(['propietario_id']);
            $table->dropUnique(['referencia_interna']);
            $table->dropColumn(['propietario_id', 'referencia_interna', 'correo_pagador']);
        });
    }
};
