<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asdp_lokasi', function (Blueprint $t) {
            $t->renameColumn('created_at', 'created');
            $t->renameColumn('updated_at', 'updated');
        });
    }

    public function down(): void
    {
        Schema::table('asdp_lokasi', function (Blueprint $t) {
            $t->renameColumn('created', 'created_at');
            $t->renameColumn('updated', 'updated_at');
        });
    }
};
