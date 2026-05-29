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
        Schema::create('reserva', function (Blueprint $table) {
            $table->bigIncrements('id_reserva');

            $table->string('codigo_reserva', 50)->unique();

            $table->timestamp('hora_inicio');
            $table->timestamp('hora_fin');

            $table->decimal('total', 10, 2);

            $table->string('estado', 50);
            $table->text('comentario')->nullable();

            $table->unsignedBigInteger('id_habitacion');
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_motel');

            $table->foreign('id_habitacion')
                ->references('id_habitacion')
                ->on('habitacion');

            $table->foreign('id_cliente')
                ->references('id_cliente')
                ->on('cliente');

            $table->foreign('id_motel')
                ->references('id_motel')
                ->on('motel')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserva');
    }
};
