<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token_hash', 64)->unique();
            $table->string('actor_type', 20);
            $table->unsignedBigInteger('actor_id');
            $table->unsignedBigInteger('id_motel')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->foreign('id_motel')->references('id_motel')->on('motel')->nullOnDelete();
            $table->index(['actor_type', 'actor_id']);
            $table->index(['id_motel', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
