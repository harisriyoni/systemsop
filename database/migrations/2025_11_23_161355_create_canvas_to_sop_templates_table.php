<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sop_templates', function (Blueprint $table) {
            // simpan snapshot penuh canvas (page + blocks)
            $table->json('canvas')->nullable()->after('builder_schema');
        });
    }

    public function down(): void
    {
        Schema::table('sop_templates', function (Blueprint $table) {
            $table->dropColumn('canvas');
        });
    }
};
