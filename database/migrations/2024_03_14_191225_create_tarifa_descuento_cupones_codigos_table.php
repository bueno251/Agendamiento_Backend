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
        Schema::create('tarifa_descuento_cupones_codigos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo');
            $table->boolean('activo')->default(1);
            $table->boolean('usado')->default(0);
            $table->unsignedBigInteger('cupon_id')->nullable();
            $table->foreign('cupon_id')->references('id')->on('tarifa_descuento_cupones')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifa_descuento_cupones_codigos');
    }
};
