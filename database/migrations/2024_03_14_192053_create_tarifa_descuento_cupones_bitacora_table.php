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
        Schema::create('tarifa_descuento_cupones_bitacora', function (Blueprint $table) {
            $table->id();
            $table->string('cedula');
            $table->unsignedBigInteger('cupon_id')->nullable();
            $table->foreign('cupon_id')->references('id')->on('tarifa_descuento_cupones')->onDelete('set null');
            $table->unsignedBigInteger('codigo_id')->nullable();
            $table->foreign('codigo_id')->references('id')->on('tarifa_descuento_cupones_codigos')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifa_descuento_cupones_bitacora');
    }
};
