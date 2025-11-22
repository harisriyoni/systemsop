<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('check_sheets', function (Blueprint $table) {
            // simpan path file qr (kalau kamu generate file)
            $table->string('qr_path')->nullable()->after('status');

            // simpan url form fill (buat QR)
            $table->string('qr_url')->nullable()->after('qr_path');
        });
    }

    public function down(): void
    {
        Schema::table('check_sheets', function (Blueprint $table) {
            $table->dropColumn(['qr_path', 'qr_url']);
        });
    }
};
