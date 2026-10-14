<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prize_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('round')->default(1);
            $table->boolean('is_claimed')->default(false);
            $table->timestamp('drawn_at')->nullable();
            $table->timestamps();

            $table->unique(['prize_id', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winners');
    }
};
