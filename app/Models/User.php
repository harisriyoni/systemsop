<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Kolom yang boleh di-mass assign.
     * Note: role/status boleh dimasukin kalau admin pakai mass-assign,
     * tapi pastikan controller profile TIDAK menerima input role/status.
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',

        // role & status
        'role',
        'status',

        // info karyawan / organisasi
        'employee_code',
        'phone',
        'company',
        'department',
        'position',
        'site',
        'join_date',

        // profil tambahan
        'avatar_path',
        'notes',

        // security/tracking
        'last_login_at',
        'last_login_ip',

        // audit
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'join_date'         => 'date',
        'last_login_at'     => 'datetime',
        'deleted_at'        => 'datetime',
    ];

    /**
     * Mutator: setiap set password lewat fill()/create() otomatis di-hash.
     */
    public function setPasswordAttribute($value)
    {
        if (!$value) return;

        // kalau sudah hash, jangan di-hash ulang
        $this->attributes['password'] =
            str_starts_with($value, '$2y$') ? $value : Hash::make($value);
    }

    // =========================
    // RELATIONS
    // =========================
    public function sops()
    {
        return $this->hasMany(Sop::class, 'created_by');
    }

    public function checkSheets()
    {
        return $this->hasMany(CheckSheet::class, 'created_by');
    }

    public function submissions()
    {
        return $this->hasMany(CheckSheetSubmission::class, 'operator_id');
    }

    // (opsional) siapa yg bikin akun ini
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // (opsional) siapa yg terakhir update akun ini
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // =========================
    // HELPERS
    // =========================
    public function isRole($roles): bool
    {
        $roles = (array) $roles;
        return in_array($this->role, $roles, true);
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    // =========================
    // ACCESSORS
    // =========================
    /**
     * Biar gampang panggil $user->avatar_url di blade.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar_path) return null;

        if (str_starts_with($this->avatar_path, 'http')) {
            return $this->avatar_path;
        }

        return Storage::disk('public')->url($this->avatar_path);
    }

    /**
     * Alias kompatibilitas untuk layout lama kalau masih pakai $user->photo_url
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->avatar_url;
    }
}
