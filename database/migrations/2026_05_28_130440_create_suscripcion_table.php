<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suscripcion', function (Blueprint $table) {

            $table->bigIncrements('id_suscripcion');

            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_fin');

            $table->enum('estado', ['ACTIVA', 'PENDIENTE', 'VENCIDA', 'SUSPENDIDA', 'TRIAL']);

            $table->string('metodo_pago', 30);
            $table->decimal('monto_pagado', 10, 2);

            $table->unsignedBigInteger('id_plan');
            $table->unsignedBigInteger('id_motel');

            $table->timestamps();

            $table->foreign('id_plan')
                ->references('id_plan')
                ->on('planes');

            $table->foreign('id_motel')
                ->references('id_motel')
                ->on('motel')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripcion');
    }
};
