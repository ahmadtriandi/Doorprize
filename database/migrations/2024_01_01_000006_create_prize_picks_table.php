<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prizes', function (Blueprint $table) {
            // 'acak' = diundi server; 'ditentukan' = mengikuti daftar prize_picks
            $table->string('mode', 20)->default('acak')->after('name');
        });

        Schema::create('prize_picks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prize_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();

            $table->unique(['prize_id', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prize_picks');

        Schema::table('prizes', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }
};
