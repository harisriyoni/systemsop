<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_sheet_approvals', function (Blueprint $table) {
            $table->id();

            // submission yang di-approve/reject
            $table->foreignId('check_sheet_submission_id')
                ->constrained('check_sheet_submissions')
                ->cascadeOnDelete();

            // user yg approve/reject
            $table->foreignId('reviewer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // status per reviewer
            $table->enum('status', ['approved', 'rejected']);

            // catatan opsional
            $table->text('note')->nullable();

            $table->timestamps();

            // satu reviewer hanya boleh 1 record per submission
            $table->unique(
                ['check_sheet_submission_id', 'reviewer_id'],
                'check_sheet_approvals_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_sheet_approvals');
    }
};
