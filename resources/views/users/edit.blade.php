@extends('layouts.app')
@section('title', 'Edit User')

@section('content')
@php
  $roles = $roles ?? ['admin','produksi','qa','logistik','operator'];
@endphp

<div class="max-w-3xl mx-auto space-y-4">

  <div class="bg-white border border-blue-100 rounded-2xl shadow-sm overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-5 text-white">
      <div class="text-2xl font-semibold">Edit User</div>
      <div class="text-sm text-blue-50/90 mt-1">
        Update akses & data user.
      </div>
    </div>

    {{-- routes kamu: users.update = PATCH /users/{user} --}}
    <form method="POST" action="{{ route('users.update', $user) }}" class="p-6 space-y-4 text-sm">
      @csrf
      @method('PATCH')

      @if ($errors->any())
        <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-xs">
          <div class="font-semibold mb-1">Terjadi error:</div>
          <ul class="list-disc pl-4 space-y-0.5">
            @foreach($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs text-slate-500 mb-1">Nama</label>
          <input type="text" name="name"
                 value="{{ old('name', $user->name) }}"
                 class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs
                        focus:ring-2 focus:ring-blue-200 focus:border-blue-400 outline-none">
        </div>

        <div>
          <label class="block text-xs text-slate-500 mb-1">Email</label>
          <input type="email" name="email"
                 value="{{ old('email', $user->email) }}"
                 class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs
                        focus:ring-2 focus:ring-blue-200 focus:border-blue-400 outline-none">
        </div>

        <div>
          <label class="block text-xs text-slate-500 mb-1">Role</label>
          <select name="role"
                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs
                         focus:ring-2 focus:ring-blue-200 focus:border-blue-400 outline-none">
            @foreach($roles as $r)
              <option value="{{ $r }}" {{ old('role', $user->role)==$r?'selected':'' }}>
                {{ strtoupper($r) }}
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-xs text-slate-500 mb-1">Password Baru (opsional)</label>
          <input type="password" name="password"
                 class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs
                        focus:ring-2 focus:ring-blue-200 focus:border-blue-400 outline-none"
                 placeholder="Kosongkan jika tidak ganti">
        </div>

        <div>
          <label class="block text-xs text-slate-500 mb-1">Konfirmasi Password Baru</label>
          <input type="password" name="password_confirmation"
                 class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs
                        focus:ring-2 focus:ring-blue-200 focus:border-blue-400 outline-none">
        </div>

        {{-- optional status aktif kalau kolom ada --}}
        @php
          $isActive = old('is_active', $user->is_active ?? $user->active ?? null);
        @endphp
        @if(!is_null($isActive))
        <div class="md:col-span-2">
          <label class="inline-flex items-center gap-2 text-xs text-slate-600">
            <input type="checkbox" name="is_active" value="1" {{ $isActive ? 'checked' : '' }}
                   class="rounded border-slate-300 text-blue-600 focus:ring-blue-200">
            User Aktif
          </label>
          <div class="text-[11px] text-slate-400 mt-1">
            Kalau dimatikan, user tidak bisa login.
          </div>
        </div>
        @endif
      </div>

      <div class="flex items-center justify-between pt-2">
        <a href="{{ route('users.index') }}"
           class="text-xs text-slate-500 hover:underline">
          ← Kembali
        </a>

        <div class="flex items-center gap-2">
          <button
            class="inline-flex items-center px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold">
            Update User
          </button>
        </div>
      </div>

    </form>
  </div>

</div>
@endsection
