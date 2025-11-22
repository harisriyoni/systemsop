<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckSheet extends Model
{
    use HasFactory;

    // kalau nama tabelnya default "check_sheets", ini opsional aja
    protected $table = 'check_sheets';

    /**
     * Status constants biar konsisten di controller/view
     */
    public const STATUS_DRAFT    = 'draft';
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'title',
        'department',
        'product',
        'line',
        'status',
        'description',
        'created_by',

        // === QR fields (sesuai error kamu) ===
        'qr_path',
        'qr_url',
    ];

    /**
     * Casts (aman walau null)
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* =========================
     * RELATIONS
     * ========================= */

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions()
    {
        return $this->hasMany(CheckSheetSubmission::class, 'check_sheet_id');
    }

    /* =========================
     * SCOPES (buat query di controller)
     * ========================= */

    public function scopeActive($q)
    {
        return $q->where('status', self::STATUS_ACTIVE);
    }

    public function scopeDraft($q)
    {
        return $q->where('status', self::STATUS_DRAFT);
    }

    public function scopeInactive($q)
    {
        return $q->where('status', self::STATUS_INACTIVE);
    }

    /* =========================
     * ACCESSORS / HELPERS
     * ========================= */

    /**
     * Kalau qr_url belum diset, fallback otomatis ke route fill
     * jadi view QR Center tetap aman.
     */
    public function getQrUrlAttribute($value)
    {
        if (!empty($value)) return $value;

        // route helper aman dipakai di web context
        if (function_exists('route') && \Illuminate\Support\Facades\Route::has('check_sheets.fill')) {
            return route('check_sheets.fill', $this);
        }

        return null;
    }

    /**
     * Simple label untuk status (opsional buat badge)
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            self::STATUS_ACTIVE   => 'ACTIVE',
            self::STATUS_DRAFT    => 'DRAFT',
            self::STATUS_INACTIVE => 'INACTIVE',
            default               => strtoupper((string) $this->status),
        };
    }

    /**
     * Helper boolean
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
