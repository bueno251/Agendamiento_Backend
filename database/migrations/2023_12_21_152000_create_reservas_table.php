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
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_entrada');
            $table->date('fecha_salida');
            $table->unsignedBigInteger('origen_id')->nullable();
            $table->foreign('origen_id')->references('id')->on('reservas_origen')->onDelete('set null');
            $table->unsignedBigInteger('room_id')->nullable();
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('estado_id')->nullable();
            $table->foreign('estado_id')->references('id')->on('reserva_estados')->onDelete('set null');
            $table->unsignedBigInteger('desayuno_id')->nullable();
            $table->foreign('desayuno_id')->references('id')->on('room_desayunos')->onDelete('set null');
            $table->unsignedBigInteger('decoracion_id')->nullable();
            $table->foreign('decoracion_id')->references('id')->on('room_decoraciones')->onDelete('set null');
            $table->integer('adultos');
            $table->integer('niños')->default(0);
            $table->integer('precio');
            $table->integer('abono')->default(0);
            $table->text('descuentos')->nullable();
            $table->text('cupon')->nullable();
            $table->boolean('tarifa_especial')->default(0);
            $table->boolean('es_extrangero')->default(0);
            $table->string('comprobante')->nullable();
            $table->boolean('verificacion_pago');
            $table->softDeletes();
            $table->timestamps();
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
