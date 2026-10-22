<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('balikgratis_pemesanan', function (Blueprint $t) {
            $t->string('no_hp')->nullable();
            $t->string('no_kk', 16)->nullable();
            $t->string('ip_pendaftar', 45)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('balikgratis_pemesanan', function (Blueprint $t) {
            $t->dropColumn(['no_hp', 'no_kk', 'ip_pendaftar']);
        });
    }
};
