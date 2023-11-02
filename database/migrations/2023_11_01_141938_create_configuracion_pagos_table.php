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
        Schema::create('configuracion_pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('configuracion_id')->nullable();
            $table->foreign('configuracion_id')->references('id')->on('configuracions')->onDelete('set null');
            $table->unsignedBigInteger('tipo_pago_id')->nullable();
            $table->foreign('tipo_pago_id')->references('id')->on('tipo_pagos')->onDelete('set null');
            $table->unique(['configuracion_id', 'tipo_pago_id']);
            $table->integer('estado')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_pagos');
    }
};
