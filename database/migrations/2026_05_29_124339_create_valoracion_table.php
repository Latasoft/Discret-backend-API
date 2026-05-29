<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valoracion', function (Blueprint $table) {

            $table->bigIncrements('id_valoracion');

            $table->tinyInteger('puntuacion');

            $table->text('comentario')->nullable();

            $table->enum('estado', [
                'VISIBLE',
                'OCULTA'
            ])->default('VISIBLE');

            $table->unsignedBigInteger('id_reserva');
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_habitacion');
            $table->unsignedBigInteger('id_motel');

            $table->timestamps();

            /*
            |----------------------------------------
            | FOREIGN KEYS
            |----------------------------------------
            */

            $table->foreign('id_reserva')
                ->references('id_reserva')
                ->on('reserva')
                ->onDelete('cascade');

            $table->foreign('id_cliente')
                ->references('id_cliente')
                ->on('cliente')
                ->onDelete('cascade');

            $table->foreign('id_habitacion')
                ->references('id_habitacion')
                ->on('habitacion')
                ->onDelete('cascade');

            $table->foreign('id_motel')
                ->references('id_motel')
                ->on('motel')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valoracion');
    }
};