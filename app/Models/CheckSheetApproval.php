<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckSheetApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'check_sheet_submission_id',
        'reviewer_id',
        'status',
        'note',
    ];

    public function submission()
    {
        return $this->belongsTo(CheckSheetSubmission::class, 'check_sheet_submission_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
