<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SopTemplate extends Model
{
    protected $table = 'sop_templates';

    protected $fillable = [
        'name',
        'code',
        'department',
        'product',
        'line',
        'form_schema',
        'builder_schema',
        'meta',
        'canvas',          // <- NEW
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'form_schema'    => 'array',
        'builder_schema' => 'array',
        'meta'           => 'array',
        'canvas'         => 'array',   // <- NEW
        'is_active'      => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function sops()
    {
        return $this->hasMany(Sop::class, 'template_id');
    }
}
