<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sop_templates', function (Blueprint $table) {
            $table->id();

            $table->string('name');                 // nama template
            $table->string('code')->nullable();     // kode internal template (opsional)

            $table->string('department')->nullable();
            $table->string('product')->nullable();
            $table->string('line')->nullable();

            $table->json('form_schema')->nullable();
            $table->json('builder_schema')->nullable();
            $table->json('meta')->nullable();

            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->index(['name','department','is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_templates');
    }
};
