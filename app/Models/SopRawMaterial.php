<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SopRawMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'sop_id',
        'name',
        'amount',
        'unit',
        'image_path',
        'notes',
    ];

    protected $appends = ['image_url'];

    // Relasi balik ke SOP
    public function sop()
    {
        return $this->belongsTo(Sop::class);
    }

    // Accessor untuk mempermudah pemanggilan gambar di Blade/API
    // Contoh panggil: $material->image_url
    public function getImageUrlAttribute()
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::url($this->image_path);
        }
        
        // Return placeholder jika tidak ada gambar (opsional)
        return asset('images/placeholder-material.png'); 
    }
}