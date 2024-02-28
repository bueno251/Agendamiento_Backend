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
            $table->text('descripcion');
            $table->unsignedBigInteger('room_padre_id')->nullable();
            $table->foreign('room_padre_id')->references('id')->on('room_padre')->onDelete('set null');
            $table->unsignedBigInteger('room_tipo_id')->nullable();
            $table->foreign('room_tipo_id')->references('id')->on('room_tipos')->onDelete('set null');
            $table->unsignedBigInteger('room_estado_id')->nullable();
            $table->foreign('room_estado_id')->references('id')->on('room_estados')->onDelete('set null');
            $table->integer('capacidad');
            $table->boolean('habilitada')->default(1);
            $table->boolean('has_desayuno');
            $table->boolean('has_decoracion');
            $table->boolean('incluye_desayuno');
            $table->softDeletes();
            $table->timestamps();
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
