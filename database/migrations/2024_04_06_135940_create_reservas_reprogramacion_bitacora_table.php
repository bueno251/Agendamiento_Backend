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
        Schema::create('reservas_reprogramacion_bitacora', function (Blueprint $table) {
            $table->id();
            $table->date('nueva_fecha_entrada');
            $table->date('nueva_fecha_salida');
            $table->date('antigua_fecha_entrada');
            $table->date('antigua_fecha_salida');
            $table->unsignedBigInteger('reserva_id')->nullable()->unique();
            $table->foreign('reserva_id')->references('id')->on('reservas')->onDelete('set null');
            $table->unsignedBigInteger('motivo_id')->nullable()->unique();
            $table->foreign('motivo_id')->references('id')->on('reservas_reprogramacion_motivos')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas_reprogramacion_bitacora');
    }
};
