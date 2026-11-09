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
        Schema::create('tipo_habitaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_motel');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('precio_base')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_motel')
                ->references('id_motel')
                ->on('motel')
                ->onDelete('cascade');

            $table->unique(['id_motel', 'nombre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_habitaciones');
    }
};
