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
        Schema::create('reservas_temporales', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_entrada');
            $table->date('fecha_salida');
            $table->unsignedBigInteger('origen_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('estado_id')->nullable();
            $table->unsignedBigInteger('desayuno_id')->nullable();
            $table->unsignedBigInteger('decoracion_id')->nullable();
            $table->integer('adultos');
            $table->integer('niños');
            $table->integer('precio');
            $table->integer('abono')->default(0);
            $table->text('descuentos')->nullable();
            $table->text('cupon')->nullable();
            $table->boolean('tarifa_especial')->default(0);
            $table->boolean('es_extrangero')->default(0);
            $table->string('comprobante')->nullable();
            $table->boolean('verificacion_pago');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
