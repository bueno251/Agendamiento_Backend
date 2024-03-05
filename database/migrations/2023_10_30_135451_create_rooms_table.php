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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('room_padre_id')->nullable();
            $table->foreign('room_padre_id')->references('id')->on('room_padre')->onDelete('set null');
            $table->unsignedBigInteger('room_estado_id')->nullable();
            $table->foreign('room_estado_id')->references('id')->on('room_estados')->onDelete('set null');
            $table->boolean('habilitada')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
