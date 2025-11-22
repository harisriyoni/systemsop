@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
@php
  $user = $user ?? auth()->user();
  $photo = null;

  if ($user) {
      if (!empty($user->avatar_path)) {
          $photo = \Illuminate\Support\Facades\Storage::disk('public')->url($user->avatar_path);
      } elseif (!empty($user->photo_url)) {
          $photo = $user->photo_url;
      }
  }

  $fmtDate = function($v, $withTime = false) {
      if (!$v) return '-';
      try {
          $c = \Carbon\Carbon::parse($v);
          return $withTime ? $c->format('d M Y H:i') : $c->format('d M Y');
      } catch (\Throwable $e) {
          return $v;
      }
  };
@endphp

<div class="max-w-4xl mx-auto space-y-5">

  {{-- Avatar --}}
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 md:p-6">
    <div class="flex items-center justify-between mb-4">
      <div class="font-semibold text-slate-900">Foto Profile</div>
      <a href="{{ route('profile.show') }}" class="text-sm text-[#05727d] font-semibold hover:underline">
        Kembali
      </a>
    </div>

    <div class="flex flex-col md:flex-row md:items-center gap-5">
      <div class="shrink-0">
        @if($photo)
          <img src="{{ $photo }}" class="h-24 w-24 rounded-2xl object-cover ring-2 ring-[#05727d]/25" alt="avatar">
        @else
          <div class="h-24 w-24 rounded-2xl bg-[#e6f1f2] text-[#05727d] grid place-items-center text-3xl font-bold">
            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
          </div>
        @endif
      </div>

      <div class="flex-1 space-y-2">
        <form action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data"
              class="flex flex-col md:flex-row gap-2 md:items-center">
          @csrf
          <input type="file" name="avatar" accept="image/*"
                 class="block w-full text-sm file:mr-3 file:px-3 file:py-2 file:rounded-lg file:border-0
                        file:bg-[#05727d] file:text-white file:font-semibold
                        rounded-xl border border-slate-200 bg-white px-3 py-2">
          <button class="px-4 py-2 rounded-xl bg-[#05727d] text-white text-sm font-semibold hover:brightness-110">
            Upload
          </button>
        </form>

        @if($user->avatar_path)
        <form action="{{ route('profile.avatar.delete') }}" method="POST">
          @csrf
          @method('DELETE')
          <button class="px-3 py-2 rounded-xl bg-rose-50 text-rose-700 text-sm font-semibold hover:bg-rose-100">
            Hapus Avatar
          </button>
        </form>
        @endif

        @error('avatar')
          <div class="text-sm text-rose-600">{{ $message }}</div>
        @enderror
      </div>
    </div>
  </div>

  {{-- Data Profile --}}
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 md:p-6">
    <div class="font-semibold text-slate-900 mb-4">Data Profile</div>

    <form action="{{ route('profile.update') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @csrf
      @method('PATCH')

      {{-- READ ONLY: employee_code --}}
      <div>
        <label class="text-sm font-medium text-slate-700">Employee Code / NIK</label>
        <input type="text" value="{{ $user->employee_code ?? '-' }}" disabled
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 text-slate-600">
      </div>

      {{-- READ ONLY: role --}}
      <div>
        <label class="text-sm font-medium text-slate-700">Role</label>
        <input type="text" value="{{ $user->role ?? '-' }}" disabled
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 text-slate-600">
      </div>

      {{-- READ ONLY: status --}}
      <div>
        <label class="text-sm font-medium text-slate-700">Status</label>
        <input type="text" value="{{ $user->status ?? '-' }}" disabled
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 text-slate-600">
      </div>

      {{-- name --}}
      <div>
        <label class="text-sm font-medium text-slate-700">Nama</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}"
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2
                      focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d]">
        @error('name') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      {{-- username --}}
      <div>
        <label class="text-sm font-medium text-slate-700">Username</label>
        <input type="text" name="username" value="{{ old('username', $user->username) }}"
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2
                      focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d]">
        @error('username') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      {{-- email --}}
      <div>
        <label class="text-sm font-medium text-slate-700">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}"
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2
                      focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d]">
        @error('email') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      {{-- phone --}}
      <div>
        <label class="text-sm font-medium text-slate-700">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2
                      focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d]">
        @error('phone') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      {{-- company --}}
      <div>
        <label class="text-sm font-medium text-slate-700">Company</label>
        <input type="text" name="company" value="{{ old('company', $user->company) }}"
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2
                      focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d]">
        @error('company') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      {{-- department --}}
      <div>
        <label class="text-sm font-medium text-slate-700">Department</label>
        <input type="text" name="department" value="{{ old('department', $user->department) }}"
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2
                      focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d]">
        @error('department') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      {{-- position --}}
      <div>
        <label class="text-sm font-medium text-slate-700">Position</label>
        <input type="text" name="position" value="{{ old('position', $user->position) }}"
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2
                      focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d]">
        @error('position') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      {{-- site --}}
      <div>
        <label class="text-sm font-medium text-slate-700">Site</label>
        <input type="text" name="site" value="{{ old('site', $user->site) }}"
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2
                      focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d]">
        @error('site') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      {{-- join_date --}}
      <div>
        <label class="text-sm font-medium text-slate-700">Join Date</label>
        <input type="date" name="join_date"
               value="{{ old('join_date', optional($user->join_date)->format('Y-m-d')) }}"
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2
                      focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d]">
        @error('join_date') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      {{-- notes --}}
      <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700">Catatan / Notes</label>
        <textarea name="notes" rows="4"
                  class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2
                         focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d]"
                  placeholder="Tambahkan catatan...">{{ old('notes', $user->notes) }}</textarea>
        @error('notes') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      <div class="md:col-span-2 flex justify-end pt-2">
        <button class="px-5 py-2.5 rounded-xl bg-[#05727d] text-white text-sm font-semibold hover:brightness-110">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>

  {{-- Info login terakhir (read-only) --}}
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 md:p-6">
    <div class="font-semibold text-slate-900 mb-3">Aktivitas Login</div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div class="text-xs text-slate-500">Last Login At</div>
        <div class="font-semibold">{{ $fmtDate($user->last_login_at, true) }}</div>
      </div>
      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div class="text-xs text-slate-500">Last Login IP</div>
        <div class="font-semibold">{{ $user->last_login_ip ?? '-' }}</div>
      </div>
    </div>
  </div>

  {{-- Form ganti password --}}
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 md:p-6">
    <div class="font-semibold text-slate-900 mb-4">Ganti Password</div>

    <form action="{{ route('profile.password.update') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @csrf
      @method('PATCH')

      <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700">Password Lama</label>
        <input type="password" name="current_password"
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2
                      focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d]">
        @error('current_password') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="text-sm font-medium text-slate-700">Password Baru</label>
        <input type="password" name="password"
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2
                      focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d]">
        @error('password') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="text-sm font-medium text-slate-700">Konfirmasi Password Baru</label>
        <input type="password" name="password_confirmation"
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2
                      focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d]">
      </div>

      <div class="md:col-span-2 flex justify-end pt-2">
        <button class="px-5 py-2.5 rounded-xl bg-[#05727d] text-white text-sm font-semibold hover:brightness-110">
          Update Password
        </button>
      </div>
    </form>
  </div>

</div>
@endsection
