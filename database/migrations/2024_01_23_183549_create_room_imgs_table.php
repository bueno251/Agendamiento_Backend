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
        Schema::create('room_imgs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_padre_id')->nullable();
            $table->foreign('room_padre_id')->references('id')->on('room_padre')->onDelete('set null');
            $table->string('url');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_imgs');
    }
};
