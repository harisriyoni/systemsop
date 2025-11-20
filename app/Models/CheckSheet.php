<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'department',
        'product',
        'line',
        'status',
        'description',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions()
    {
        return $this->hasMany(CheckSheetSubmission::class);
    }
}
