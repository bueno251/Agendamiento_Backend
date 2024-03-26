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
        Schema::create('config_defecto_form_reserva', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_documento_id')->nullable();
            $table->foreign('tipo_documento_id')->references('id')->on('cliente_tipo_documento')->onDelete('set null');
            $table->unsignedBigInteger('pais_residencia_id')->nullable();
            $table->foreign('pais_residencia_id')->references('id')->on('direcciones_paises')->onDelete('set null');
            $table->unsignedBigInteger('departamento_residencia_id')->nullable();
            $table->foreign('departamento_residencia_id')->references('id')->on('direcciones_departamentos')->onDelete('set null');
            $table->unsignedBigInteger('ciudad_residencia_id')->nullable();
            $table->foreign('ciudad_residencia_id')->references('id')->on('direcciones_ciudades')->onDelete('set null');
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
        Schema::dropIfExists('config_defecto_form_reserva');
    }
};
