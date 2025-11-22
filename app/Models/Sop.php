<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'version', // ✅ penting untuk versioning

        'title',
        'department',
        'product',
        'line',

        'photos',     // JSON array foto + deskripsi
        'pin',        // PIN akses (opsional)
        'is_public',  // publik / tidak

        'status',

        'is_approved_produksi',
        'is_approved_qa',
        'is_approved_logistik',

        // opsional audit approval kalau kolomnya ada
        'approved_by_produksi',
        'approved_at_produksi',
        'approved_by_qa',
        'approved_at_qa',
        'approved_by_logistik',
        'approved_at_logistik',

        // opsional reject audit kalau kolomnya ada
        'rejected_reason',
        'rejected_by',
        'rejected_at',

        // opsional QR simpan path/url kalau kolomnya ada
        'qr_path',
        'qr_url',

        'content',
        'effective_from',
        'effective_to',

        'created_by',
    ];

    protected $casts = [
        'version' => 'integer',

        'effective_from' => 'date',
        'effective_to'   => 'date',

        'is_approved_produksi' => 'boolean',
        'is_approved_qa'       => 'boolean',
        'is_approved_logistik' => 'boolean',

        'approved_at_produksi' => 'datetime',
        'approved_at_qa'       => 'datetime',
        'approved_at_logistik' => 'datetime',

        'rejected_at' => 'datetime',

        'photos'    => 'array',
        'is_public' => 'boolean',
    ];

    // kalau mau langsung bisa dipakai di blade: $sop->status_label dll
    protected $appends = [
        'photos_safe',
        'status_label',
        'status_badge_class',
        'is_expired',
        'qr_link',
    ];

    // ==========================
    // RELATIONSHIPS
    // ==========================
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // optional auditor approver (kalau kolomnya ada)
    public function approvedByProduksi()
    {
        return $this->belongsTo(User::class, 'approved_by_produksi');
    }

    public function approvedByQa()
    {
        return $this->belongsTo(User::class, 'approved_by_qa');
    }

    public function approvedByLogistik()
    {
        return $this->belongsTo(User::class, 'approved_by_logistik');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Semua versi SOP dengan code sama
     */
    public function revisions()
    {
        return $this->hasMany(Sop::class, 'code', 'code')
            ->orderByDesc('version');
    }

    /**
     * Versi terbaru dari SOP ini (code sama)
     */
    public function latestRevision()
    {
        return $this->hasOne(Sop::class, 'code', 'code')
            ->orderByDesc('version');
    }

    // ==========================
    // SCOPES
    // ==========================
    /**
     * Ambil SOP hanya versi terbaru per code.
     * (kalau nanti mau list tanpa duplikat versi)
     */
    public function scopeLatestPerCode($query)
    {
        return $query->whereIn('id', function ($sub) {
            $sub->selectRaw('MAX(id)')
                ->from('sops')
                ->groupBy('code');
        });
    }

    // ==========================
    // ACCESSORS / HELPERS
    // ==========================
    public function getPhotosSafeAttribute()
    {
        return $this->photos ?? [];
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'draft'            => 'Draf',
            'waiting_approval' => 'Menunggu Persetujuan',
            'approved'         => 'Disetujui',
            'expired'          => 'Kedaluwarsa',
            default            => strtoupper($this->status),
        };
    }

    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'draft'            => 'bg-slate-50 text-slate-700 border-slate-200',
            'waiting_approval' => 'bg-blue-50 text-blue-700 border-blue-200',
            'approved'         => 'bg-blue-600 text-white border-blue-600',
            'expired'          => 'bg-slate-100 text-slate-500 border-slate-200',
            default            => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    public function getIsExpiredAttribute()
    {
        if (!$this->effective_to) return false;
        return now()->startOfDay()->gt($this->effective_to);
    }

    /**
     * Link QR yang dipakai di UI (public kalau public, internal kalau privat)
     */
    public function getQrLinkAttribute()
    {
        if ($this->is_public && \Route::has('sop.public.show')) {
            return route('sop.public.show', $this);
        }
        return route('sop.show', $this);
    }
}
