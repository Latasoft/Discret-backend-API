<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suscripcion', function (Blueprint $table) {
            $table->string('mp_preapproval_id')->nullable()->after('monto_pagado');
            $table->string('mp_preapproval_plan_id')->nullable()->after('mp_preapproval_id');
            $table->string('mp_status', 50)->nullable()->after('mp_preapproval_plan_id');
            $table->string('mp_checkout_url')->nullable()->after('mp_status');
            $table->json('mp_payload')->nullable()->after('mp_checkout_url');
        });
    }

    public function down(): void
    {
        Schema::table('suscripcion', function (Blueprint $table) {
            $table->dropColumn([
                'mp_preapproval_id',
                'mp_preapproval_plan_id',
                'mp_status',
                'mp_checkout_url',
                'mp_payload',
            ]);
        });
    }
};
