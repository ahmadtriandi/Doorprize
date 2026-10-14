<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mode acak vs ditentukan dihapus.
     *
     * Sekarang setiap hadiah selalu diundi acak, dan panitia bisa mengunci
     * posisi tertentu lewat tabel prize_picks. Hadiah berkuota 50 dengan satu
     * posisi terkunci berarti 1 nama sudah pasti dan 49 sisanya tetap diundi.
     */
    public function up(): void
    {
        Schema::table('prizes', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }

    public function down(): void
    {
        Schema::table('prizes', function (Blueprint $table) {
            $table->string('mode', 20)->default('acak')->after('name');
        });
    }
};
