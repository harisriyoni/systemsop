<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_sheet_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('check_sheet_id')->constrained('check_sheets')->cascadeOnDelete();
            $table->foreignId('operator_id')->constrained('users')->cascadeOnDelete();

            $table->enum('status', ['submitted','under_review','approved','rejected'])
                ->default('submitted');

            $table->json('data')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_sheet_submissions');
    }
};
