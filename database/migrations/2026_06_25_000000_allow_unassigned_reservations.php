<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('reservas', 'tipo_habitacion_id')) {
            return;
        }

        Schema::table('reservas', function (Blueprint $table) {
            $table->dropForeign(['habitacion_id']);
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->foreignId('tipo_habitacion_id')
                ->nullable()
                ->after('habitacion_id')
                ->constrained('tipo_habitaciones')
                ->nullOnDelete();

            $table->foreignId('habitacion_id')
                ->nullable()
                ->change();

            $table->foreign('habitacion_id')
                ->references('id')
                ->on('habitaciones')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropForeign(['habitacion_id']);
            $table->dropForeign(['tipo_habitacion_id']);
            $table->dropColumn('tipo_habitacion_id');
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->foreignId('habitacion_id')
                ->nullable(false)
                ->change();

            $table->foreign('habitacion_id')
                ->references('id')
                ->on('habitaciones')
                ->cascadeOnDelete();
        });
    }
};
