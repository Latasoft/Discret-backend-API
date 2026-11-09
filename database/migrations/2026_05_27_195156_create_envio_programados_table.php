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
        Schema::create('envios_programados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_motel')->nullable();
            $table->foreignId('reserva_id')->nullable()->constrained('reservas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->enum('tipo', ['confirmacion_reserva', 'qr_porteria', 'valoracion_post_salida']);
            $table->enum('canal', ['correo', 'whatsapp']);
            $table->string('destinatario');
            $table->string('asunto')->nullable();
            $table->text('mensaje');
            $table->dateTime('programado_para');
            $table->timestamp('enviado_at')->nullable();
            $table->enum('estado', ['pendiente', 'enviado', 'fallido'])->default('pendiente');
            $table->text('error')->nullable();
            $table->timestamps();

            $table->foreign('id_motel')
                ->references('id_motel')
                ->on('motel')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('envios_programados');
    }
};
