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
        Schema::create('decoracion_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('decoracion_id')->nullable();
            $table->foreign('decoracion_id')->references('id')->on('decoraciones')->onDelete('set null');
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
        Schema::dropIfExists('decoracion_media');
    }
};
