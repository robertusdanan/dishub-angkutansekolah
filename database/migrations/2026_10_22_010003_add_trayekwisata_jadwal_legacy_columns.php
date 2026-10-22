<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trayekwisata_jadwal', function (Blueprint $t) {
            if (!Schema::hasColumn('trayekwisata_jadwal', 'driver_id')) $t->uuid('driver_id')->nullable()->index();
            if (!Schema::hasColumn('trayekwisata_jadwal', 'tahun')) $t->unsignedInteger('tahun')->nullable()->index();
            if (!Schema::hasColumn('trayekwisata_jadwal', 'bulan')) $t->unsignedTinyInteger('bulan')->nullable()->index();
            if (!Schema::hasColumn('trayekwisata_jadwal', 'minggu_ke')) $t->unsignedTinyInteger('minggu_ke')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('trayekwisata_jadwal', function (Blueprint $t) {
            foreach (['driver_id', 'tahun', 'bulan', 'minggu_ke'] as $column) {
                if (Schema::hasColumn('trayekwisata_jadwal', $column)) $t->dropColumn($column);
            }
        });
    }
};
