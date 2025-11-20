<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('department'); // QA / Logistik / Produksi
            $table->string('product')->nullable();
            $table->string('line')->nullable();

            $table->enum('status', ['draft','active','archived'])
                ->default('draft');

            $table->text('description')->nullable();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_sheets');
    }
};
