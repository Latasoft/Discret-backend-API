<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tarifa_habitacion', function (Blueprint $table) {
            $table->bigIncrements('id_tarifa_habitacion');

            $table->decimal('precio', 10, 2);
            $table->integer('duracion_horas');

            $table->date('fecha_inicio');
            $table->date('fecha_fin');

            $table->boolean('activa')->default(true);

            $table->unsignedBigInteger('id_tipo_habitacion');

            $table->foreign('id_tipo_habitacion')
                ->references('id_tipo_habitacion')
                ->on('tipo_habitacion')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifa_habitacion');
    }
};
