<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('asdp_lokasi', 'created_at') && !Schema::hasColumn('asdp_lokasi', 'created')) {
            Schema::table('asdp_lokasi', fn (Blueprint $t) => $t->renameColumn('created_at', 'created'));
        }
        if (Schema::hasColumn('asdp_lokasi', 'updated_at') && !Schema::hasColumn('asdp_lokasi', 'updated')) {
            Schema::table('asdp_lokasi', fn (Blueprint $t) => $t->renameColumn('updated_at', 'updated'));
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('asdp_lokasi', 'created')) {
            Schema::table('asdp_lokasi', fn (Blueprint $t) => $t->renameColumn('created', 'created_at'));
        }
        if (Schema::hasColumn('asdp_lokasi', 'updated')) {
            Schema::table('asdp_lokasi', fn (Blueprint $t) => $t->renameColumn('updated', 'updated_at'));
        }
    }
};
