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
        Schema::create('tarifa_descuento_cupones', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('nombre');
            $table->boolean('activo')->default(1);
            $table->integer('descuento');
            $table->text('habitaciones');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('clients')->onDelete('set null');
            $table->unsignedBigInteger('tipo_id')->nullable();
            $table->foreign('tipo_id')->references('id')->on('tarifa_descuento_tipos')->onDelete('set null');
            $table->unsignedBigInteger('precio_id')->nullable();
            $table->foreign('precio_id')->references('id')->on('tarifa_descuento_precios')->onDelete('set null');
            $table->unsignedBigInteger('user_registro_id')->nullable();
            $table->foreign('user_registro_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('user_actualizo_id')->nullable();
            $table->foreign('user_actualizo_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifa_descuento_cupones');
    }
};
