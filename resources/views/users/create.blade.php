@extends('layouts.app')
@section('title', 'Tambah User')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-2xl border border-blue-100 shadow-sm p-6">

  {{-- HEADER --}}
  <div class="flex items-start justify-between gap-3 mb-5">
    <div>
      <h2 class="text-base font-semibold text-slate-900">Tambah User Baru</h2>
      <p class="text-xs text-slate-500">
        Buat akun untuk Admin / Produksi / QA / Logistik / Operator.
      </p>
    </div>
    <a href="{{ route('users.index') }}"
       class="text-xs font-semibold text-blue-700 hover:underline">
      ← Kembali
    </a>
  </div>

  {{-- ERROR GLOBAL --}}
  @if($errors->any())
    <div class="mb-4 text-xs rounded-lg bg-rose-50 border border-rose-200 text-rose-800 px-3 py-2">
      <div class="font-semibold mb-1">Periksa kembali input:</div>
      <ul class="list-disc pl-4 space-y-0.5">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
    @csrf

    {{-- INFO UTAMA --}}
    <div class="bg-blue-50/60 border border-blue-100 rounded-xl p-4">
      <div class="text-xs font-semibold text-blue-700 mb-3 flex items-center gap-2">
        <span class="h-2 w-2 rounded-full bg-blue-600"></span>
        Informasi Akun
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- NAMA --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">Nama <span class="text-rose-500">*</span></label>
          <input type="text" name="name" value="{{ old('name') }}"
                 placeholder="Nama lengkap"
                 class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                        {{ $errors->has('name') ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500' : 'border-slate-200 focus:ring-blue-100 focus:border-blue-500' }}"
                 required>
          @error('name')
            <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div>
          @enderror
        </div>

        {{-- EMAIL --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">Email <span class="text-rose-500">*</span></label>
          <input type="email" name="email" value="{{ old('email') }}"
                 placeholder="email@company.com"
                 class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                        {{ $errors->has('email') ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500' : 'border-slate-200 focus:ring-blue-100 focus:border-blue-500' }}"
                 required>
          @error('email')
            <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div>
          @enderror
        </div>

        {{-- ROLE --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">Role <span class="text-rose-500">*</span></label>
          <select name="role"
                  class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none
                         focus:ring-blue-100 focus:border-blue-500"
                  required>
            <option value="">Pilih Role</option>
            @foreach($roles as $r)
              <option value="{{ $r }}" {{ old('role')==$r ? 'selected':'' }}>
                {{ strtoupper($r) }}
              </option>
            @endforeach
          </select>
          @error('role')
            <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div>
          @enderror
        </div>

        {{-- STATUS AKTIF --}}
        <div class="flex items-center gap-2 pt-6">
          <input id="is_active" type="checkbox" name="is_active" value="1"
                 class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                 {{ old('is_active', 1) ? 'checked' : '' }}>
          <label for="is_active" class="text-xs text-slate-700">
            Aktifkan akun
          </label>
        </div>

      </div>
    </div>

    {{-- KEAMANAN --}}
    <div class="bg-white border border-blue-100 rounded-xl p-4">
      <div class="text-xs font-semibold text-blue-700 mb-3 flex items-center gap-2">
        <span class="h-2 w-2 rounded-full bg-blue-600"></span>
        Keamanan
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- PASSWORD --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">Password <span class="text-rose-500">*</span></label>
          <input type="password" name="password"
                 placeholder="Minimal 6 karakter"
                 class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                        {{ $errors->has('password') ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500' : 'border-slate-200 focus:ring-blue-100 focus:border-blue-500' }}"
                 required>
          @error('password')
            <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div>
          @enderror
        </div>

        {{-- PASSWORD CONFIRM --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">Konfirmasi Password <span class="text-rose-500">*</span></label>
          <input type="password" name="password_confirmation"
                 placeholder="Ulangi password"
                 class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                        focus:ring-blue-100 focus:border-blue-500"
                 required>
        </div>

      </div>

      <div class="mt-2 text-[11px] text-slate-500">
        Password akan dipakai user untuk login ke SOP+CheckFlow.
      </div>
    </div>

    {{-- ACTIONS --}}
    <div class="flex items-center justify-end gap-2 pt-1">
      <a href="{{ route('users.index') }}"
         class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
        Batal
      </a>
      <button type="submit"
              class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm">
        Simpan User
      </button>
    </div>

  </form>
</div>
@endsection
