<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('balikgratis_pemesanan', 'verifikasi_oleh')) {
            Schema::table('balikgratis_pemesanan', function (Blueprint $table): void {
                $table->string('verifikasi_oleh')->nullable()->after('verifikasi_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('balikgratis_pemesanan', 'verifikasi_oleh')) {
            Schema::table('balikgratis_pemesanan', fn (Blueprint $table) => $table->dropColumn('verifikasi_oleh'));
        }
    }
};
