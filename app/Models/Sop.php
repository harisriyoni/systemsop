<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Route;

class Sop extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'version', // penting untuk versioning

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
        'form_schema',
        'builder_schema',
        'meta',
        'effective_from',
        'effective_to',

        'created_by',

        // ✅ SOP Template (kalau migration kamu sudah add template_id)
        'template_id',
    ];

    protected $casts = [
        'version' => 'integer',
        'template_id' => 'integer',

        'effective_from' => 'date',
        'effective_to'   => 'date',

        'is_approved_produksi' => 'boolean',
        'is_approved_qa'       => 'boolean',
        'is_approved_logistik' => 'boolean',

        'approved_at_produksi' => 'datetime',
        'approved_at_qa'       => 'datetime',
        'approved_at_logistik' => 'datetime',

        'rejected_at' => 'datetime',

        'photos'         => 'array',
        'form_schema'    => 'array',
        'builder_schema' => 'array',
        'meta'           => 'array',
        'is_public'      => 'boolean',
    ];

    // bisa langsung dipakai di blade: $sop->status_label dll
    protected $appends = [
        'photos_safe',
        'status_label',
        'status_badge_class',
        'is_expired',
        'qr_link',
        'template_name', // optional, aman walau template null
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

    // ✅ relasi ke SOP Template (kalau kamu bikin SopTemplate model)
    public function template()
    {
        return $this->belongsTo(SopTemplate::class, 'template_id');
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
     * UUID-safe: pakai MAX(version), bukan MAX(id).
     */
    public function scopeLatestPerCode($query)
    {
        return $query->whereIn('id', function ($sub) {
            $sub->select('s1.id')
                ->from('sops as s1')
                ->joinSub(
                    Sop::query()
                        ->selectRaw('code, MAX(version) as max_version')
                        ->groupBy('code'),
                    'mx',
                    function ($join) {
                        $join->on('s1.code', '=', 'mx.code')
                             ->on('s1.version', '=', 'mx.max_version');
                    }
                );
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
            default            => strtoupper((string) $this->status),
        };
    }

    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'draft'            => 'bg-slate-50 text-slate-700 border-slate-200',
            'waiting_approval' => 'bg-teal-50 text-teal-700 border-teal-200',
            'approved'         => 'bg-[#05727d] text-white border-[#05727d]',
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
        try {
            if ($this->is_public && Route::has('sop.public.show')) {
                return route('sop.public.show', $this);
            }
            if (Route::has('sop.show')) {
                return route('sop.show', $this);
            }
        } catch (\Throwable $e) {
            // kalau dipanggil di CLI / route belum ready
        }
        return '#';
    }

    public function getTemplateNameAttribute()
    {
        return $this->template?->name;
    }

    public function rawMaterials()
    {
        return $this->hasMany(SopRawMaterial::class, 'sop_id');
    }
}
