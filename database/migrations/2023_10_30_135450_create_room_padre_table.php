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
        Schema::create('room_padre', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion');
            $table->unsignedBigInteger('impuesto_id')->nullable();
            $table->foreign('impuesto_id')->references('id')->on('tarifa_impuestos')->onDelete('set null');
            $table->unsignedBigInteger('room_tipo_id')->nullable();
            $table->foreign('room_tipo_id')->references('id')->on('room_tipos')->onDelete('set null');
            $table->unsignedBigInteger('room_estado_id')->nullable();
            $table->foreign('room_estado_id')->references('id')->on('room_estados')->onDelete('set null');
            $table->integer('capacidad');
            $table->integer('cantidad')->default(1);
            $table->boolean('habilitada')->default(1);
            $table->boolean('has_desayuno');
            $table->boolean('has_decoracion');
            $table->boolean('incluye_desayuno');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_padre');
    }
};
