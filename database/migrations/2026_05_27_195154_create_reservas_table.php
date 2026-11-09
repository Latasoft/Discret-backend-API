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
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_reserva')->unique();
            $table->unsignedBigInteger('id_motel');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('habitacion_id')->nullable()->constrained('habitaciones')->nullOnDelete();
            $table->foreignId('tipo_habitacion_id')->nullable()->constrained('tipo_habitaciones')->nullOnDelete();

            $table->foreign('id_motel')
                ->references('id_motel')
                ->on('motel')
                ->onDelete('cascade');

            $table->foreignId('creada_por_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre_cliente')->nullable();
            $table->string('telefono_cliente')->nullable();
            $table->string('correo_cliente')->nullable();
            $table->unsignedTinyInteger('cantidad_personas')->default(1);
            $table->dateTime('fecha_entrada');
            $table->dateTime('fecha_salida');
            $table->enum('estado', ['pendiente', 'confirmada', 'ocupada', 'finalizada', 'cancelada'])->default('pendiente');
            $table->enum('tipo_pago', ['efectivo', 'transferencia', 'tarjeta', 'online'])->nullable();
            $table->text('comentario')->nullable();
            $table->string('qr_token')->unique()->nullable();
            $table->timestamp('qr_enviado_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
