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
        Schema::create('tipo_habitacion', function (Blueprint $table) {
            $table->bigIncrements('id_tipo_habitacion');

            $table->string('nombre', 50);
            $table->text('descripcion');

            $table->boolean('activa')->default(true);

            $table->unsignedBigInteger('id_motel');

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
        Schema::dropIfExists('tipo_habitacion');
    }
};
