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
        Schema::create('tarifas_otas', function (Blueprint $table) {
            $table->id();
            $table->integer('precio');
            $table->boolean('es_porcentaje');
            $table->unsignedBigInteger('room_id')->nullable()->unique();
            $table->foreign('room_id')->references('id')->on('room_padre')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifas_otas');
    }
};
