<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prizes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sponsor')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('quota')->default(1);      // jumlah pemenang
            $table->unsignedInteger('per_draw')->default(1);   // pemenang per putaran
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_selected')->default(false);    // hadiah yang sedang diundi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prizes');
    }
};
