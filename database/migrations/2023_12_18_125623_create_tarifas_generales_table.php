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
        Schema::create('tarifas_generales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->integer('precio');
            $table->unsignedBigInteger('estado_id')->nullable();
            $table->foreign('estado_id')->references('id')->on('tarifa_estados')->onDelete('set null');
            $table->unsignedBigInteger('impuesto_id')->nullable();
            $table->foreign('impuesto_id')->references('id')->on('impuestos')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifas_generales');
    }
};
