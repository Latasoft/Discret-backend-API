<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan', function (Blueprint $table) {

            $table->bigIncrements('id_plan');

            $table->string('nombre', 50);
            $table->decimal('precio', 10, 2);
            $table->integer('max_habitaciones');

            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan');
    }
};