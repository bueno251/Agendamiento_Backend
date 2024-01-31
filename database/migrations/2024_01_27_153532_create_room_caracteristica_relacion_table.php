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
        Schema::create('room_caracteristica_relacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('no action');
            $table->unsignedBigInteger('caracteristica_id')->nullable();
            $table->foreign('caracteristica_id')->references('id')->on('room_caracteristicas')->onDelete('set null');
            $table->boolean('estado')->default(1);
            $table->unique(['room_id', 'caracteristica_id'], 'room_caracteristica');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_caracteristica_relacion');
    }
};
