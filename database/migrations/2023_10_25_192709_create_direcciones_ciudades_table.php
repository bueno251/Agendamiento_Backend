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
        Schema::create('direcciones_ciudades', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('direcciones_departamentos')->onDelete('restrict');
            $table->string('exportar_codigo_ciudad');
            $table->integer('id_munydpt')->nullable();
            $table->boolean('con_covertura')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direcciones_ciudades');
    }
};
