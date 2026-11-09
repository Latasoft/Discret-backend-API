<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporte', function (Blueprint $table) {

            $table->bigIncrements('id_reporte');

            $table->enum('tipo', [
                'DIARIO',
                'SEMANAL',
                'MENSUAL'
            ]);

            $table->date('fecha_inicio');
            $table->date('fecha_fin');

            $table->enum('estado', [
                'GENERADO',
                'PENDIENTE',
                'ERROR'
            ]);

            $table->decimal('total_ingresos', 10, 2);
            $table->integer('total_reservas');

            $table->decimal('ocupacion_promedio', 5, 2);

            $table->string('archivo_url', 255)->nullable();

            $table->unsignedBigInteger('id_motel');
            $table->unsignedBigInteger('user_id');

            $table->timestamps();

            $table->foreign('id_motel')
                ->references('id_motel')
                ->on('motel')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporte');
    }
};
