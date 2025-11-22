@extends('layouts.app')
@section('title', 'Edit SOP')

@section('content')
@php
  $user = auth()->user();
  $photos = is_array($sop->photos ?? null) ? $sop->photos : (json_decode($sop->photos, true) ?: []);
  $canManage = $user->isRole(['admin','produksi']);
@endphp

<div
  x-data="{
    newPhotos: [{ id: Date.now(), name:'', preview:null }],
    addNewPhoto(){ this.newPhotos.push({ id: Date.now(), name:'', preview:null }); },
    removeNewPhoto(i){
      if(this.newPhotos.length===1) return;
      if(this.newPhotos[i].preview) URL.revokeObjectURL(this.newPhotos[i].preview);
      this.newPhotos.splice(i,1);
    },
    setNewPhoto(i, e){
      const file = e.target.files?.[0];
      this.newPhotos[i].name = file?.name || '';
      if(this.newPhotos[i].preview) URL.revokeObjectURL(this.newPhotos[i].preview);
      this.newPhotos[i].preview = file ? URL.createObjectURL(file) : null;
    }
  }"
  class="space-y-4"
>

  {{-- ================= HEADER ================= --}}
  <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h2 class="text-base font-semibold text-slate-900">Edit SOP</h2>
        <p class="text-xs text-slate-500 mt-1">
          Kode: <span class="font-semibold text-blue-700">{{ $sop->code }}</span>
          • Versi: <span class="font-semibold">v{{ $sop->version ?? 1 }}</span>
        </p>
        <p class="text-[11px] text-slate-400 mt-1">
          Jika SOP sebelumnya <b>Approved</b>, saat disimpan akan jadi <b>Draft</b> dan versi naik otomatis.
        </p>
      </div>

      <div class="flex items-center gap-2">
        @if(Route::has('sop.show'))
          <a href="{{ route('sop.show', $sop) }}"
             class="inline-flex items-center px-4 py-2 rounded-xl
                    bg-white border border-slate-200 text-slate-700
                    hover:bg-slate-50 text-xs font-semibold transition">
            Kembali
          </a>
        @endif
      </div>
    </div>
  </div>

  {{-- ================= FORM ================= --}}
  <form id="sopEditForm"
        method="POST"
        action="{{ route('sop.update', $sop) }}"
        enctype="multipart/form-data"
        class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5 space-y-5"
  >
    @csrf
    @method('PUT')

    {{-- Error global --}}
    @if($errors->any())
      <div class="text-xs rounded-lg bg-blue-50 border border-blue-200 text-blue-800 px-3 py-2">
        <div class="font-semibold mb-1">Periksa kembali input:</div>
        <ul class="list-disc pl-4 space-y-0.5">
          @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- ===== Informasi Utama ===== --}}
    <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4">
      <div class="text-xs font-semibold text-blue-700 mb-3 flex items-center gap-2">
        <span class="h-2 w-2 rounded-full bg-blue-600"></span>
        Informasi Utama
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs text-slate-600 mb-1">Kode SOP <span class="text-rose-500">*</span></label>
          <input type="text" name="code" value="{{ old('code', $sop->code) }}"
                 class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                        {{ $errors->has('code') ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500' : 'border-slate-200 focus:ring-blue-100 focus:border-blue-500' }}"
                 required>
          @error('code') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-xs text-slate-600 mb-1">Judul SOP <span class="text-rose-500">*</span></label>
          <input type="text" name="title" value="{{ old('title', $sop->title) }}"
                 class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                        {{ $errors->has('title') ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500' : 'border-slate-200 focus:ring-blue-100 focus:border-blue-500' }}"
                 required>
          @error('title') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-xs text-slate-600 mb-1">Departemen <span class="text-rose-500">*</span></label>
          <input type="text" name="department" value="{{ old('department', $sop->department) }}"
                 class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                        {{ $errors->has('department') ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500' : 'border-slate-200 focus:ring-blue-100 focus:border-blue-500' }}"
                 required>
          @error('department') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-xs text-slate-600 mb-1">Produk (Opsional)</label>
          <input type="text" name="product" value="{{ old('product', $sop->product) }}"
                 class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                        focus:ring-blue-100 focus:border-blue-500">
        </div>
      </div>
    </div>

    {{-- ===== Detail Operasional ===== --}}
    <div class="bg-white border border-blue-100 rounded-xl p-4">
      <div class="text-xs font-semibold text-blue-700 mb-3 flex items-center gap-2">
        <span class="h-2 w-2 rounded-full bg-blue-600"></span>
        Detail Operasional
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs text-slate-600 mb-1">Lini Produksi (Opsional)</label>
          <input type="text" name="line" value="{{ old('line', $sop->line) }}"
                 class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                        focus:ring-blue-100 focus:border-blue-500">
        </div>

        <div>
          <label class="block text-xs text-slate-600 mb-1">Tanggal Berlaku Mulai</label>
          <input type="date" name="effective_from" value="{{ old('effective_from', optional($sop->effective_from)->toDateString()) }}"
                 class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                        focus:ring-blue-100 focus:border-blue-500">
        </div>

        <div class="md:col-span-2">
          <label class="block text-xs text-slate-600 mb-1">Tanggal Berlaku Sampai</label>
          <input type="date" name="effective_to" value="{{ old('effective_to', optional($sop->effective_to)->toDateString()) }}"
                 class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                        {{ $errors->has('effective_to') ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500' : 'border-slate-200 focus:ring-blue-100 focus:border-blue-500' }}">
          @error('effective_to') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>
      </div>
    </div>

    {{-- ===== FOTO LAMA (BISA DIHAPUS) ===== --}}
    <div class="bg-blue-50/40 border border-blue-100 rounded-xl p-4">
      <div class="flex items-center justify-between mb-3">
        <div class="text-xs font-semibold text-blue-700 flex items-center gap-2">
          <span class="h-2 w-2 rounded-full bg-blue-600"></span>
          Foto/Lampiran Lama
        </div>
        <div class="text-[11px] text-slate-500">
          Centang untuk menghapus foto lama.
        </div>
      </div>

      @if(count($photos))
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          @foreach($photos as $p)
            @php
              $path = $p['path'] ?? null;
              $desc = $p['desc'] ?? null;
              $url = $path ? Storage::disk('public')->url($path) : null;
            @endphp

            <label class="bg-white border border-blue-100 rounded-xl p-3 flex gap-3 cursor-pointer hover:bg-blue-50/60 transition">
              <input type="checkbox" name="remove_photos[]" value="{{ $path }}"
                     class="mt-1 h-4 w-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500">

              <div class="flex-1 min-w-0">
                <div class="h-28 w-full rounded-lg bg-slate-50 border border-slate-200 overflow-hidden grid place-items-center">
                  @if($url)
                    <img src="{{ $url }}" class="h-full w-full object-cover" alt="foto SOP">
                  @else
                    <div class="text-[11px] text-slate-400">Tidak ada preview</div>
                  @endif
                </div>

                <div class="mt-2 text-[11px] text-slate-600 truncate">
                  {{ $path }}
                </div>

                @if($desc)
                  <div class="text-[11px] text-slate-500 mt-1">
                    Deskripsi: <span class="font-medium">{{ $desc }}</span>
                  </div>
                @endif
              </div>
            </label>
          @endforeach
        </div>
      @else
        <div class="text-sm text-slate-400 py-6 text-center">
          Belum ada foto lama.
        </div>
      @endif
    </div>

    {{-- ===== FOTO BARU (BISA TAMBAH) ===== --}}
    <div class="bg-blue-50/40 border border-blue-100 rounded-xl p-4">
      <div class="flex items-center justify-between mb-3">
        <div class="text-xs font-semibold text-blue-700 flex items-center gap-2">
          <span class="h-2 w-2 rounded-full bg-blue-600"></span>
          Tambah Foto Baru
        </div>
        <button type="button"
                @click="addNewPhoto()"
                class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-semibold shadow-sm">
          + Tambah Foto
        </button>
      </div>

      <div class="flex gap-3 overflow-x-auto pb-2 flex-nowrap">
        <template x-for="(p, i) in newPhotos" :key="p.id">
          <div class="bg-white border border-blue-100 rounded-xl p-3 min-w-[280px] md:min-w-[320px] shrink-0">
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
                              rounded-lg border border-dashed border-blue-200 bg-white px-3 py-2 text-sm
                              hover:bg-blue-50 transition">
                  <div class="flex items-center gap-2 text-slate-600">
                    <svg class="w-4 h-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4-4a3 3 0 014 0l4 4M2 20h20M2 12l5-5a3 3 0 014 0l3 3m7-7v8"/>
                    </svg>
                    <span x-show="!p.name" class="text-[12px]">Pilih foto</span>
                    <span x-show="p.name" class="font-semibold text-slate-800 text-[12px]" x-text="p.name"></span>
                  </div>
                  <span class="text-[11px] text-blue-700 font-semibold">Upload</span>
                  <input type="file" name="photos[]" accept="image/*" class="hidden"
                         @change="setNewPhoto(i, $event)">
                </label>

                <div class="mt-3">
                  <label class="block text-[11px] text-slate-600 mb-1">Deskripsi Foto</label>
                  <input type="text" name="photo_desc[]"
                         class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                                focus:ring-blue-100 focus:border-blue-500"
                         placeholder="Cover / Step / Area kerja">
                </div>
              </div>
            </div>

            <div class="mt-3 flex justify-end">
              <button type="button"
                      @click="removeNewPhoto(i)"
                      :disabled="newPhotos.length===1"
                      class="text-[11px] px-2 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                Hapus
              </button>
            </div>
          </div>
        </template>
      </div>

      <div class="text-[11px] text-slate-500 mt-2">Geser ke kanan untuk melihat slot foto lainnya.</div>
    </div>

    {{-- ===== AKSES SOP ===== --}}
    <div class="bg-white border border-blue-100 rounded-xl p-4">
      <div class="text-xs font-semibold text-blue-700 mb-3 flex items-center gap-2">
        <span class="h-2 w-2 rounded-full bg-blue-600"></span>
        Akses SOP
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label class="flex items-center gap-2">
          <input id="is_public" type="checkbox" name="is_public" value="1"
                 class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                 {{ old('is_public', $sop->is_public) ? 'checked' : '' }}>
          <span class="text-xs text-slate-700">
            Jadikan SOP publik (bisa dibuka via link/QR tanpa login)
          </span>
        </label>

        <div>
          <label class="block text-xs text-slate-600 mb-1">PIN Akses (Opsional)</label>
          <input type="text" name="pin" value="{{ old('pin', $sop->pin) }}"
                 class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                        focus:ring-blue-100 focus:border-blue-500"
                 placeholder="Contoh: 1234">
          <div class="text-[11px] text-slate-400 mt-1">
            Jika publik + PIN diisi, SOP perlu PIN sebelum dibuka.
          </div>
        </div>
      </div>
    </div>

    {{-- ===== ISI SOP ===== --}}
    <div>
      <label class="block text-xs text-slate-600 mb-1">Isi / Deskripsi SOP (Opsional)</label>
      <textarea name="content" rows="7"
                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none
                       focus:ring-blue-100 focus:border-blue-500"
                placeholder="Tuliskan isi SOP atau ringkasan langkah-langkahnya...">{{ old('content', $sop->content) }}</textarea>
    </div>

    {{-- ===== ACTIONS ===== --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 pt-2">
      <div class="text-[11px] text-slate-400">
        Terakhir update: {{ optional($sop->updated_at)->format('d M Y H:i') }}
      </div>

      <div class="flex items-center gap-2 justify-end">
        @if(Route::has('sop.index'))
          <a href="{{ route('sop.index') }}"
             class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
            Batal
          </a>
        @endif

        <button
          type="submit"
          class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm">
          Simpan Perubahan
        </button>
      </div>
    </div>

  </form>
</div>
@endsection
