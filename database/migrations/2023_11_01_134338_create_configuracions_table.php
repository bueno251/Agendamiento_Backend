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
            $table->boolean('usuario_reserva');
            $table->boolean('correo_obligatorio');
            $table->integer('porcentaje_separacion');
            $table->boolean('tarifas_generales');
            $table->integer('edad_tarifa_niños');
            $table->unsignedBigInteger('id_empresa')->nullable();
            $table->foreign('id_empresa')->references('id')->on('empresa')->onDelete('set null');
            $table->unsignedBigInteger('id_config')->nullable();
            $table->foreign('id_config')->references('id')->on('configuracion_defecto')->onDelete('set null');
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
