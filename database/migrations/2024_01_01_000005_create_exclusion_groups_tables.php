<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nama tabel sengaja bukan "groups": GROUPS adalah kata kunci cadangan
        // MySQL 8 dan menyulitkan penulisan query mentah di kemudian hari.
        Schema::create('exclusion_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('exclusion_group_participant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exclusion_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();

            $table->unique(['exclusion_group_id', 'participant_id'], 'grup_peserta_unik');
        });

        Schema::create('exclusion_group_prize', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exclusion_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prize_id')->constrained()->cascadeOnDelete();

            $table->unique(['exclusion_group_id', 'prize_id'], 'grup_hadiah_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exclusion_group_prize');
        Schema::dropIfExists('exclusion_group_participant');
        Schema::dropIfExists('exclusion_groups');
    }
};
