<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::create('sops', function (Blueprint $table) {
        // ID pakai UUID
        $table->uuid('id')->primary();

        $table->string('code')->unique();
        $table->string('title');
        $table->string('department'); // Produksi / QA / Logistik / dll
        $table->string('product')->nullable();
        $table->string('line')->nullable();

        // Foto / lampiran (sudah ada)
        // contoh isi:
        // [
        //   {"path":"sops/a.jpg","desc":"Foto area kerja"},
        //   {"path":"sops/b.jpg","desc":"Lampiran"}
        // ]
        $table->json('photos')->nullable();

        // PIN akses (opsional)
        $table->string('pin')->nullable();

        // akses publik
        $table->boolean('is_public')->default(false);
        $table->index('is_public');

        $table->enum('status', ['draft','waiting_approval','approved','expired'])
            ->default('draft');

        // approval 3 departemen
        $table->boolean('is_approved_produksi')->default(false);
        $table->boolean('is_approved_qa')->default(false);
        $table->boolean('is_approved_logistik')->default(false);

        // isi SOP bebas (rich text / markdown)
        $table->longText('content')->nullable();

        // 🔥 Tambahan: schema builder SOP (dipakai fitur Check Sheet)
        // contoh:
        // [
        //   {"name":"Persiapan","items":[{"label":"Area bersih","type":"checkbox"}]}
        // ]
        

        $table->date('effective_from')->nullable();
        $table->date('effective_to')->nullable();

        // relasi ke users (tetap BIGINT, gak usah dipaksain UUID kalau user belum siap)
        $table->foreignId('created_by')
            ->constrained('users')
            ->cascadeOnDelete();

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sops');
    }
};  