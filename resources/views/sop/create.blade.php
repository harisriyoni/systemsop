@extends('layouts.app')
@section('title', 'Create SOP')

@section('content')

@php $user = auth()->user(); @endphp

<div
  x-data="{
    photos: [{ id: Date.now(), name: '', preview: null }],
    addPhoto() { this.photos.push({ id: Date.now(), name: '', preview: null }); },
    removePhoto(i) {
      if (this.photos.length === 1) return;
      if (this.photos[i].preview) URL.revokeObjectURL(this.photos[i].preview);
      this.photos.splice(i, 1);
    },
    setPhoto(i, e) {
      const file = e.target.files?.[0];
      this.photos[i].name = file?.name || '';
      if (this.photos[i].preview) URL.revokeObjectURL(this.photos[i].preview);
      this.photos[i].preview = file ? URL.createObjectURL(file) : null;
    }
  }"
  class="bg-white rounded-2xl border border-[#05727d]/20 shadow-sm overflow-hidden"
>

  {{-- HEADER --}}
  <div class="bg-gradient-to-r from-[#05727d] to-[#05727d] text-white px-5 md:px-6 py-4">
    <div class="flex items-start justify-between gap-3">
      <div>
        <h2 class="text-base font-semibold leading-tight">Tambah SOP Baru</h2>
        <p class="text-xs text-white/80 mt-1">
          Setelah disimpan, SOP otomatis masuk status <b>Menunggu Persetujuan</b>.
        </p>
      </div>

      <a href="{{ route('sop.index') }}"
         class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold">
        Kembali
      </a>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-5 md:p-6 space-y-5">

    {{-- ERROR GLOBAL --}}
    @if ($errors->any())
      <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-xs">
        <div class="font-semibold mb-1">Periksa kembali input:</div>
        <ul class="list-disc pl-4 space-y-0.5">
          @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form id="sopForm" method="POST" action="{{ route('sop.store') }}"
          enctype="multipart/form-data" class="space-y-5">
      @csrf

      {{-- =========================
          SECTION: INFORMASI UTAMA
      ========================== --}}
      <div class="bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl p-4">
        <div class="text-xs font-semibold text-[#05727d] mb-3 flex items-center gap-2">
          <span class="h-2 w-2 rounded-full bg-[#05727d]"></span>
          Informasi Utama
        </div>

        <div class="grid md:grid-cols-2 gap-4 text-sm">

          <div>
            <label class="block text-xs text-slate-600 mb-1">Kode SOP <span class="text-rose-500">*</span></label>
            <input type="text" name="code" value="{{ old('code') }}"
                   class="w-full rounded-lg border px-3 py-2 outline-none
                          {{ $errors->has('code')
                              ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500'
                              : 'border-slate-200 focus:ring-[#05727d]/15 focus:border-[#05727d]' }}"
                   placeholder="Contoh: SOP-PRD-001" required>
            @error('code') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
          </div>

          <div>
            <label class="block text-xs text-slate-600 mb-1">Judul SOP <span class="text-rose-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}"
                   class="w-full rounded-lg border px-3 py-2 outline-none
                          {{ $errors->has('title')
                              ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500'
                              : 'border-slate-200 focus:ring-[#05727d]/15 focus:border-[#05727d]' }}"
                   placeholder="Contoh: Prosedur Operasi Alat..." required>
            @error('title') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
          </div>

          <div>
            <label class="block text-xs text-slate-600 mb-1">Departemen <span class="text-rose-500">*</span></label>
            <input type="text" name="department" value="{{ old('department') }}"
                   class="w-full rounded-lg border px-3 py-2 outline-none
                          {{ $errors->has('department')
                              ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500'
                              : 'border-slate-200 focus:ring-[#05727d]/15 focus:border-[#05727d]' }}"
                   placeholder="Produksi / QA / Logistik" required>
            @error('department') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
          </div>

          <div>
            <label class="block text-xs text-slate-600 mb-1">Produk (Opsional)</label>
            <input type="text" name="product" value="{{ old('product') }}"
                   class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none
                          focus:ring-[#05727d]/15 focus:border-[#05727d]"
                   placeholder="Nickel Matte / Packing...">
            @error('product') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
          </div>

        </div>
      </div>


      {{-- =========================
          SECTION: DETAIL OPERASIONAL
      ========================== --}}
      <div class="bg-white border border-[#05727d]/20 rounded-xl p-4">
        <div class="text-xs font-semibold text-[#05727d] mb-3 flex items-center gap-2">
          <span class="h-2 w-2 rounded-full bg-[#05727d]"></span>
          Detail Operasional
        </div>

        <div class="grid md:grid-cols-2 gap-4 text-sm">
          <div>
            <label class="block text-xs text-slate-600 mb-1">Lini Produksi (Opsional)</label>
            <input type="text" name="line" value="{{ old('line') }}"
                   class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none
                          focus:ring-[#05727d]/15 focus:border-[#05727d]"
                   placeholder="Line A / Line B">
            @error('line') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
          </div>

          <div>
            <label class="block text-xs text-slate-600 mb-1">Tanggal Berlaku Mulai (Opsional)</label>
            <input type="date" name="effective_from" value="{{ old('effective_from') }}"
                   class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none
                          focus:ring-[#05727d]/15 focus:border-[#05727d]">
            @error('effective_from') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
          </div>

          <div class="md:col-span-2">
            <label class="block text-xs text-slate-600 mb-1">Tanggal Berlaku Sampai (Opsional)</label>
            <input type="date" name="effective_to" value="{{ old('effective_to') }}"
                   class="w-full rounded-lg border px-3 py-2 outline-none
                          {{ $errors->has('effective_to')
                              ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500'
                              : 'border-slate-200 focus:ring-[#05727d]/15 focus:border-[#05727d]' }}">
            @error('effective_to') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>


      {{-- =========================
          SECTION: FOTO / LAMPIRAN
      ========================== --}}
      <div class="bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
          <div class="text-xs font-semibold text-[#05727d] flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-[#05727d]"></span>
            Foto SOP / Lampiran (Bisa Banyak)
          </div>
          <button type="button"
                  @click="addPhoto()"
                  class="px-3 py-1.5 rounded-lg bg-[#05727d] hover:bg-[#05727d]/90 text-white text-[11px] font-semibold shadow-sm">
            + Tambah Foto
          </button>
        </div>

        <div class="space-y-3">
          <template x-for="(p, i) in photos" :key="p.id">
            <div class="bg-white border border-[#05727d]/20 rounded-xl p-3">
              <div class="flex items-start gap-3">
                <div class="h-16 w-16 rounded-lg bg-slate-50 border border-slate-200 overflow-hidden grid place-items-center shrink-0">
                  <template x-if="p.preview">
                    <img :src="p.preview" class="h-full w-full object-cover" alt="preview">
                  </template>
                  <template x-if="!p.preview">
                    <svg class="w-6 h-6 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4-4a3 3 0 014 0l4 4M2 20h20M2 12l5-5a3 3 0 014 0l3 3m7-7v8"/>
                    </svg>
                  </template>
                </div>

                <div class="flex-1">
                  <label class="block text-[11px] text-slate-600 mb-1">File Foto</label>
                  <label class="flex items-center justify-between gap-3 w-full cursor-pointer
                                rounded-lg border border-dashed border-[#05727d]/30 bg-white px-3 py-2 text-sm
                                hover:bg-[#05727d]/5 transition">
                    <div class="flex items-center gap-2 text-slate-600">
                      <svg class="w-4 h-4 text-[#05727d]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4-4a3 3 0 014 0l4 4M2 20h20M2 12l5-5a3 3 0 014 0l3 3m7-7v8"/>
                      </svg>
                      <span x-show="!p.name" class="text-[12px]">Pilih foto</span>
                      <span x-show="p.name" class="font-semibold text-slate-800 text-[12px]" x-text="p.name"></span>
                    </div>
                    <span class="text-[11px] text-[#05727d] font-semibold">Upload</span>
                    <input type="file" name="photos[]" accept="image/*" class="hidden"
                           @change="setPhoto(i, $event)">
                  </label>

                  <div class="mt-3">
                    <label class="block text-[11px] text-slate-600 mb-1">Deskripsi Foto</label>
                    <input type="text" name="photo_desc[]"
                           class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                                  focus:ring-[#05727d]/15 focus:border-[#05727d]"
                           placeholder="Cover / Step / Area kerja">
                  </div>
                </div>
              </div>

              <div class="mt-3 flex justify-end">
                <button type="button"
                        @click="removePhoto(i)"
                        :disabled="photos.length===1"
                        class="text-[11px] px-2 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                  Hapus
                </button>
              </div>
            </div>
          </template>
        </div>

        @error('photos') <div class="text-[11px] text-rose-600 mt-2">{{ $message }}</div> @enderror
        @error('photos.*') <div class="text-[11px] text-rose-600 mt-2">{{ $message }}</div> @enderror
      </div>


      {{-- =========================
          SECTION: AKSES SOP
      ========================== --}}
      <div class="bg-white border border-[#05727d]/20 rounded-xl p-4">
        <div class="text-xs font-semibold text-[#05727d] mb-3 flex items-center gap-2">
          <span class="h-2 w-2 rounded-full bg-[#05727d]"></span>
          Akses SOP
        </div>

        <div class="grid md:grid-cols-2 gap-4 text-sm">
          <label class="flex items-center gap-2">
            <input id="is_public" type="checkbox" name="is_public" value="1"
                   class="h-4 w-4 rounded border-slate-300 text-[#05727d] focus:ring-[#05727d]"
                   {{ old('is_public') ? 'checked' : '' }}>
            <span class="text-xs text-slate-700">
              Jadikan SOP publik (QR tanpa login)
            </span>
          </label>

          <div>
            <label class="block text-xs text-slate-600 mb-1">PIN Akses (Opsional)</label>
            <input type="text" name="pin" value="{{ old('pin') }}"
                   class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none
                          focus:ring-[#05727d]/15 focus:border-[#05727d]"
                   placeholder="Contoh: 1234">
            <div class="text-[11px] text-slate-400 mt-1">
              Jika publik + PIN diisi, SOP perlu PIN sebelum dibuka.
            </div>
            @error('pin') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>


      {{-- =========================
          SECTION: ISI SOP
      ========================== --}}
      <div>
        <label class="block text-xs text-slate-600 mb-1">Isi / Deskripsi SOP (Opsional)</label>
        <textarea name="content" rows="7"
                  class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none
                         focus:ring-[#05727d]/15 focus:border-[#05727d]"
                  placeholder="Tuliskan isi SOP atau ringkasan langkah-langkahnya...">{{ old('content') }}</textarea>
        @error('content') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

    </form>
  </div>

  {{-- FOOTER ACTION --}}
  <div class="sticky bottom-0 bg-white/95 backdrop-blur border-t border-[#05727d]/20 px-5 md:px-6 py-3">
    <div class="flex items-center justify-end gap-2">
      <a href="{{ route('sop.index') }}"
         class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
        Batal
      </a>

      <button type="submit"
              form="sopForm"
              class="px-5 py-2 rounded-lg bg-[#05727d] hover:bg-[#05727d]/90 text-white text-xs font-semibold shadow-sm">
        Simpan SOP
      </button>
    </div>
  </div>

</div>
@endsection
