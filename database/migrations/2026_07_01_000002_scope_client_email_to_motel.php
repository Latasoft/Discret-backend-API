<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique('clientes_correo_unique');
            $table->unique(['id_motel', 'correo'], 'clientes_motel_correo_unique');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique('clientes_motel_correo_unique');
            $table->unique('correo');
        });
    }
};
