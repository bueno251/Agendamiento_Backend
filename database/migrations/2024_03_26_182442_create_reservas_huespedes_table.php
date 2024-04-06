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
        Schema::create('reservas_huespedes', function (Blueprint $table) {
            $table->id();
            $table->boolean('responsable')->default(0);
            $table->unsignedBigInteger('reserva_id')->nullable();
            $table->foreign('reserva_id')->references('id')->on('reservas')->onDelete('set null');
            $table->unsignedBigInteger('reserva_temporal_id')->nullable();
            $table->foreign('reserva_temporal_id')->references('id')->on('reservas_temporales')->onDelete('set null');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('clients')->onDelete('set null');
            $table->unsignedBigInteger('motivo_id')->nullable();
            $table->foreign('motivo_id')->references('id')->on('reserva_motivos')->onDelete('set null');
            $table->unsignedBigInteger('pais_procedencia_id')->nullable();
            $table->foreign('pais_procedencia_id')->references('id')->on('direcciones_paises')->onDelete('set null');
            $table->unsignedBigInteger('departamento_procedencia_id')->nullable();
            $table->foreign('departamento_procedencia_id')->references('id')->on('direcciones_departamentos')->onDelete('set null');
            $table->unsignedBigInteger('ciudad_procedencia_id')->nullable();
            $table->foreign('ciudad_procedencia_id')->references('id')->on('direcciones_ciudades')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas_huespedes');
    }
};
