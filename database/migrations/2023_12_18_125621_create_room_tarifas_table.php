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
        Schema::create('room_tarifas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->foreign('room_id')->references('id')->on('room_padre')->onDelete('set null');
            $table->unsignedBigInteger('jornada_id')->nullable();
            $table->foreign('jornada_id')->references('id')->on('tarifa_jornada')->onDelete('set null');
            $table->unsignedBigInteger('estado_id')->nullable();
            $table->foreign('estado_id')->references('id')->on('tarifa_estados')->onDelete('set null');
            $table->string('dia_semana');
            $table->integer('precio');
            $table->unique(['room_id', 'dia_semana'], 'unique_room_day_tarifa');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_tarifas');
    }
};
