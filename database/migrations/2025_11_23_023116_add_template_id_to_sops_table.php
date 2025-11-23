<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sops', function (Blueprint $table) {
            if (!Schema::hasColumn('sops','template_id')) {
                $table->unsignedBigInteger('template_id')->nullable()->after('id');
                $table->index('template_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sops', function (Blueprint $table) {
            if (Schema::hasColumn('sops','template_id')) {
                $table->dropIndex(['template_id']);
                $table->dropColumn('template_id');
            }
        });
    }
};
