<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

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

    public function isRole($roles): bool
    {
        $roles = (array) $roles;
        return in_array($this->role, $roles, true);
    }
}
