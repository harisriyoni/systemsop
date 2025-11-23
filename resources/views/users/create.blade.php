@extends('layouts.app')
@section('title', 'Tambah User')

@section('content')
@php
  $roles = $roles ?? ['admin','produksi','qa','logistik','operator'];
@endphp

<div class="max-w-3xl mx-auto space-y-4">

  <div class="bg-white border border-[#05727d]/20 rounded-2xl shadow-sm overflow-hidden">
    <div class="bg-gradient-to-r from-[#05727d] to-[#04656f] px-6 py-5 text-white">
      <div class="text-2xl font-semibold">Tambah User</div>
      <div class="text-sm text-white/80 mt-1">
        Buat akun baru untuk akses SOP & Check Sheet.
      </div>
    </div>

    {{-- routes kamu: users.store = POST /users --}}
    <form method="POST" action="{{ route('users.store') }}" class="p-6 space-y-4 text-sm">
      @csrf

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
        {{-- Nama --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">Nama</label>
          <input type="text" name="name" value="{{ old('name') }}"
                 class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs
                        focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d] outline-none"
                 placeholder="Nama lengkap">
        </div>

        {{-- Email --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">Email</label>
          <input type="email" name="email" value="{{ old('email') }}"
                 class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs
                        focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d] outline-none"
                 placeholder="email@perusahaan.com">
        </div>

        {{-- Role --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">Role</label>
          <select name="role"
                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs
                         focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d] outline-none">
            @foreach($roles as $r)
              <option value="{{ $r }}" {{ old('role')==$r?'selected':'' }}>
                {{ strtoupper($r) }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Status --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">Status</label>
          <select name="status"
                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs
                         focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d] outline-none">
            <option value="active"   {{ old('status','active')=='active'?'selected':'' }}>Aktif</option>
            <option value="inactive" {{ old('status')=='inactive'?'selected':'' }}>Nonaktif</option>
            <option value="suspended" {{ old('status')=='suspended'?'selected':'' }}>Suspended</option>
          </select>
        </div>

        {{-- Password --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">Password</label>
          <input type="password" name="password"
                 class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs
                        focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d] outline-none"
                 placeholder="Minimal 6 karakter">
        </div>

        {{-- Konfirmasi Password --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">Konfirmasi Password</label>
          <input type="password" name="password_confirmation"
                 class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs
                        focus:ring-2 focus:ring-[#05727d]/30 focus:border-[#05727d] outline-none">
        </div>
      </div>

      <div class="flex items-center justify-between pt-2">
        <a href="{{ route('users.index') }}"
           class="text-xs text-slate-500 hover:text-[#05727d] hover:underline">
          ← Kembali
        </a>

        <div class="flex items-center gap-2">
          <button
            class="inline-flex items-center px-4 py-2 rounded-xl bg-[#05727d] hover:brightness-110 text-white text-xs font-semibold">
            Simpan User
          </button>
        </div>
      </div>

    </form>
  </div>

</div>
@endsection
