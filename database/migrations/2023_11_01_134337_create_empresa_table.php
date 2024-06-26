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
        Schema::create('empresa', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->default('');
            $table->string('codigo_rnt')->default('');
            $table->string('codigo_ciiu')->default('');
            $table->unsignedBigInteger('id_tipo_documento')->nullable();
            $table->foreign('id_tipo_documento')->references('id')->on('cliente_tipo_documento')->onDelete('set null');
            $table->string('identificacion')->default('')->unique();
            $table->string('dv')->default('');
            $table->string('registro_mercantil')->default('');
            $table->unsignedBigInteger('pais_id')->nullable();
            $table->foreign('pais_id')->references('id')->on('direcciones_paises')->onDelete('set null');
            $table->unsignedBigInteger('departamento_id')->nullable();
            $table->foreign('departamento_id')->references('id')->on('direcciones_departamentos')->onDelete('set null');
            $table->unsignedBigInteger('ciudad_id')->nullable();
            $table->foreign('ciudad_id')->references('id')->on('direcciones_ciudades')->onDelete('set null');
            $table->string('direccion')->default('');
            $table->string('correo')->default('');
            $table->string('telefono')->default('');
            $table->string('lenguaje')->default('');
            $table->string('impuesto')->default('');
            $table->unsignedBigInteger('id_operacion')->nullable();
            $table->foreign('id_operacion')->references('id')->on('empresa_tipo_operacion')->onDelete('set null');
            $table->unsignedBigInteger('id_entorno')->nullable();
            $table->foreign('id_entorno')->references('id')->on('empresa_tipo_entorno')->onDelete('set null');
            $table->unsignedBigInteger('id_organizacion')->nullable();
            $table->foreign('id_organizacion')->references('id')->on('cliente_tipo_persona')->onDelete('set null');
            $table->unsignedBigInteger('id_responsabilidad')->nullable();
            $table->foreign('id_responsabilidad')->references('id')->on('cliente_tipo_obligacion')->onDelete('set null');
            $table->unsignedBigInteger('id_regimen')->nullable();
            $table->foreign('id_regimen')->references('id')->on('cliente_tipo_regimen')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa');
    }
};
