@extends('layouts.app')
@section('title', 'Daftar SOP')

@section('content')

<div 
  x-data="{
    openCreate: {{ $errors->any() ? 'true' : 'false' }},
    photos: [{ id: 1, name: '' }],
    addPhoto() { this.photos.push({ id: Date.now(), name: '' }); },
    removePhoto(i) {
      if (this.photos.length === 1) return;
      this.photos.splice(i, 1);
    },
    setPhotoName(i, e) {
      this.photos[i].name = e.target.files?.[0]?.name || '';
    }
  }"
  class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5"
>

  {{-- HEADER --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
    <div>
      <h2 class="text-base font-semibold text-slate-900">Daftar SOP</h2>
      <p class="text-xs text-slate-500">
        Menampilkan {{ $sops->count() }} dari {{ $sops->total() }} SOP
      </p>
    </div>

    {{-- TOMBOL TAMBAH SOP (BUKA MODAL) --}}
    @if (auth()->user()->isRole(['admin','produksi']))
      <button
        type="button"
        @click="openCreate = true"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition"
      >
        <span class="text-lg leading-none">+</span>
        Tambah SOP
      </button>
    @endif
  </div>

  {{-- FILTER / SEARCH --}}
  <form method="GET" action="{{ route('sop.index') }}"
        class="bg-blue-50/60 border border-blue-100 rounded-xl p-3 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">

      <div>
        <label class="block mb-1 text-slate-600">Kata Kunci</label>
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Cari kode / judul SOP..."
               class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                      focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none">
      </div>

      <div>
        <label class="block mb-1 text-slate-600">Departemen</label>
        <input type="text" name="department" value="{{ request('department') }}"
               placeholder="Produksi / QA / Logistik..."
               class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                      focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none">
      </div>

      <div>
        <label class="block mb-1 text-slate-600">Status</label>
        <select name="status"
                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                       focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none">
          <option value="">Semua Status</option>
          <option value="draft" {{ request('status')=='draft' ? 'selected' : '' }}>Draf</option>
          <option value="waiting_approval" {{ request('status')=='waiting_approval' ? 'selected' : '' }}>Menunggu Persetujuan</option>
          <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>Disetujui</option>
          <option value="expired" {{ request('status')=='expired' ? 'selected' : '' }}>Kedaluwarsa</option>
        </select>
      </div>

      <div class="flex items-end gap-2">
        <button
          class="inline-flex items-center justify-center w-full md:w-auto px-4 py-2 rounded-lg
                 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition">
          Terapkan
        </button>
        <a href="{{ route('sop.index') }}"
           class="inline-flex items-center justify-center w-full md:w-auto px-4 py-2 rounded-lg
                  bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
          Reset
        </a>
      </div>

    </div>
  </form>

  {{-- TABLE --}}
  <div class="overflow-x-auto rounded-xl border border-blue-100">
    <table class="min-w-full text-xs bg-white">
      <thead class="bg-blue-50 text-blue-700 text-[11px] uppercase tracking-wider">
        <tr>
          <th class="px-4 py-3 text-left whitespace-nowrap">Kode</th>
          <th class="px-4 py-3 text-left">Judul SOP</th>
          <th class="px-4 py-3 text-left whitespace-nowrap">Departemen</th>
          <th class="px-4 py-3 text-left whitespace-nowrap">Status</th>
          <th class="px-4 py-3 text-left whitespace-nowrap">Aksi</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-blue-50">
        @forelse ($sops as $sop)
          @php
            $statusMap = [
              'draft' => ['label' => 'Draf', 'class' => 'bg-slate-50 text-slate-700 border-slate-200'],
              'waiting_approval' => ['label' => 'Menunggu Persetujuan', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
              'approved' => ['label' => 'Disetujui', 'class' => 'bg-blue-600 text-white border-blue-600'],
              'expired' => ['label' => 'Kedaluwarsa', 'class' => 'bg-slate-100 text-slate-500 border-slate-200'],
            ];
            $st = $statusMap[$sop->status] ?? ['label'=>$sop->status, 'class'=>'bg-slate-50 text-slate-700 border-slate-200'];

            $photoCount = is_array($sop->photos ?? null) ? count($sop->photos) : 0;
          @endphp

          <tr class="hover:bg-blue-50/40 transition">
            <td class="px-4 py-3 font-semibold text-slate-900 whitespace-nowrap">
              {{ $sop->code }}
            </td>

            <td class="px-4 py-3 text-slate-800">
              <div class="font-medium">{{ $sop->title }}</div>
              <div class="text-[11px] text-slate-400">
                Dibuat: {{ optional($sop->created_at)->format('d M Y') }}
                @if($sop->product || $sop->line)
                  • {{ $sop->product ?? '-' }} {{ $sop->line ? ' / '.$sop->line : '' }}
                @endif
              </div>

              {{-- info kecil akses & foto --}}
              <div class="mt-1 flex items-center gap-1.5 text-[11px]">
                <span class="px-2 py-0.5 rounded-full border
                  {{ $sop->is_public ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                  {{ $sop->is_public ? 'Publik' : 'Privat' }}
                </span>
                <span class="px-2 py-0.5 rounded-full border bg-white text-slate-600 border-slate-200">
                  {{ $photoCount }} Foto
                </span>
              </div>
            </td>

            <td class="px-4 py-3 text-slate-700 whitespace-nowrap">
              {{ $sop->department }}
            </td>

            <td class="px-4 py-3 whitespace-nowrap">
              <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-semibold {{ $st['class'] }}">
                {{ $st['label'] }}
              </span>
            </td>

            <td class="px-4 py-3 whitespace-nowrap">
              <div class="flex items-center gap-2">
                <a href="{{ route('sop.show', $sop) }}"
                   class="inline-flex items-center px-3 py-1.5 rounded-lg
                          bg-white border border-blue-200 text-blue-700
                          hover:bg-blue-50 font-semibold text-[11px] transition">
                  Lihat
                </a>

                @if(auth()->user()->isRole(['admin','produksi','qa','logistik']) && $sop->status === 'waiting_approval')
                  <form method="POST" action="{{ route('sop.approve', $sop) }}" class="inline">
                    @csrf
                    <button
                      class="inline-flex items-center px-3 py-1.5 rounded-lg
                             bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[11px] transition">
                      Setujui
                    </button>
                  </form>
                @endif
              </div>
            </td>
          </tr>

        @empty
          <tr>
            <td colspan="5" class="px-4 py-10 text-center">
              <div class="text-sm font-semibold text-slate-700 mb-1">Belum ada SOP</div>
              <div class="text-xs text-slate-400">Silakan buat SOP baru atau ubah filter pencarian.</div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINATION --}}
  <div class="mt-4">
    {{ $sops->appends(request()->query())->links() }}
  </div>



{{-- ================= MODAL TAMBAH SOP (RESPONSIVE, TIDAK KEPANJANGAN) ================= --}}
<div
  x-show="openCreate"
  x-transition.opacity
  class="fixed inset-0 z-50 flex items-center justify-center px-3 md:px-4"
  style="display:none;"
>
  {{-- overlay --}}
  <div class="absolute inset-0 bg-black/40" @click="openCreate=false"></div>

  {{-- modal --}}
  <div
    class="relative w-full max-w-3xl bg-white rounded-2xl border border-blue-100 shadow-2xl overflow-hidden
           flex flex-col max-h-[90vh] md:max-h-[85vh]"
  >

    {{-- HEADER BIRU (STICKY) --}}
    <div class="sticky top-0 z-10 bg-gradient-to-r from-blue-600 to-blue-500 px-5 md:px-6 py-4 text-white">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-xl bg-white/15 grid place-items-center">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
            </svg>
          </div>
          <div>
            <h3 class="text-base font-semibold leading-tight">Tambah SOP Baru</h3>
            <p class="text-xs text-blue-100">
              SOP akan masuk antrean persetujuan Produksi, QA, dan Logistik.
            </p>
          </div>
        </div>

        <button @click="openCreate=false" class="p-2 rounded-lg hover:bg-white/10">
          ✕
        </button>
      </div>
    </div>

    {{-- BODY (YANG SCROLL) --}}
    <div class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4">

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

      <form method="POST" action="{{ route('sop.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        {{-- SECTION: Informasi Utama --}}
        <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4">
          <div class="text-xs font-semibold text-blue-700 mb-3 flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-blue-600"></span>
            Informasi Utama
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs text-slate-600 mb-1">Kode SOP <span class="text-rose-500">*</span></label>
              <input type="text" name="code" value="{{ old('code') }}"
                     class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                            {{ $errors->has('code') ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500' : 'border-slate-200 focus:ring-blue-100 focus:border-blue-500' }}"
                     placeholder="Contoh: SOP-PRD-001" required>
              @error('code') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
              <label class="block text-xs text-slate-600 mb-1">Judul SOP <span class="text-rose-500">*</span></label>
              <input type="text" name="title" value="{{ old('title') }}"
                     class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                            {{ $errors->has('title') ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500' : 'border-slate-200 focus:ring-blue-100 focus:border-blue-500' }}"
                     placeholder="Contoh: Prosedur Operasi Alat..." required>
              @error('title') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
              <label class="block text-xs text-slate-600 mb-1">Departemen <span class="text-rose-500">*</span></label>
              <input type="text" name="department" value="{{ old('department') }}"
                     class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                            {{ $errors->has('department') ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500' : 'border-slate-200 focus:ring-blue-100 focus:border-blue-500' }}"
                     placeholder="Produksi / QA / Logistik" required>
              @error('department') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
              <label class="block text-xs text-slate-600 mb-1">Produk (Opsional)</label>
              <input type="text" name="product" value="{{ old('product') }}"
                     class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                            focus:ring-blue-100 focus:border-blue-500"
                     placeholder="Contoh: Nickel Matte / Packing ...">
              @error('product') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>
          </div>
        </div>

        {{-- SECTION: Detail Operasional --}}
        <div class="bg-white border border-blue-100 rounded-xl p-4">
          <div class="text-xs font-semibold text-blue-700 mb-3 flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-blue-600"></span>
            Detail Operasional
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs text-slate-600 mb-1">Lini Produksi (Opsional)</label>
              <input type="text" name="line" value="{{ old('line') }}"
                     class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                            focus:ring-blue-100 focus:border-blue-500"
                     placeholder="Contoh: Line A / Line B">
              @error('line') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
              <label class="block text-xs text-slate-600 mb-1">Tanggal Berlaku Mulai (Opsional)</label>
              <input type="date" name="effective_from" value="{{ old('effective_from') }}"
                     class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                            focus:ring-blue-100 focus:border-blue-500">
              @error('effective_from') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="md:col-span-2">
              <label class="block text-xs text-slate-600 mb-1">Tanggal Berlaku Sampai (Opsional)</label>
              <input type="date" name="effective_to" value="{{ old('effective_to') }}"
                     class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                            {{ $errors->has('effective_to') ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500' : 'border-slate-200 focus:ring-blue-100 focus:border-blue-500' }}">
              @error('effective_to') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>
          </div>
        </div>

        {{-- SECTION: Foto SOP (Array Horizontal) --}}
        <div class="bg-blue-50/40 border border-blue-100 rounded-xl p-4">
          <div class="flex items-center justify-between mb-3">
            <div class="text-xs font-semibold text-blue-700 flex items-center gap-2">
              <span class="h-2 w-2 rounded-full bg-blue-600"></span>
              Foto SOP / Lampiran (Bisa Banyak)
            </div>
            <button type="button"
                    @click="addPhoto()"
                    class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-semibold shadow-sm">
              + Tambah Foto
            </button>
          </div>

          <div class="flex gap-3 overflow-x-auto pb-2 flex-nowrap">
            <template x-for="(p, i) in photos" :key="p.id">
              <div class="bg-white border border-blue-100 rounded-xl p-3 min-w-[280px] md:min-w-[320px] shrink-0">
                <div>
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
                           @change="setPhotoName(i, $event)">
                  </label>
                </div>

                <div class="mt-3">
                  <label class="block text-[11px] text-slate-600 mb-1">Deskripsi Foto</label>
                  <input type="text" name="photo_desc[]"
                         class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                                focus:ring-blue-100 focus:border-blue-500"
                         placeholder="Contoh: Cover SOP / Step 1 / Area kerja">
                </div>

                <div class="mt-3 flex justify-end">
                  <button type="button"
                          @click="removePhoto(i)"
                          class="text-[11px] px-2 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">
                    Hapus
                  </button>
                </div>
              </div>
            </template>
          </div>

          <div class="text-[11px] text-slate-500 mt-2">Geser ke kanan untuk melihat foto lainnya.</div>
        </div>

        {{-- SECTION: Akses SOP --}}
        <div class="bg-white border border-blue-100 rounded-xl p-4">
          <div class="text-xs font-semibold text-blue-700 mb-3 flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-blue-600"></span>
            Akses SOP
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="flex items-center gap-2">
              <input id="is_public" type="checkbox" name="is_public" value="1"
                     class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                     {{ old('is_public') ? 'checked' : '' }}>
              <span class="text-xs text-slate-700">
                Jadikan SOP publik (bisa dibuka via link/QR tanpa login)
              </span>
            </label>

            <div>
              <label class="block text-xs text-slate-600 mb-1">PIN Akses (Opsional)</label>
              <input type="text" name="pin" value="{{ old('pin') }}"
                     class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                            focus:ring-blue-100 focus:border-blue-500"
                     placeholder="Contoh: 1234">
              <div class="text-[11px] text-slate-400 mt-1">
                Jika publik + PIN diisi, SOP perlu PIN sebelum dibuka.
              </div>
            </div>
          </div>
        </div>

        {{-- Isi SOP --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">Isi / Deskripsi SOP (Opsional)</label>
          <textarea name="content" rows="6"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none
                           focus:ring-blue-100 focus:border-blue-500"
                    placeholder="Tuliskan isi SOP atau ringkasan langkah-langkahnya...">{{ old('content') }}</textarea>
        </div>

      </form>
    </div>

    {{-- FOOTER ACTION (STICKY BAWAH) --}}
    <div class="sticky bottom-0 z-10 bg-white/95 backdrop-blur border-t border-blue-100 px-4 md:px-6 py-3">
      <div class="flex items-center justify-end gap-2">
        <button type="button"
                @click="openCreate=false"
                class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
          Batal
        </button>
        <button type="submit"
                form=""
                onclick="this.closest('.relative').querySelector('form').submit()"
                class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm">
          Simpan SOP
        </button>
      </div>
    </div>

  </div>
</div>
{{-- ================= END MODAL ================= --}}

</div>
@endsection
