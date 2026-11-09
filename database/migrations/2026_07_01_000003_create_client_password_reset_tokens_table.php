<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_password_reset_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_motel');
            $table->string('email');
            $table->string('token');
            $table->timestamp('created_at')->nullable();

            $table->foreign('id_motel')->references('id_motel')->on('motel')->cascadeOnDelete();
            $table->unique(['id_motel', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_password_reset_tokens');
    }
};
