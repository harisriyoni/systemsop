@extends('layouts.app')
@section('title', 'Create Check Sheet Form')

@section('content')
@php
  $user = auth()->user();
@endphp

<div class="max-w-3xl mx-auto space-y-4">

  {{-- HEADER CARD --}}
  <div class="bg-white rounded-2xl border border-[#05727d]/20 shadow-sm overflow-hidden">
    <div class="bg-gradient-to-r from-[#05727d] to-[#0894a0] px-5 py-4 text-white">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-xl bg-white/15 grid place-items-center">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6M9 9h6M9 13h6M9 17h6M5 5h.01M5 9h.01M5 13h.01M5 17h.01"/>
            </svg>
          </div>
          <div>
            <h2 class="text-base font-semibold leading-tight">Create Check Sheet Form</h2>
            <p class="text-xs text-[#d5f3f4] mt-0.5">
              Buat form baru untuk operator isi harian.
            </p>
          </div>
        </div>

        @if(Route::has('check_sheets.index'))
          <a href="{{ route('check_sheets.index') }}"
             class="text-xs bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg transition">
            ← Kembali
          </a>
        @endif
      </div>
    </div>

    {{-- BODY --}}
    <div class="p-5 md:p-6">

      <form id="checkSheetCreateForm" method="POST" action="{{ route('check_sheets.store') }}"
            class="space-y-5">
        @csrf

        {{-- Error Global --}}
        @if ($errors->any())
          <div class="rounded-lg bg-[#05727d]/10 border border-[#05727d]/40 text-[#05727d] px-3 py-2 text-xs">
            <div class="font-semibold mb-1">Periksa kembali input:</div>
            <ul class="list-disc pl-4 space-y-0.5">
              @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        {{-- SECTION: Informasi Utama --}}
        <div class="bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl p-4">
          <div class="text-xs font-semibold text-[#05727d] mb-3 flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-[#05727d]"></span>
            Informasi Utama
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Title --}}
            <div class="md:col-span-2">
              <label class="block text-xs text-slate-600 mb-1">
                Title <span class="text-rose-500">*</span>
              </label>
              <input type="text" name="title" value="{{ old('title') }}" required
                     placeholder="Contoh: Check Sheet Harian OHT"
                     class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                            {{ $errors->has('title') ? 
                              'border-rose-300 focus:ring-rose-100 focus:border-rose-500' : 
                              'border-slate-200 focus:ring-[#b7e9ec] focus:border-[#05727d]' }}">
              @error('title')
                <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div>
              @enderror
            </div>

            {{-- Department --}}
            <div>
              <label class="block text-xs text-slate-600 mb-1">
                Department <span class="text-rose-500">*</span>
              </label>
              <input type="text" name="department" value="{{ old('department') }}" required
                     placeholder="QA / Logistik / Produksi"
                     class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                            {{ $errors->has('department') ? 
                              'border-rose-300 focus:ring-rose-100 focus:border-rose-500' : 
                              'border-slate-200 focus:ring-[#b7e9ec] focus:border-[#05727d]' }}">
              @error('department')
                <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div>
              @enderror
            </div>

            {{-- Product --}}
            <div>
              <label class="block text-xs text-slate-600 mb-1">Product (Opsional)</label>
              <input type="text" name="product" value="{{ old('product') }}"
                     placeholder="Nickel Matte / Packing ..."
                     class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                            focus:ring-[#b7e9ec] focus:border-[#05727d]">
            </div>

            {{-- Line --}}
            <div>
              <label class="block text-xs text-slate-600 mb-1">Line (Opsional)</label>
              <input type="text" name="line" value="{{ old('line') }}"
                     placeholder="Line A / Line B"
                     class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                            focus:ring-[#b7e9ec] focus:border-[#05727d]">
            </div>

            {{-- Status --}}
            <div class="md:col-span-2">
              <label class="block text-xs text-slate-600 mb-1">Status Awal (Opsional)</label>
              <select name="status"
                      class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none
                             focus:ring-[#b7e9ec] focus:border-[#05727d]">
                <option value="">Default sesuai sistem</option>
                <option value="draft" {{ old('status')=='draft' ? 'selected' : '' }}>Draf</option>
                <option value="active" {{ old('status')=='active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ old('status')=='inactive' ? 'selected' : '' }}>Nonaktif</option>
              </select>
              <div class="text-[11px] text-slate-400 mt-1">
                Jika controller belum mendukung, field ini akan diabaikan otomatis.
              </div>
            </div>

          </div>
        </div>

        {{-- Description --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">Description / Instruksi Singkat (Opsional)</label>
          <textarea name="description" rows="5"
                    placeholder="Instruksi singkat untuk operator saat mengisi form..."
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none
                           focus:ring-[#b7e9ec] focus:border-[#05727d]">{{ old('description') }}</textarea>
        </div>

      </form>
    </div>

    {{-- FOOTER ACTION --}}
    <div class="sticky bottom-0 bg-white/95 backdrop-blur border-t border-[#05727d]/20 px-5 py-3">
      <div class="flex items-center justify-between gap-2">

        @if(Route::has('check_sheets.index'))
          <a href="{{ route('check_sheets.index') }}"
             class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
            Batal
          </a>
        @else
          <div></div>
        @endif

        <div class="flex items-center gap-2">

          {{-- Simpan Draft --}}
          <button type="submit"
                  form="checkSheetCreateForm"
                  name="save_draft" value="1"
                  class="px-4 py-2 rounded-lg bg-white border border-[#05727d]/40 text-[#05727d] text-xs font-semibold hover:bg-[#05727d]/10">
            Simpan Draft
          </button>

          {{-- Simpan --}}
          <button type="submit"
                  form="checkSheetCreateForm"
                  class="px-5 py-2 rounded-lg bg-[#05727d] hover:bg-[#0894a0] text-white text-xs font-semibold shadow-sm">
            Simpan Form
          </button>

        </div>
      </div>
    </div>

  </div>
</div>
@endsection
