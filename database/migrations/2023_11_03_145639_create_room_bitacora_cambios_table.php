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
        Schema::create('room_bitacora_cambios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->foreign('room_id')->references('id')->on('room_padre')->onDelete('set null');
            $table->unsignedBigInteger('estado_anterior_id')->nullable();
            $table->foreign('estado_anterior_id')->references('id')->on('room_estados')->onDelete('set null');
            $table->unsignedBigInteger('estado_nuevo_id')->nullable();
            $table->foreign('estado_nuevo_id')->references('id')->on('room_estados')->onDelete('set null');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->string('motivo');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_bitacora_cambios');
    }
};
