@extends('layouts.app')

@section('title', 'Profile Saya')

@section('content')
@php
  $user = $user ?? auth()->user();

  /**
   * FIX PHOTO LOGIC:
   * - kalau avatar_path sudah URL (http/https) -> pakai langsung
   * - kalau avatar_path path storage -> Storage::disk('public')->url()
   * - fallback ke photo_url (kalau masih ada)
   */
  $photo = null;
  if ($user) {
      if (!empty($user->avatar_path)) {
          $photo = $user->avatar_path;

          if (!str_starts_with($photo, 'http')) {
              $photo = \Illuminate\Support\Facades\Storage::disk('public')->url($photo);
          }
      } elseif (!empty($user->photo_url)) {
          $photo = $user->photo_url;
      }
  }

  // Helper format tanggal
  $fmtDate = function($v, $withTime = false) {
      if (!$v) return '-';
      try {
          $c = \Carbon\Carbon::parse($v);
          return $withTime ? $c->format('d M Y H:i') : $c->format('d M Y');
      } catch (\Throwable $e) {
          return $v;
      }
  };

  // kalau di model ada relation creator/updater
  $creatorName = optional($user->creator)->name ?? ($user->created_by ?? '-');
  $updaterName = optional($user->updater)->name ?? ($user->updated_by ?? '-');
@endphp

<div class="max-w-4xl mx-auto space-y-5">

  {{-- Header Profile --}}
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 md:p-6">
    <div class="flex flex-col md:flex-row md:items-center gap-4 md:gap-6">
      <div class="shrink-0">
        @if($photo)
          {{-- img + fallback inisial kalau error --}}
          <img src="{{ $photo }}"
               onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';"
               class="h-24 w-24 rounded-2xl object-cover ring-2 ring-[#05727d]/25"
               alt="avatar">

          <div style="display:none"
               class="h-24 w-24 rounded-2xl bg-[#e6f1f2] text-[#05727d]
                      grid place-items-center text-3xl font-bold">
            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
          </div>
        @else
          <div class="h-24 w-24 rounded-2xl bg-[#e6f1f2] text-[#05727d]
                      grid place-items-center text-3xl font-bold">
            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
          </div>
        @endif
      </div>

      <div class="flex-1">
        <div class="text-xl font-bold text-slate-900">{{ $user->name }}</div>
        <div class="text-sm text-slate-500">{{ $user->email }}</div>

        <div class="mt-2 flex flex-wrap gap-2">
          <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                       bg-[#05727d]/10 text-[#05727d] uppercase tracking-wide">
            {{ $user->role ?? 'operator' }}
          </span>
          <span class="px-2.5 py-1 rounded-full text-xs bg-slate-100 text-slate-600">
            Status: {{ $user->status ?? 'active' }}
          </span>
        </div>
      </div>

      <div class="md:self-start">
        <a href="{{ route('profile.edit') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                  bg-[#05727d] text-white text-sm font-semibold hover:brightness-110">
          Edit Profile
        </a>
      </div>
    </div>
  </div>

  {{-- Informasi Akun --}}
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 md:p-6">
    <div class="text-sm font-semibold text-slate-900 mb-4">Informasi Akun</div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div class="text-xs text-slate-500">Username</div>
        <div class="font-semibold">{{ $user->username ?? '-' }}</div>
      </div>

      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div class="text-xs text-slate-500">Phone</div>
        <div class="font-semibold">{{ $user->phone ?? '-' }}</div>
      </div>

      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div class="text-xs text-slate-500">Employee Code / NIK</div>
        <div class="font-semibold">{{ $user->employee_code ?? '-' }}</div>
      </div>

      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div class="text-xs text-slate-500">Company</div>
        <div class="font-semibold">{{ $user->company ?? '-' }}</div>
      </div>

      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div class="text-xs text-slate-500">Department</div>
        <div class="font-semibold">{{ $user->department ?? '-' }}</div>
      </div>

      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div class="text-xs text-slate-500">Position</div>
        <div class="font-semibold">{{ $user->position ?? '-' }}</div>
      </div>

      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div class="text-xs text-slate-500">Site</div>
        <div class="font-semibold">{{ $user->site ?? '-' }}</div>
      </div>

      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div class="text-xs text-slate-500">Join Date</div>
        <div class="font-semibold">{{ $fmtDate($user->join_date) }}</div>
      </div>
    </div>
  </div>

  {{-- Notes / Catatan --}}
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 md:p-6">
    <div class="text-sm font-semibold text-slate-900 mb-2">Catatan</div>
    @if(!empty($user->notes))
      <div class="text-sm text-slate-700 whitespace-pre-line">{{ $user->notes }}</div>
    @else
      <div class="text-sm text-slate-400 italic">Belum ada catatan.</div>
    @endif
  </div>

  {{-- Aktivitas & Verifikasi --}}
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 md:p-6">
    <div class="text-sm font-semibold text-slate-900 mb-4">Aktivitas & Verifikasi</div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div class="text-xs text-slate-500">Email Verified At</div>
        <div class="font-semibold">{{ $fmtDate($user->email_verified_at, true) }}</div>
      </div>

      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div class="text-xs text-slate-500">Last Login At</div>
        <div class="font-semibold">{{ $fmtDate($user->last_login_at, true) }}</div>
      </div>

      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 md:col-span-2">
        <div class="text-xs text-slate-500">Last Login IP</div>
        <div class="font-semibold">{{ $user->last_login_ip ?? '-' }}</div>
      </div>
    </div>
  </div>

  {{-- Audit Trail --}}
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 md:p-6">
    <div class="text-sm font-semibold text-slate-900 mb-4">Audit Trail</div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div class="text-xs text-slate-500">Created By</div>
        <div class="font-semibold">{{ $creatorName }}</div>
      </div>

      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div class="text-xs text-slate-500">Updated By</div>
        <div class="font-semibold">{{ $updaterName }}</div>
      </div>

      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div class="text-xs text-slate-500">Created At</div>
        <div class="font-semibold">{{ $fmtDate($user->created_at, true) }}</div>
      </div>

      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div class="text-xs text-slate-500">Updated At</div>
        <div class="font-semibold">{{ $fmtDate($user->updated_at, true) }}</div>
      </div>

      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 md:col-span-2">
        <div class="text-xs text-slate-500">Deleted At (Soft Delete)</div>
        <div class="font-semibold">{{ $fmtDate($user->deleted_at, true) }}</div>
      </div>
    </div>
  </div>

</div>
@endsection
