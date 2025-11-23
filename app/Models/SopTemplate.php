<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SopTemplate extends Model
{
    protected $fillable = [
        'name','code','department','product','line',
        'form_schema','builder_schema','meta',
        'is_active','created_by','updated_by',
    ];

    protected $casts = [
        'form_schema'    => 'array',
        'builder_schema' => 'array',
        'meta'           => 'array',
        'is_active'      => 'boolean',
    ];

    // relasi opsional
    public function sops()
    {
        return $this->hasMany(Sop::class, 'template_id');
    }
}
