<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {

            $table->bigIncrements('id_usuario');

            $table->string('nombre', 50);
            $table->string('email', 100);
            $table->string('password_hash', 255);

            $table->enum('rol', ['ADMIN', 'RECEPCION', 'LIMPIEZA']);

            $table->timestamp('ultimo_login')->nullable();

            $table->boolean('activo')->default(true);

            $table->unsignedBigInteger('id_motel');

            $table->timestamps();

            // unicidad por motel
            $table->unique(['id_motel', 'email']);

            // FK motel
            $table->foreign('id_motel')
                ->references('id_motel')
                ->on('motel')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};