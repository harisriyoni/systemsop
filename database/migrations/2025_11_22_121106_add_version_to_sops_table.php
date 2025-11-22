<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sops', function (Blueprint $table) {
            // 1) drop unique code lama (karena sekarang boleh banyak versi)
            $table->dropUnique('sops_code_unique');

            // 2) tambah version
            $table->unsignedInteger('version')->default(1)->after('code');

            // 3) bikin unique gabungan code+version
            $table->unique(['code','version']);
        });

        // set default buat data lama biar aman
        DB::table('sops')->whereNull('version')->update(['version' => 1]);
    }

    public function down(): void
    {
        Schema::table('sops', function (Blueprint $table) {
            $table->dropUnique(['code','version']);
            $table->dropColumn('version');

            // balikin unique code seperti semula
            $table->unique('code');
        });
    }
};
