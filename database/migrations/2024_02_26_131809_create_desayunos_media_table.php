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
        Schema::create('desayunos_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('desayuno_id')->nullable();
            $table->foreign('desayuno_id')->references('id')->on('desayunos')->onDelete('set null');
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
        Schema::dropIfExists('desayunos_media');
    }
};
