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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nombre1');
            $table->string('nombre2')->nullable();
            $table->string('apellido1');
            $table->string('apellido2')->nullable();
            $table->string('documento');
            $table->string('direccion')->nullable();
            $table->string('correo')->default('');
            $table->string('telefono');
            $table->string('telefono_alt')->nullable();
            $table->unsignedBigInteger('tipo_documento_id')->nullable();
            $table->foreign('tipo_documento_id')->references('id')->on('cliente_tipo_documento')->onDelete('set null');
            $table->unsignedBigInteger('pais_id')->nullable();
            $table->foreign('pais_id')->references('id')->on('direcciones_paises')->onDelete('set null');
            $table->unsignedBigInteger('departamento_id')->nullable();
            $table->foreign('departamento_id')->references('id')->on('direcciones_departamentos')->onDelete('set null');
            $table->unsignedBigInteger('ciudad_id')->nullable();
            $table->foreign('ciudad_id')->references('id')->on('direcciones_ciudades')->onDelete('set null');
            $table->unsignedBigInteger('tipo_persona_id')->nullable();
            $table->foreign('tipo_persona_id')->references('id')->on('cliente_tipo_persona')->onDelete('set null');
            $table->unsignedBigInteger('tipo_obligacion_id')->nullable();
            $table->foreign('tipo_obligacion_id')->references('id')->on('cliente_tipo_obligacion')->onDelete('set null');
            $table->unsignedBigInteger('tipo_regimen_id')->nullable();
            $table->foreign('tipo_regimen_id')->references('id')->on('cliente_tipo_regimen')->onDelete('set null');
            $table->string('observacion')->default('')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
