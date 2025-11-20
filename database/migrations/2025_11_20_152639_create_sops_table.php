<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sops', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('department'); // Produksi / QA / Logistik / etc
            $table->string('product')->nullable();
            $table->string('line')->nullable();

            $table->enum('status', ['draft','waiting_approval','approved','expired'])
                ->default('draft');

            // approval 3 departemen
            $table->boolean('is_approved_produksi')->default(false);
            $table->boolean('is_approved_qa')->default(false);
            $table->boolean('is_approved_logistik')->default(false);

            $table->longText('content')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sops');
    }
};
