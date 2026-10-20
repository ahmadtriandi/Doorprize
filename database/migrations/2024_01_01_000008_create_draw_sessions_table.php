<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sesi undian: beberapa hadiah yang diundi bersamaan dalam satu layar,
     * dikelompokkan per baris (misalnya "25 PV" dan "50 PV").
     */
    public function up(): void
    {
        Schema::create('draw_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('rows_per_column')->default(6);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('prizes', function (Blueprint $table) {
            $table->foreignId('draw_session_id')->nullable()->after('id')
                  ->constrained('draw_sessions')->nullOnDelete();
            $table->unsignedSmallInteger('session_row')->default(1)->after('draw_session_id');
            $table->string('group_label', 50)->nullable()->after('session_row');
            $table->unsignedInteger('session_order')->default(0)->after('group_label');
        });
    }

    public function down(): void
    {
        Schema::table('prizes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('draw_session_id');
            $table->dropColumn(['session_row', 'group_label', 'session_order']);
        });

        Schema::dropIfExists('draw_sessions');
    }
};
