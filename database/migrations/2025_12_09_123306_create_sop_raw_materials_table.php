<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop_raw_materials', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel sops (karena Sop pakai UUID, kita pakai foreignUuid)
            $table->foreignUuid('sop_id')
                  ->constrained('sops')
                  ->cascadeOnDelete(); // Jika SOP dihapus, material ikut terhapus

            $table->string('name'); // Nama Row Material (contoh: Phosphorous Acid)
            $table->decimal('amount', 10, 2); // Isi angka (contoh: 49.86)
            $table->string('unit')->default('kg'); // Satuan (default kg, sesuai gambar)
            $table->string('image_path')->nullable(); // Untuk menyimpan path foto upload
            $table->text('notes')->nullable(); // Keterangan

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_raw_materials');
    }
};