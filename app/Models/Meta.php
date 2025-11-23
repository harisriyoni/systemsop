<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    protected $fillable = [
        'metaable_id',
        'metaable_type',
        'key',
        'value',
        'group',
        'order',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public function metaable()
    {
        return $this->morphTo();
    }
}
