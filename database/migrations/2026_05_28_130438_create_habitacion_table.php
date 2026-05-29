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
        Schema::create('habitacion', function (Blueprint $table) {
            $table->bigIncrements('id_habitacion');

            $table->integer('numero');
            $table->string('nombre', 50);
            $table->string('estado', 30);

            $table->boolean('activa')->default(true);

            $table->unsignedBigInteger('id_tipo_habitacion');
            $table->unsignedBigInteger('id_motel');

            $table->foreign('id_tipo_habitacion')
                ->references('id_tipo_habitacion')
                ->on('tipo_habitacion');

            $table->foreign('id_motel')
                ->references('id_motel')
                ->on('motel')
                ->onDelete('cascade');

            $table->timestamps();

            $table->unique(['id_motel', 'numero']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habitacion');
    }
};
