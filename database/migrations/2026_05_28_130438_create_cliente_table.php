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
        Schema::create('cliente', function (Blueprint $table) {
            $table->bigIncrements('id_cliente');

            $table->string('nombre', 30);
            $table->string('apellido', 50);
            $table->string('telefono', 50);
            $table->string('email', 100);

            $table->date('fecha_nacimiento')->nullable();

            $table->unsignedBigInteger('id_motel');

            $table->foreign('id_motel')
                ->references('id_motel')
                ->on('motel')
                ->onDelete('cascade');

            $table->timestamps();

            $table->unique(['id_motel', 'telefono']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente');
    }
};
