<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('check_sheets', function (Blueprint $table) {
            // tempat nyimpen definisi field dinamis (builder form)
            $table->json('fields')->nullable()->after('description');

            // tempat nyimpen meta (approval_flow, setting lain2)
            $table->json('meta')->nullable()->after('fields');
        });
    }

    public function down(): void
    {
        Schema::table('check_sheets', function (Blueprint $table) {
            $table->dropColumn(['fields', 'meta']);
        });
    }
};
