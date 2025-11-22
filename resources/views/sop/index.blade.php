@extends('layouts.app')
@section('title', 'Daftar SOP')

@section('content')

@php
  $user = auth()->user();
@endphp

<div 
  x-data="{
    openCreate: {{ $errors->any() ? 'true' : 'false' }},
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
  class="space-y-4"
>

  {{-- =======================
     HEADER + FILTER
  ======================= --}}
  <div class="bg-white rounded-2xl border border-[#05727d]/20 shadow-sm p-5">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h2 class="text-base font-semibold text-slate-900">Daftar SOP</h2>
        <p class="text-xs text-slate-500">
          Menampilkan {{ $sops->count() }} dari {{ $sops->total() }} SOP
        </p>
      </div>

      @if ($user->isRole(['admin','produksi']))
        <button
          type="button"
          @click="openCreate = true"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#05727d] hover:bg-[#05727d]/90 text-white text-xs font-semibold shadow-sm transition"
        >
          <span class="text-lg leading-none">+</span>
          Tambah SOP
        </button>
      @endif
    </div>

    {{-- FILTER / SEARCH --}}
    <form method="GET" action="{{ route('sop.index') }}"
          class="mt-4 bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl p-3">
      <div class="grid grid-cols-1 md:grid-cols-6 gap-3 text-xs">

        <div class="md:col-span-2">
          <label class="block mb-1 text-slate-600">Kata Kunci</label>
          <input type="text" name="q" value="{{ request('q') }}"
                 placeholder="Cari kode / judul SOP..."
                 class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                        focus:ring-4 focus:ring-[#05727d]/15 focus:border-[#05727d] outline-none">
        </div>

        <div>
          <label class="block mb-1 text-slate-600">Departemen</label>
          <input type="text" name="department" value="{{ request('department') }}"
                 placeholder="Produksi / QA / Logistik..."
                 class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                        focus:ring-4 focus:ring-[#05727d]/15 focus:border-[#05727d] outline-none">
        </div>

        <div>
          <label class="block mb-1 text-slate-600">Produk</label>
          <input type="text" name="product" value="{{ request('product') }}"
                 placeholder="Produk..."
                 class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                        focus:ring-4 focus:ring-[#05727d]/15 focus:border-[#05727d] outline-none">
        </div>

        <div>
          <label class="block mb-1 text-slate-600">Line</label>
          <input type="text" name="line" value="{{ request('line') }}"
                 placeholder="Line A/B..."
                 class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                        focus:ring-4 focus:ring-[#05727d]/15 focus:border-[#05727d] outline-none">
        </div>

        <div>
          <label class="block mb-1 text-slate-600">Status</label>
          <select name="status"
                  class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                         focus:ring-4 focus:ring-[#05727d]/15 focus:border-[#05727d] outline-none">
            <option value="">Semua Status</option>
            <option value="draft" {{ request('status')=='draft' ? 'selected' : '' }}>Draf</option>
            <option value="waiting_approval" {{ request('status')=='waiting_approval' ? 'selected' : '' }}>Menunggu Persetujuan</option>
            <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>Disetujui</option>
            <option value="expired" {{ request('status')=='expired' ? 'selected' : '' }}>Kedaluwarsa</option>
          </select>
        </div>

        <div class="flex items-end gap-2 md:col-span-6 justify-end">
          <button
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg
                   bg-[#05727d] hover:bg-[#05727d]/90 text-white font-semibold text-xs transition">
            Terapkan
          </button>
          <a href="{{ route('sop.index') }}"
             class="inline-flex items-center justify-center px-4 py-2 rounded-lg
                    bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
            Reset
          </a>
        </div>

      </div>
    </form>
  </div>


  {{-- =======================
     TABLE SOP
  ======================= --}}
  <div class="bg-white rounded-2xl border border-[#05727d]/20 shadow-sm p-0 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-xs bg-white">
        <thead class="bg-[#05727d]/5 text-[#05727d] text-[11px] uppercase tracking-wider sticky top-0 z-10">
          <tr>
            <th class="px-4 py-3 text-left whitespace-nowrap">Kode</th>
            <th class="px-4 py-3 text-left">Judul SOP</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Departemen</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Status</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Updated</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-[#05727d]/10">
          @forelse ($sops as $sop)
            @php
              $photoCount = is_array($sop->photos ?? null) ? count($sop->photos) : 0;
              $canManage  = $user->isRole(['admin','produksi']);
              $canApprove = $user->isRole(['admin','produksi','qa','logistik']) && $sop->status === 'waiting_approval';
            @endphp

            <tr class="hover:bg-[#05727d]/5 transition">
              {{-- KODE --}}
              <td class="px-4 py-3 font-semibold text-slate-900 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <span>{{ $sop->code }}</span>

                  {{-- badge versi kalau ada --}}
                  @if(!is_null($sop->version ?? null))
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-50 border border-slate-200 text-slate-600">
                      v{{ $sop->version }}
                    </span>
                  @endif
                </div>
              </td>

              {{-- JUDUL --}}
              <td class="px-4 py-3 text-slate-800">
                <div class="font-medium">{{ $sop->title }}</div>
                <div class="text-[11px] text-slate-400">
                  Dibuat: {{ optional($sop->created_at)->format('d M Y') }}
                  @if($sop->product || $sop->line)
                    • {{ $sop->product ?? '-' }} {{ $sop->line ? ' / '.$sop->line : '' }}
                  @endif
                </div>

                <div class="mt-1 flex items-center gap-1.5 text-[11px]">
                  <span class="px-2 py-0.5 rounded-full border
                    {{ $sop->is_public ? 'bg-[#05727d]/10 text-[#05727d] border-[#05727d]/30' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                    {{ $sop->is_public ? 'Publik' : 'Privat' }}
                  </span>

                  <span class="px-2 py-0.5 rounded-full border bg-white text-slate-600 border-slate-200">
                    {{ $photoCount }} Foto
                  </span>

                  @if($sop->pin)
                    <span class="px-2 py-0.5 rounded-full border bg-amber-50 text-amber-700 border-amber-200">
                      PIN
                    </span>
                  @endif
                </div>
              </td>

              {{-- DEPT --}}
              <td class="px-4 py-3 text-slate-700 whitespace-nowrap">
                {{ $sop->department }}
              </td>

              {{-- STATUS (pakai accessor) --}}
              <td class="px-4 py-3 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-semibold {{ $sop->status_badge_class }}">
                  {{ $sop->status_label }}
                </span>
              </td>

              {{-- UPDATED --}}
              <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                {{ optional($sop->updated_at)->format('d M Y H:i') }}
              </td>

              {{-- AKSI --}}
              <td class="px-4 py-3 whitespace-nowrap">
                <div class="flex flex-wrap items-center gap-2">

                  {{-- VIEW --}}
                  <a href="{{ route('sop.show', $sop) }}"
                     class="inline-flex items-center px-3 py-1.5 rounded-lg
                            bg-white border border-[#05727d]/30 text-[#05727d]
                            hover:bg-[#05727d]/5 font-semibold text-[11px] transition">
                    Lihat
                  </a>

                  {{-- EDIT --}}
                  @if($canManage && in_array($sop->status, ['draft','waiting_approval']) && Route::has('sop.edit'))
                    <a href="{{ route('sop.edit', $sop) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg
                              bg-white border border-slate-200 text-slate-700
                              hover:bg-slate-50 font-semibold text-[11px] transition">
                      Edit
                    </a>
                  @endif

                  {{-- SUBMIT approval utk draft --}}
                  @if($canManage && $sop->status === 'draft' && Route::has('sop.submit'))
                    <form method="POST" action="{{ route('sop.submit', $sop) }}" class="inline">
                      @csrf
                      <button
                        onclick="return confirm('Ajukan SOP ini untuk approval?');"
                        class="inline-flex items-center px-3 py-1.5 rounded-lg
                               bg-[#05727d] hover:bg-[#05727d]/90 text-white font-semibold text-[11px] transition">
                        Submit
                      </button>
                    </form>
                  @endif

                  {{-- APPROVE / REJECT --}}
                  @if($canApprove)
                    @if(Route::has('sop.approve'))
                      <form method="POST" action="{{ route('sop.approve', $sop) }}" class="inline">
                        @csrf
                        <button
                          onclick="return confirm('Yakin menyetujui SOP ini?');"
                          class="inline-flex items-center px-3 py-1.5 rounded-lg
                                 bg-[#05727d] hover:bg-[#05727d]/90 text-white font-semibold text-[11px] transition">
                          Setujui
                        </button>
                      </form>
                    @endif

                    @if(Route::has('sop.reject'))
                      <form method="POST" action="{{ route('sop.reject', $sop) }}" class="inline">
                        @csrf
                        <button
                          onclick="return confirm('Yakin menolak SOP ini?');"
                          class="inline-flex items-center px-3 py-1.5 rounded-lg
                                 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold text-[11px] transition">
                          Tolak
                        </button>
                      </form>
                    @endif
                  @endif

                  {{-- QR / PDF ketika approved --}}
                  @if($sop->status === 'approved')
                    @if(Route::has('sop.qr'))
                      <form method="POST" action="{{ route('sop.qr', $sop) }}">
                        @csrf
                        <button class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-[11px] transition">
                          QR
                        </button>
                      </form>
                    @endif

                    @if(Route::has('sop.download'))
                      <a href="{{ route('sop.download', $sop) }}"
                         class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-[11px] transition">
                        PDF
                      </a>
                    @endif
                  @endif

                  {{-- MORE: Versions / History --}}
                  @if(Route::has('sop.versions') || Route::has('sop.history'))
                    <div class="relative" x-data="{open:false}">
                      <button @click="open=!open"
                              class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-[11px] transition">
                        More ▾
                      </button>

                      <div x-show="open" @click.outside="open=false" x-transition
                           class="absolute right-0 mt-2 w-36 bg-white border border-slate-200 rounded-xl shadow-lg p-1 text-[11px]"
                           style="display:none;">
                        @if(Route::has('sop.versions'))
                          <a href="{{ route('sop.versions', $sop) }}"
                             class="block px-3 py-2 rounded-lg hover:bg-[#05727d]/5 text-slate-700">
                            Versi SOP
                          </a>
                        @endif
                        @if(Route::has('sop.history'))
                          <a href="{{ route('sop.history', $sop) }}"
                             class="block px-3 py-2 rounded-lg hover:bg-[#05727d]/5 text-slate-700">
                            History
                          </a>
                        @endif
                      </div>
                    </div>
                  @endif

                </div>
              </td>
            </tr>

          @empty
            <tr>
              <td colspan="6" class="px-4 py-12 text-center">
                <div class="mx-auto w-14 h-14 rounded-2xl bg-[#05727d]/10 grid place-items-center text-[#05727d] mb-3">
                  <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
                  </svg>
                </div>
                <div class="text-sm font-semibold text-slate-800 mb-1">Belum ada SOP</div>
                <div class="text-xs text-slate-400">Silakan buat SOP baru atau ubah filter pencarian.</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- PAGINATION --}}
    <div class="px-4 py-3 border-t border-[#05727d]/20">
      {{ $sops->appends(request()->query())->links() }}
    </div>
  </div>



  {{-- ================= MODAL TAMBAH SOP ================= --}}
  <div
    x-show="openCreate"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center px-3 md:px-4"
    style="display:none;"
  >
    <div class="absolute inset-0 bg-black/40" @click="openCreate=false"></div>

    <div
      class="relative w-full max-w-3xl bg-white rounded-2xl border border-[#05727d]/20 shadow-2xl overflow-hidden
             flex flex-col max-h-[90vh] md:max-h-[85vh]"
    >

      <div class="sticky top-0 z-10 bg-gradient-to-r from-[#05727d] to-[#05727d] px-5 md:px-6 py-4 text-white">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-white/15 grid place-items-center">
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
              </svg>
            </div>
            <div>
              <h3 class="text-base font-semibold leading-tight">Tambah SOP Baru</h3>
              <p class="text-xs text-white/80">
                SOP akan masuk antrean persetujuan Produksi, QA, dan Logistik.
              </p>
            </div>
          </div>

          <button @click="openCreate=false" class="p-2 rounded-lg hover:bg-white/10">✕</button>
        </div>
      </div>

      <div class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4">
        @if($errors->any())
          <div class="text-xs rounded-lg bg-[#05727d]/5 border border-[#05727d]/30 text-[#05727d] px-3 py-2">
            <div class="font-semibold mb-1">Periksa kembali input:</div>
            <ul class="list-disc pl-4 space-y-0.5">
              @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form id="sopCreateForm" method="POST" action="{{ route('sop.store') }}" enctype="multipart/form-data" class="space-y-5">
          @csrf

          {{-- Informasi Utama --}}
          <div class="bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl p-4">
            <div class="text-xs font-semibold text-[#05727d] mb-3 flex items-center gap-2">
              <span class="h-2 w-2 rounded-full bg-[#05727d]"></span>
              Informasi Utama
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs text-slate-600 mb-1">Kode SOP <span class="text-rose-500">*</span></label>
                <input type="text" name="code" value="{{ old('code') }}"
                       class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                              {{ $errors->has('code')
                                  ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500'
                                  : 'border-slate-200 focus:ring-[#05727d]/15 focus:border-[#05727d]' }}"
                       placeholder="Contoh: SOP-PRD-001" required>
                @error('code') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
              </div>

              <div>
                <label class="block text-xs text-slate-600 mb-1">Judul SOP <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                       class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                              {{ $errors->has('title')
                                  ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500'
                                  : 'border-slate-200 focus:ring-[#05727d]/15 focus:border-[#05727d]' }}"
                       placeholder="Contoh: Prosedur Operasi Alat..." required>
                @error('title') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
              </div>

              <div>
                <label class="block text-xs text-slate-600 mb-1">Departemen <span class="text-rose-500">*</span></label>
                <input type="text" name="department" value="{{ old('department') }}"
                       class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                              {{ $errors->has('department')
                                  ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500'
                                  : 'border-slate-200 focus:ring-[#05727d]/15 focus:border-[#05727d]' }}"
                       placeholder="Produksi / QA / Logistik" required>
                @error('department') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
              </div>

              <div>
                <label class="block text-xs text-slate-600 mb-1">Produk (Opsional)</label>
                <input type="text" name="product" value="{{ old('product') }}"
                       class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                              focus:ring-[#05727d]/15 focus:border-[#05727d]"
                       placeholder="Contoh: Nickel Matte / Packing ...">
              </div>
            </div>
          </div>

          {{-- Detail Operasional --}}
          <div class="bg-white border border-[#05727d]/20 rounded-xl p-4">
            <div class="text-xs font-semibold text-[#05727d] mb-3 flex items-center gap-2">
              <span class="h-2 w-2 rounded-full bg-[#05727d]"></span>
              Detail Operasional
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs text-slate-600 mb-1">Lini Produksi (Opsional)</label>
                <input type="text" name="line" value="{{ old('line') }}"
                       class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                              focus:ring-[#05727d]/15 focus:border-[#05727d]"
                       placeholder="Line A / Line B">
              </div>

              <div>
                <label class="block text-xs text-slate-600 mb-1">Tanggal Berlaku Mulai (Opsional)</label>
                <input type="date" name="effective_from" value="{{ old('effective_from') }}"
                       class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                              focus:ring-[#05727d]/15 focus:border-[#05727d]">
              </div>

              <div class="md:col-span-2">
                <label class="block text-xs text-slate-600 mb-1">Tanggal Berlaku Sampai (Opsional)</label>
                <input type="date" name="effective_to" value="{{ old('effective_to') }}"
                       class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                              {{ $errors->has('effective_to')
                                  ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500'
                                  : 'border-slate-200 focus:ring-[#05727d]/15 focus:border-[#05727d]' }}">
                @error('effective_to') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          {{-- Foto SOP --}}
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

            <div class="flex gap-3 overflow-x-auto pb-2 flex-nowrap">
              <template x-for="(p, i) in photos" :key="p.id">
                <div class="bg-white border border-[#05727d]/20 rounded-xl p-3 min-w-[280px] md:min-w-[320px] shrink-0">
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

            <div class="text-[11px] text-slate-500 mt-2">Geser ke kanan untuk melihat foto lainnya.</div>
          </div>

          {{-- Akses SOP --}}
          <div class="bg-white border border-[#05727d]/20 rounded-xl p-4">
            <div class="text-xs font-semibold text-[#05727d] mb-3 flex items-center gap-2">
              <span class="h-2 w-2 rounded-full bg-[#05727d]"></span>
              Akses SOP
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <label class="flex items-center gap-2">
                <input id="is_public" type="checkbox" name="is_public" value="1"
                       class="h-4 w-4 rounded border-slate-300 text-[#05727d] focus:ring-[#05727d]"
                       {{ old('is_public') ? 'checked' : '' }}>
                <span class="text-xs text-slate-700">
                  Jadikan SOP publik (bisa dibuka via link/QR tanpa login)
                </span>
              </label>

              <div>
                <label class="block text-xs text-slate-600 mb-1">PIN Akses (Opsional)</label>
                <input type="text" name="pin" value="{{ old('pin') }}"
                       class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                              focus:ring-[#05727d]/15 focus:border-[#05727d]"
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
                             focus:ring-[#05727d]/15 focus:border-[#05727d]"
                      placeholder="Tuliskan isi SOP atau ringkasan langkah-langkahnya...">{{ old('content') }}</textarea>
          </div>

        </form>
      </div>

      {{-- FOOTER ACTION --}}
      <div class="sticky bottom-0 z-10 bg-white/95 backdrop-blur border-t border-[#05727d]/20 px-4 md:px-6 py-3">
        <div class="flex items-center justify-end gap-2">
          <button type="button"
                  @click="openCreate=false"
                  class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
            Batal
          </button>

          <button type="submit"
                  form="sopCreateForm"
                  class="px-5 py-2 rounded-lg bg-[#05727d] hover:bg-[#05727d]/90 text-white text-xs font-semibold shadow-sm">
            Simpan SOP
          </button>
        </div>
      </div>

    </div>
  </div>
  {{-- ================= END MODAL ================= --}}

</div>
@endsection
