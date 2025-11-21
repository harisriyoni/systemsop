<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'department',
        'product',
        'line',

        // ✅ tambahan baru
        'photos',     // JSON array foto + deskripsi
        'pin',        // PIN akses (opsional)
        'is_public',  // publik / tidak

        'status',
        'is_approved_produksi',
        'is_approved_qa',
        'is_approved_logistik',
        'content',
        'effective_from',
        'effective_to',
        'created_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',

        'is_approved_produksi' => 'boolean',
        'is_approved_qa' => 'boolean',
        'is_approved_logistik' => 'boolean',

        // ✅ tambahan baru
        'photos' => 'array',      // auto decode JSON jadi array
        'is_public' => 'boolean', // auto jadi true/false
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Helper biar aman kalau photos null
     */
    public function getPhotosSafeAttribute()
    {
        return $this->photos ?? [];
    }
}
