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
        Schema::create('configuracions', function (Blueprint $table) {
            $table->id();
            $table->integer('usuario_reserva');
            $table->string('correo')->unique()->default('');
            $table->string('telefono')->default('');
            $table->string('nombre_empresa')->default('');
            $table->string('nit')->default('');
            $table->string('ciudad')->default('');
            $table->string('departamento')->default('');
            $table->string('pais')->default('');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracions');
    }
};
