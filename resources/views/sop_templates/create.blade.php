{{-- resources/views/sop_templates/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Buat Template SOP')

@section('content')
@php
  $brand = '#05727d';

  // PREPARE DEFAULT JSON (as STRING) UNTUK TEXTAREA / ALPINE
  $formSchemaStr = old('form_schema') ?? json_encode(
    $template->form_schema ?? [
      'title'   => $template->name ?? 'Template SOP',
      'version' => 1,
      'fields'  => [],
    ],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
  );

  $builderSchemaStr = old('builder_schema') ?? json_encode(
    $template->builder_schema ?? [
      'page'   => [],
      'blocks' => [],
    ],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
  );

  $metaStr = old('meta') ?? json_encode(
    $template->meta ?? ['extra_fields' => []],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
  );
@endphp

{{-- SortableJS untuk drag & drop blok di Canvas --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<div class="max-w-6xl mx-auto space-y-6" x-data="tplForm()" x-init="init()">

  {{-- ===== HEADER / HERO ===== --}}
  <div class="bg-white rounded-3xl ring-1 ring-slate-200 p-5 md:p-7">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div class="flex items-start gap-4">
        <div class="shrink-0 w-12 h-12 rounded-2xl bg-[#05727d]/10 text-[#05727d] flex items-center justify-center">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12h6m-6 4h6M7 4h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900">
            Buat Template SOP
          </h1>
          <p class="text-sm text-slate-500 mt-1">
            Isi data dasar &amp; susun layout SOP lewat Canvas (drag &amp; drop) atau edit schema JSON langsung.
          </p>
        </div>
      </div>

      <a href="{{ route('sop.templates.index') }}"
         class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl text-sm bg-slate-100 hover:bg-slate-200 text-slate-700 active:scale-[.98] transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
      </a>
    </div>
  </div>

  {{-- ===== ERRORS ===== --}}
  @if ($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 text-rose-800 ring-1 ring-rose-200">
      <div class="font-semibold mb-1">Ada error:</div>
      <ul class="list-disc ml-5 text-sm space-y-1">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('sop.templates.store') }}" method="POST" class="space-y-6">
    @csrf

    {{-- ===== TABS (BASIC / SCHEMA) ===== --}}
    <div class="flex flex-wrap gap-2 bg-white ring-1 ring-slate-200 rounded-2xl p-2">
      <button type="button" @click="tab='basic'" class="px-4 py-2 rounded-xl text-sm font-semibold transition"
              :class="tab === 'basic'
                ? 'bg-[#05727d] text-white shadow-sm'
                : 'bg-transparent text-slate-700 hover:bg-slate-50'">
        Informasi Dasar
      </button>

      <button type="button" @click="tab='schema'" class="px-4 py-2 rounded-xl text-sm font-semibold transition"
              :class="tab === 'schema'
                ? 'bg-[#05727d] text-white shadow-sm'
                : 'bg-transparent text-slate-700 hover:bg-slate-50'">
        Layout &amp; Schema
      </button>
    </div>

    {{-- ===== BASIC ===== --}}
    <div x-show="tab==='basic'" x-transition
         class="bg-white rounded-3xl ring-1 ring-slate-200 p-5 md:p-6 space-y-4">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
        <div>
          <label class="text-sm font-semibold text-slate-700">Nama Template *</label>
          <input type="text" name="name" value="{{ old('name') }}"
                 class="mt-1 w-full rounded-2xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-sm py-2.5"
                 placeholder="SOP Penimbangan &amp; Mixing">
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Kode Template (unik)</label>
          <input type="text" name="code" value="{{ old('code') }}"
                 class="mt-1 w-full rounded-2xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-sm py-2.5"
                 placeholder="TMP-SOP-001">
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Departemen</label>
          <input type="text" name="department" value="{{ old('department') }}"
                 class="mt-1 w-full rounded-2xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-sm py-2.5"
                 placeholder="Produksi / HSE / QC">
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Product</label>
          <input type="text" name="product" value="{{ old('product') }}"
                 class="mt-1 w-full rounded-2xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-sm py-2.5"
                 placeholder="Nama produk">
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Line</label>
          <input type="text" name="line" value="{{ old('line') }}"
                 class="mt-1 w-full rounded-2xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-sm py-2.5"
                 placeholder="Line A / Area">
        </div>

        <div class="flex items-center gap-3 md:mt-6">
          <input type="hidden" name="is_active" value="0">
          <input id="is_active" type="checkbox" name="is_active" value="1"
                 {{ old('is_active', 1) ? 'checked' : '' }}
                 class="rounded border-slate-300 text-[#05727d] focus:ring-[#05727d]">
          <label for="is_active" class="text-sm font-semibold text-slate-700">
            Aktifkan Template
          </label>
        </div>
      </div>

      <div class="pt-2 text-xs text-slate-500">
        * Wajib diisi. Kode template disarankan unik biar gampang dicari.
      </div>
    </div>

    {{-- ===== LAYOUT & SCHEMA (CANVAS + JSON) ===== --}}
    <div x-show="tab==='schema'" x-transition
         class="bg-white rounded-3xl ring-1 ring-slate-200 p-5 md:p-6 space-y-5">

      {{-- ===== IMPORT FROM SOP ===== --}}
      <div class="rounded-2xl ring-1 ring-slate-200 bg-white p-4 md:p-5 space-y-3">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
          <div>
            <div class="text-sm font-extrabold text-slate-900">Import Schema dari SOP</div>
            <div class="text-xs text-slate-500 mt-0.5">
              Pilih SOP yang sudah ada, lalu ambil schema-nya ke Template ini.
            </div>
          </div>

          <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
            <input type="text" x-model="sopSearch"
                   class="w-full sm:w-56 rounded-xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-sm py-2"
                   placeholder="Cari SOP (kode/judul)...">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-center">
          <div class="md:col-span-8">
            <label class="text-xs font-semibold text-slate-600">Pilih SOP</label>
            <select x-model="importSopId" x-ref="sopSelect"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-sm py-2.5">
              <option value="">-- pilih SOP --</option>

              @foreach ($sops ?? [] as $s)
                <option value="{{ $s->id }}"
                        data-text="{{ strtolower(($s->code ?? '') . ' ' . ($s->title ?? '') . ' ' . ($s->department ?? '') . ' ' . ($s->product ?? '') . ' ' . ($s->line ?? '')) }}">
                  {{ $s->code }} — {{ $s->title }}
                  ({{ $s->department ?? '-' }} / {{ $s->product ?? '-' }} / {{ $s->line ?? '-' }})
                  • {{ $s->status ?? 'draft' }}
                </option>
              @endforeach
            </select>

            <div class="text-[11px] text-slate-500 mt-1">
              Total SOP loaded: {{ ($sops ?? collect())->count() }}.
            </div>
          </div>

          <div class="md:col-span-4 flex gap-2 md:justify-end mt-1 md:mt-6">
            <button type="button" @click="importFromSop()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-[#05727d] hover:bg-[#045e68] text-white text-sm font-semibold shadow-sm active:scale-[.98] transition disabled:opacity-60"
                    :disabled="importLoading || !importSopId">
              <svg x-show="!importLoading" class="w-4 h-4" fill="none" stroke="currentColor"
                   stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v16h16M4 12h16M12 4v16"/>
              </svg>
              <svg x-show="importLoading" class="w-4 h-4 animate-spin" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                        fill="none" opacity=".25"/>
                <path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="4" fill="none"/>
              </svg>
              <span x-text="importLoading ? 'Mengambil...' : 'Import Schema'"></span>
            </button>

            <button type="button" @click="clearImported()"
                    class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm active:scale-[.98] transition">
              Clear
            </button>
          </div>
        </div>

        <div x-show="importInfo" x-transition class="text-xs text-slate-600">
          <span class="font-semibold">SOP Terpilih:</span>
          <span x-text="importInfo"></span>
        </div>
      </div>

      {{-- ===== SUB TABS: CANVAS + JSON ===== --}}
      <div class="flex flex-wrap gap-2">
        <button type="button" @click="schemaTab='canvas'"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition ring-1"
                :class="schemaTab === 'canvas'
                  ? 'bg-[#05727d] text-white ring-[#05727d]'
                  : 'bg-white text-slate-700 ring-slate-200 hover:bg-slate-50'">
          Canvas
        </button>

        <button type="button" @click="schemaTab='form'"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition ring-1"
                :class="schemaTab === 'form'
                  ? 'bg-[#05727d] text-white ring-[#05727d]'
                  : 'bg-white text-slate-700 ring-slate-200 hover:bg-slate-50'">
          form_schema
        </button>

        <button type="button" @click="schemaTab='builder'"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition ring-1"
                :class="schemaTab === 'builder'
                  ? 'bg-[#05727d] text-white ring-[#05727d]'
                  : 'bg-white text-slate-700 ring-slate-200 hover:bg-slate-50'">
          builder_schema
        </button>

        <button type="button" @click="schemaTab='meta'"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition ring-1"
                :class="schemaTab === 'meta'
                  ? 'bg-[#05727d] text-white ring-[#05727d]'
                  : 'bg-white text-slate-700 ring-slate-200 hover:bg-slate-50'">
          meta
        </button>
      </div>

      <div class="space-y-4">

        {{-- ========== CANVAS DESIGNER ========== --}}
        <template x-if="schemaTab === 'canvas'">
          <div class="grid gap-4 md:grid-cols-[230px,1fr,260px] items-start">

            {{-- LEFT: library blok --}}
            <div class="bg-white rounded-2xl ring-1 ring-slate-200 p-3 space-y-3">
              <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                Komponen
              </div>

              <button type="button" @click="addBlock('heading')"
                      class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs bg-slate-50 hover:bg-slate-100 text-slate-800 border border-slate-200 active:scale-[.98] transition">
                <span
                  class="w-6 h-6 rounded-lg bg-[#05727d]/10 text-[#05727d] flex items-center justify-center text-[11px] font-bold">H</span>
                <span>Heading (judul)</span>
              </button>

              <button type="button" @click="addBlock('paragraph')"
                      class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs bg-slate-50 hover:bg-slate-100 text-slate-800 border border-slate-200 active:scale-[.98] transition">
                <span
                  class="w-6 h-6 rounded-lg bg-[#05727d]/10 text-[#05727d] flex items-center justify-center text-[11px] font-bold">P</span>
                <span>Paragraf</span>
              </button>

              <button type="button" @click="addBlock('info_table')"
                      class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs bg-slate-50 hover:bg-slate-100 text-slate-800 border border-slate-200 active:scale-[.98] transition">
                <span
                  class="w-6 h-6 rounded-lg bg-[#05727d]/10 text-[#05727d] flex items-center justify-center text-[11px] font-bold">Tb</span>
                <span>Tabel Info Dokumen</span>
              </button>

              <button type="button" @click="addBlock('checklist')"
                      class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs bg-slate-50 hover:bg-slate-100 text-slate-800 border border-slate-200 active:scale-[.98] transition">
                <span
                  class="w-6 h-6 rounded-lg bg-[#05727d]/10 text-[#05727d] flex items-center justify-center text-[11px] font-bold">CL</span>
                <span>Checklist Langkah Kerja</span>
              </button>

              {{-- KOMPOnen FOTO --}}
              <button type="button" @click="addBlock('photos')"
                      class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs bg-slate-50 hover:bg-slate-100 text-slate-800 border border-slate-200 active:scale-[.98] transition">
                <span
                  class="w-6 h-6 rounded-lg bg-[#05727d]/10 text-[#05727d] flex items-center justify-center text-[11px] font-bold">Img</span>
                <span>Foto Pendukung</span>
              </button>

              <p class="text-[11px] text-slate-500 pt-1">
                Tips: drag blok di tengah untuk atur urutan. Klik blok untuk edit isi di panel kanan.
              </p>
            </div>

            {{-- CENTER: canvas dokumen --}}
            <div class="bg-slate-100/80 rounded-2xl p-3 md:p-5">
              <div
                class="mx-auto max-w-[720px] bg-white border border-slate-200 shadow-sm rounded-xl p-5 md:p-7 relative">

                <div
                  class="absolute -top-3 left-4 text-[10px] px-3 py-0.5 rounded-full bg-[#05727d] text-white tracking-wide">
                  Preview Template SOP
                </div>

                {{-- header dokumen --}}
                <div class="mb-4 border-b border-slate-200 pb-3">
                  <div class="text-[10px] font-semibold text-[#05727d] uppercase tracking-[0.25em]">
                    <span x-text="builder.page.title"></span>
                  </div>
                  <div class="text-[11px] text-slate-500 mt-1" x-text="builder.page.subtitle"></div>
                </div>

                {{-- list blok --}}
                <div class="space-y-3" x-ref="canvasList">
                  <template x-for="(b, idx) in builder.blocks" :key="b.id">
                    <div class="group border border-dashed border-slate-200 rounded-xl px-3 py-2.5 relative cursor-pointer bg-white"
                         :class="selectedId === b.id ? 'border-[#05727d] bg-[#05727d]/3' :
                                 'hover:border-[#05727d]/50'"
                         @click.stop="setSelected(b.id)">

                      {{-- tombol drag + up/down --}}
                      <div
                        class="absolute -left-3 top-2 flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition">
                        <button type="button"
                                class="w-5 h-5 rounded-full bg-white shadow flex items-center justify-center text-[10px] text-slate-400 border border-slate-200 drag-handle"
                                title="Drag">
                          ≡
                        </button>
                        <button type="button"
                                class="w-5 h-5 rounded-full bg-white shadow flex items-center justify-center text-[10px] text-slate-400 border border-slate-200"
                                @click.stop="moveBlockUp(idx)">↑</button>
                        <button type="button"
                                class="w-5 h-5 rounded-full bg-white shadow flex items-center justify-center text-[10px] text-slate-400 border border-slate-200"
                                @click.stop="moveBlockDown(idx)">↓</button>
                      </div>

                      {{-- tombol hapus --}}
                      <button type="button"
                              class="absolute -right-3 -top-3 w-6 h-6 rounded-full bg-rose-600 text-white text-[10px] shadow opacity-0 group-hover:opacity-100"
                              @click.stop="removeBlock(idx)">
                        ✕
                      </button>

                      {{-- preview per tipe blok --}}
                      <div x-show="b.type === 'heading'">
                        <div :class="{
                                    'text-lg font-bold text-slate-900 mb-1 text-center': b.level == 1,
                                    'text-base font-semibold text-slate-900 mb-1': b.level == 2,
                                    'text-sm font-semibold text-slate-800 mb-1': b.level == 3
                                  }"
                             :class="b.align === 'center' ? 'text-center' : 'text-left'"
                             x-text="b.text || 'Heading'"></div>
                      </div>

                      <div x-show="b.type === 'paragraph'">
                        <p class="text-[11px] leading-relaxed text-slate-700 whitespace-pre-line"
                           x-text="b.text || 'Teks paragraf SOP ...'"></p>
                      </div>

                      <div x-show="b.type === 'info_table'">
                        <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide mb-1"
                             x-text="b.title || 'INFORMASI'"></div>
                        <table class="w-full text-[11px]">
                          <tbody>
                          <template x-for="(row, rIdx) in b.rows" :key="rIdx">
                            <tr>
                              <td class="pr-2 text-slate-500 align-top w-32" x-text="row.label"></td>
                              <td class="align-top text-slate-800">: <span x-text="row.value"></span></td>
                            </tr>
                          </template>
                          </tbody>
                        </table>
                      </div>

                      <div x-show="b.type === 'checklist'">
                        <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide mb-1"
                             x-text="b.title || 'Checklist'"></div>
                        <ul class="space-y-1">
                          <template x-for="(it, iIdx) in b.items" :key="iIdx">
                            <li class="flex items-start gap-2 text-[11px] text-slate-700">
                              <span class="mt-[3px] w-3 h-3 rounded border border-slate-400"></span>
                              <div>
                                <div x-text="it.text || 'Item checklist'"></div>
                                <div class="text-[10px] text-slate-400"
                                     x-text="it.note"></div>
                              </div>
                            </li>
                          </template>
                        </ul>
                      </div>

                      {{-- FOTO BLOCK PREVIEW --}}
                      <div x-show="b.type === 'photos'">
                        <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide mb-1"
                             x-text="b.title || 'Foto Pendukung'"></div>
                        <div class="grid grid-cols-2 gap-3">
                          <template x-for="(ph, pIdx) in b.photos" :key="pIdx">
                            <div class="border border-dashed border-slate-300 rounded-lg p-1 bg-slate-50">
                              <div class="aspect-video bg-white rounded-md overflow-hidden flex items-center justify-center">
                                <template x-if="ph.url">
                                  <img :src="ph.url" class="w-full h-full object-contain" alt="">
                                </template>
                                <template x-if="!ph.url">
                                  <div class="text-[10px] text-slate-400">Foto belum tersedia</div>
                                </template>
                              </div>
                              <div class="mt-1 text-[11px] text-slate-600"
                                   x-text="ph.label || ('Foto ' + (pIdx+1))"></div>
                            </div>
                          </template>
                        </div>
                      </div>

                    </div>
                  </template>

                  <button type="button"
                          class="mt-3 w-full border-2 border-dashed border-slate-200 rounded-xl py-3 text-xs text-slate-500 hover:border-[#05727d]/60 hover:text-[#05727d] active:scale-[.98] transition"
                          @click="addBlock('paragraph')">
                    + Tambah blok paragraf
                  </button>
                </div>
              </div>
            </div>

            {{-- RIGHT: inspector --}}
            <div class="space-y-3">

              {{-- Pengaturan halaman --}}
              <div class="bg-white rounded-2xl ring-1 ring-slate-200 p-3 space-y-2">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">
                  Pengaturan Halaman
                </div>

                <div>
                  <label class="text-[11px] text-slate-600">Judul header</label>
                  <input type="text"
                         class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-xs py-1.5"
                         x-model="builder.page.title" @input="syncBuilderToJson()">
                </div>

                <div>
                  <label class="text-[11px] text-slate-600">Subjudul</label>
                  <input type="text"
                         class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-xs py-1.5"
                         x-model="builder.page.subtitle" @input="syncBuilderToJson()">
                </div>

                <div class="flex items-center gap-2 pt-1">
                  <input type="checkbox"
                         class="rounded border-slate-300 text-[#05727d] focus:ring-[#05727d]"
                         x-model="builder.page.show_logo" @change="syncBuilderToJson()">
                  <span class="text-[11px] text-slate-600">Tampilkan logo/kop di header</span>
                </div>

                <div class="flex items-center gap-2">
                  <input type="checkbox"
                         class="rounded border-slate-300 text-[#05727d] focus:ring-[#05727d]"
                         x-model="builder.page.show_page_number" @change="syncBuilderToJson()">
                  <span class="text-[11px] text-slate-600">Tampilkan nomor halaman</span>
                </div>
              </div>

              {{-- Pengaturan blok terpilih --}}
              <div class="bg-white rounded-2xl ring-1 ring-slate-200 p-3" x-show="currentBlock()">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                  Detail Blok
                </div>

                <template x-if="currentBlock()">
                  <div class="space-y-2">
                    <div class="text-[11px] text-slate-500 mb-1">
                      Jenis: <span class="font-semibold" x-text="currentBlock().type"></span>
                    </div>

                    {{-- Heading --}}
                    <div x-show="currentBlock().type === 'heading'" class="space-y-2">
                      <div>
                        <label class="text-[11px] text-slate-600">Teks heading</label>
                        <input type="text"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-xs py-1.5"
                               x-model="currentBlock().text" @input="syncBuilderToJson()">
                      </div>

                      <div class="grid grid-cols-2 gap-2">
                        <div>
                          <label class="text-[11px] text-slate-600">Level</label>
                          <select
                            class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-xs py-1.5"
                            x-model.number="currentBlock().level"
                            @change="syncBuilderToJson()">
                            <option :value="1">H1 (judul utama)</option>
                            <option :value="2">H2 (sub judul)</option>
                            <option :value="3">H3 (sub section)</option>
                          </select>
                        </div>
                        <div>
                          <label class="text-[11px] text-slate-600">Align</label>
                          <select
                            class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-xs py-1.5"
                            x-model="currentBlock().align" @change="syncBuilderToJson()">
                            <option value="left">Kiri</option>
                            <option value="center">Tengah</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    {{-- Paragraph --}}
                    <div x-show="currentBlock().type === 'paragraph'" class="space-y-1">
                      <label class="text-[11px] text-slate-600">Isi paragraf</label>
                      <textarea rows="4"
                                class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-xs p-2"
                                x-model="currentBlock().text" @input="syncBuilderToJson()"></textarea>
                      <p class="text-[10px] text-slate-400">
                        Bisa pakai enter untuk paragraf baru. Placeholder seperti
                        <code>@{{ sop.code }}</code> akan dipakai saat generate dokumen dari SOP.
                      </p>
                    </div>

                    {{-- Info table --}}
                    <div x-show="currentBlock().type === 'info_table'" class="space-y-2">
                      <div>
                        <label class="text-[11px] text-slate-600">Judul blok</label>
                        <input type="text"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-xs py-1.5"
                               x-model="currentBlock().title" @input="syncBuilderToJson()">
                      </div>

                      <div class="space-y-1">
                        <div class="text-[11px] text-slate-500">Baris tabel</div>
                        <template x-for="(row, rIdx) in currentBlock().rows" :key="rIdx">
                          <div class="flex items-center gap-1">
                            <input type="text"
                                   class="w-28 rounded-lg border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-[11px] py-1 px-2"
                                   placeholder="Label" x-model="row.label"
                                   @input="syncBuilderToJson()">
                            <input type="text"
                                   class="flex-1 rounded-lg border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-[11px] py-1 px-2"
                                   placeholder="Nilai / placeholder" x-model="row.value"
                                   @input="syncBuilderToJson()">
                            <button type="button"
                                    class="w-6 h-6 rounded-full bg-rose-50 text-rose-600 text-[11px] flex items-center justify-center border border-rose-200"
                                    @click="currentBlock().rows.splice(rIdx,1); syncBuilderToJson();">
                              ✕
                            </button>
                          </div>
                        </template>

                        <button type="button"
                                class="mt-1 inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[11px] text-slate-700 active:scale-[.98] transition"
                                @click="currentBlock().rows.push({label:'Field',value:'Isi'}); syncBuilderToJson();">
                          + Tambah baris
                        </button>
                      </div>
                    </div>

                    {{-- Checklist --}}
                    <div x-show="currentBlock().type === 'checklist'" class="space-y-2">
                      <div>
                        <label class="text-[11px] text-slate-600">Judul checklist</label>
                        <input type="text"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-xs py-1.5"
                               x-model="currentBlock().title" @input="syncBuilderToJson()">
                      </div>

                      <div class="space-y-1">
                        <div class="text-[11px] text-slate-500">Item</div>
                        <template x-for="(it, iIdx) in currentBlock().items" :key="iIdx">
                          <div class="flex items-center gap-1">
                            <input type="text"
                                   class="flex-1 rounded-lg border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-[11px] py-1 px-2"
                                   placeholder="Langkah / checklist" x-model="it.text"
                                   @input="syncBuilderToJson()">
                            <input type="text"
                                   class="flex-1 rounded-lg border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-[11px] py-1 px-2"
                                   placeholder="Catatan (opsional)" x-model="it.note"
                                   @input="syncBuilderToJson()">
                            <button type="button"
                                    class="w-6 h-6 rounded-full bg-rose-50 text-rose-600 text-[11px] flex items-center justify-center border border-rose-200"
                                    @click="currentBlock().items.splice(iIdx,1); syncBuilderToJson();">
                              ✕
                            </button>
                          </div>
                        </template>

                        <button type="button"
                                class="mt-1 inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[11px] text-slate-700 active:scale-[.98] transition"
                                @click="currentBlock().items.push({text:'Item checklist',note:''}); syncBuilderToJson();">
                          + Tambah item
                        </button>
                      </div>
                    </div>

                    {{-- Photos --}}
                    <div x-show="currentBlock().type === 'photos'" class="space-y-2">
                      <div>
                        <label class="text-[11px] text-slate-600">Judul blok foto</label>
                        <input type="text"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-xs py-1.5"
                               x-model="currentBlock().title" @input="syncBuilderToJson()">
                      </div>

                      <div class="space-y-1">
                        <div class="text-[11px] text-slate-500">Foto</div>
                        <template x-for="(ph, pIdx) in currentBlock().photos" :key="pIdx">
                          <div class="flex items-center gap-1">
                            <input type="text"
                                   class="flex-1 rounded-lg border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-[11px] py-1 px-2"
                                   placeholder="Label / nama foto" x-model="ph.label"
                                   @input="syncBuilderToJson()">
                            <input type="text"
                                   class="flex-[2] rounded-lg border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-[11px] py-1 px-2"
                                   placeholder="URL (opsional)" x-model="ph.url"
                                   @input="syncBuilderToJson()">
                            <button type="button"
                                    class="w-6 h-6 rounded-full bg-rose-50 text-rose-600 text-[11px] flex items-center justify-center border border-rose-200"
                                    @click="currentBlock().photos.splice(pIdx,1); syncBuilderToJson();">
                              ✕
                            </button>
                          </div>
                        </template>

                        <button type="button"
                                class="mt-1 inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[11px] text-slate-700 active:scale-[.98] transition"
                                @click="currentBlock().photos.push({label:'Foto',url:''}); syncBuilderToJson();">
                          + Tambah foto
                        </button>

                        <p class="text-[10px] text-slate-400">
                          URL foto otomatis terisi saat import dari SOP. Di sini bisa ganti label / URL jika perlu.
                        </p>
                      </div>
                    </div>

                  </div>
                </template>
              </div>

            </div>

          </div>
        </template>

        {{-- ========== RAW JSON EDITOR (form/builder/meta) ========== --}}
        <template x-if="schemaTab !== 'canvas'">
          <div class="space-y-2">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-2">
              <div class="text-sm font-semibold text-slate-800" x-text="labelFor(schemaTab)"></div>

              <div class="flex gap-2">
                <button type="button" @click="formatJSON(schemaTab)"
                        class="px-3 py-1.5 rounded-xl text-xs bg-slate-100 hover:bg-slate-200 active:scale-[.98] transition">
                  Rapihin JSON
                </button>
                <button type="button" @click="validateJSON(schemaTab)"
                        class="px-3 py-1.5 rounded-xl text-xs bg-slate-100 hover:bg-slate-200 active:scale-[.98] transition">
                  Cek JSON
                </button>
              </div>
            </div>

            <div class="rounded-2xl ring-1 ring-slate-200 overflow-hidden bg-slate-50">
              <textarea x-show="schemaTab==='form'" name="form_schema" x-model="json.form" rows="14"
                        class="w-full font-mono text-xs bg-transparent border-0 focus:ring-0 p-3 md:p-4"></textarea>

              <textarea x-show="schemaTab==='builder'" name="builder_schema" x-model="json.builder" rows="14"
                        class="w-full font-mono text-xs bg-transparent border-0 focus:ring-0 p-3 md:p-4"></textarea>

              <textarea x-show="schemaTab==='meta'" name="meta" x-model="json.meta" rows="14"
                        class="w-full font-mono text-xs bg-transparent border-0 focus:ring-0 p-3 md:p-4"></textarea>
            </div>

            <p class="text-xs text-slate-500">
              Schema JSON ini dipakai generate form &amp; layout SOP. Canvas akan otomatis menulis ke
              <code>builder_schema</code>.
            </p>
          </div>
        </template>

      </div>

      {{-- Toast --}}
      <div x-show="toast.show" x-transition
           class="fixed bottom-5 right-5 px-4 py-2 rounded-2xl text-sm shadow-lg ring-1"
           :class="toast.danger
             ? 'bg-rose-600 text-white ring-rose-700'
             : 'bg-[#05727d] text-white ring-[#05727d]'">
        <span x-text="toast.msg"></span>
      </div>
    </div>

    {{-- ===== ACTIONS ===== --}}
    <div class="flex justify-end gap-2">
      <a href="{{ route('sop.templates.index') }}"
         class="px-4 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 active:scale-[.98] transition">
        Batal
      </a>
      <button type="submit"
              class="px-5 py-2 rounded-2xl bg-[#05727d] hover:bg-[#045e68] text-white font-semibold shadow-sm active:scale-[.98] transition">
        Buat Template
      </button>
    </div>

  </form>
</div>

<script>
  function tplForm() {
    return {
      tab: 'basic',
      schemaTab: 'canvas',

      json: {
        form: @json($formSchemaStr),
        builder: @json($builderSchemaStr),
        meta: @json($metaStr),
      },

      // state canvas
      builder: {
        page: {},
        blocks: []
      },
      selectedId: null,
      sortable: null,

      // SOP dropdown + search (import)
      importSopId: '',
      importLoading: false,
      importInfo: '',
      sopSearch: '',

      toast: {
        show: false,
        msg: '',
        danger: false
      },

      init() {
        this.tryFormatAll();
        this.syncBuilderFromJson();
        this.$nextTick(() => {
          this.bindSopSearch();
          this.initSortable();
        });
      },

      // =============== CANVAS ENGINE ===============
      syncBuilderFromJson() {
        let obj = {};
        try {
          obj = this.json.builder ? JSON.parse(this.json.builder) : {};
        } catch (e) {
          obj = {};
        }
        this.builder = this.normalizeBuilder(obj);
        if (!this.selectedId && this.builder.blocks.length) {
          this.selectedId = this.builder.blocks[0].id;
        }
        this.syncBuilderToJson();
      },

      syncBuilderToJson() {
        if (!this.builder) return;
        this.json.builder = JSON.stringify(this.builder, null, 2);
      },

      normalizeBuilder(obj) {
        const base = {
          page: {
            theme: (obj.page && obj.page.theme) || '#05727d',
            title: (obj.page && obj.page.title) || 'STANDARD OPERATING PROCEDURE',
            subtitle: (obj.page && obj.page.subtitle) || 'Template SOP',
            show_logo: obj.page && typeof obj.page.show_logo === 'boolean' ? obj.page.show_logo : true,
            show_page_number: obj.page && typeof obj.page.show_page_number === 'boolean'
              ? obj.page.show_page_number
              : true,
          },
          blocks: []
        };

        let blocks = [];
        if (Array.isArray(obj.blocks)) {
          blocks = obj.blocks;
        } else if (Array.isArray(obj.sections)) {
          blocks = obj.sections;
        }

        if (!blocks.length) {
          base.blocks = this.defaultBlocks();
        } else {
          base.blocks = blocks.map(b => this.normalizeBlock(b));
        }

        return base;
      },

      normalizeBlock(b) {
        if (!b) b = {};
        if (!b.id) {
          b.id = 'blk_' + Math.random().toString(36).slice(2, 9);
        }
        if (!b.type) {
          b.type = 'paragraph';
        }

        if (b.type === 'heading') {
          b.level = b.level || 2;
          b.text = b.text || 'Judul Section';
          b.align = b.align || 'left';
        }

        if (b.type === 'paragraph') {
          b.text = b.text || 'Tulis instruksi / deskripsi SOP di sini...';
        }

        if (b.type === 'info_table') {
          b.title = b.title || 'INFORMASI DOKUMEN';
          if (!Array.isArray(b.rows) || !b.rows.length) {
            b.rows = [
              { label: 'Kode Dokumen',   value: '@{{ sop.code }}' },
              { label: 'Departemen',     value: '@{{ sop.department }}' },
              { label: 'Produk / Line',  value: '@{{ sop.product }} / @{{ sop.line }}' },
              { label: 'Revisi',         value: '1' },
            ];
          }
        }

        if (b.type === 'checklist') {
          b.title = b.title || 'Checklist Langkah Kerja';
          if (!Array.isArray(b.items) || !b.items.length) {
            b.items = [
              { text: 'Persiapan area & alat', note: '' },
              { text: 'Pelaksanaan prosedur utama', note: '' },
            ];
          }
        }

        if (b.type === 'photos') {
          b.title = b.title || 'FOTO PENDUKUNG';
          if (!Array.isArray(b.photos)) {
            b.photos = [];
          } else {
            // normalisasi struktur foto
            b.photos = b.photos.map((ph, idx) => ({
              url: ph.url || ph.path || '',
              label: ph.label || ph.desc || ('Foto ' + (idx + 1)),
            }));
          }
        }

        return b;
      },

      defaultBlocks() {
        return [
          {
            id: 'blk_title',
            type: 'heading',
            level: 1,
            align: 'center',
            text: 'JUDUL SOP'
          },
          {
            id: 'blk_info',
            type: 'info_table',
            title: 'INFORMASI DOKUMEN',
            rows: [
              { label: 'Kode Dokumen',   value: '@{{ sop.code }}' },
              { label: 'Departemen',     value: '@{{ sop.department }}' },
              { label: 'Produk / Line',  value: '@{{ sop.product }} / @{{ sop.line }}' },
              { label: 'Revisi',         value: '1' },
            ]
          },
          {
            id: 'blk_purpose',
            type: 'heading',
            level: 2,
            align: 'left',
            text: 'TUJUAN'
          },
          {
            id: 'blk_purpose_text',
            type: 'paragraph',
            text: 'Menjelaskan tujuan pelaksanaan prosedur ini...'
          },
          {
            id: 'blk_procedure',
            type: 'heading',
            level: 2,
            align: 'left',
            text: 'PROSEDUR'
          },
          {
            id: 'blk_steps',
            type: 'checklist',
            title: 'Langkah Kerja',
            items: [
              { text: 'Persiapan area & alat', note: '' },
              { text: 'Pelaksanaan prosedur utama', note: '' },
            ]
          },
        ];
      },

      makeBlock(type) {
        switch (type) {
          case 'heading':
            return {
              id: 'blk_' + Date.now() + '_h',
              type: 'heading',
              level: 2,
              align: 'left',
              text: 'Judul Section'
            };
          case 'info_table':
            return {
              id: 'blk_' + Date.now() + '_t',
              type: 'info_table',
              title: 'INFORMASI',
              rows: [{ label: 'Field', value: 'Isi / placeholder' }]
            };
          case 'checklist':
            return {
              id: 'blk_' + Date.now() + '_c',
              type: 'checklist',
              title: 'Checklist',
              items: [{ text: 'Item checklist', note: '' }]
            };
          case 'photos':
            return {
              id: 'blk_' + Date.now() + '_ph',
              type: 'photos',
              title: 'FOTO PENDUKUNG',
              photos: []
            };
          default:
            return {
              id: 'blk_' + Date.now() + '_p',
              type: 'paragraph',
              text: 'Teks paragraf SOP ...'
            };
        }
      },

      addBlock(type) {
        if (!this.builder) {
          this.builder = this.normalizeBuilder({});
        }
        const blk = this.makeBlock(type);
        this.builder.blocks.push(blk);
        this.selectedId = blk.id;
        this.syncBuilderToJson();
        this.$nextTick(() => this.initSortable());
      },

      removeBlock(index) {
        if (!this.builder || !this.builder.blocks[index]) return;
        const removed = this.builder.blocks.splice(index, 1)[0];
        if (this.selectedId === removed.id) {
          this.selectedId = this.builder.blocks[0]?.id || null;
        }
        this.syncBuilderToJson();
      },

      moveBlockUp(index) {
        const arr = this.builder.blocks;
        if (index <= 0 || index >= arr.length) return;
        const tmp = arr[index - 1];
        arr[index - 1] = arr[index];
        arr[index] = tmp;
        this.syncBuilderToJson();
      },

      moveBlockDown(index) {
        const arr = this.builder.blocks;
        if (index >= arr.length - 1) return;
        const tmp = arr[index + 1];
        arr[index + 1] = arr[index];
        arr[index] = tmp;
        this.syncBuilderToJson();
      },

      setSelected(id) {
        this.selectedId = id;
      },

      currentBlock() {
        if (!this.builder || !Array.isArray(this.builder.blocks)) return null;
        return this.builder.blocks.find(b => b.id === this.selectedId) || this.builder.blocks[0] || null;
      },

      initSortable() {
        if (this.sortable || !this.$refs.canvasList || !window.Sortable) return;
        this.sortable = Sortable.create(this.$refs.canvasList, {
          handle: '.drag-handle',
          animation: 150,
          onEnd: (evt) => {
            if (!this.builder || !Array.isArray(this.builder.blocks)) return;
            const moved = this.builder.blocks.splice(evt.oldIndex, 1)[0];
            this.builder.blocks.splice(evt.newIndex, 0, moved);
            this.syncBuilderToJson();
          }
        });
      },

      // =============== JSON RAW ===============
      labelFor(t) {
        if (t === 'canvas') return 'Canvas Designer';
        return t === 'form' ? 'form_schema (JSON)'
          : t === 'builder' ? 'builder_schema (JSON)'
          : 'meta (JSON)';
      },

      tryFormatAll() {
        ['form', 'builder', 'meta'].forEach(k => {
          try {
            this.json[k] = JSON.stringify(JSON.parse(this.json[k]), null, 2);
          } catch (e) {}
        });
      },

      formatJSON(tab) {
        if (tab === 'canvas') {
          this.syncBuilderToJson();
          this.notify('Canvas disinkron ke JSON ✅');
          return;
        }
        const k = tab === 'form' ? 'form' : tab === 'builder' ? 'builder' : 'meta';
        try {
          this.json[k] = JSON.stringify(JSON.parse(this.json[k]), null, 2);
          this.notify('JSON dirapihin ✅');
        } catch (e) {
          this.notify('JSON masih error 😅', true);
        }
        if (k === 'builder') {
          this.syncBuilderFromJson();
        }
      },

      validateJSON(tab) {
        if (tab === 'canvas') {
          this.notify('Canvas selalu menghasilkan JSON valid (selama tidak di-edit manual).');
          return;
        }
        const k = tab === 'form' ? 'form' : tab === 'builder' ? 'builder' : 'meta';
        try {
          JSON.parse(this.json[k]);
          this.notify('JSON valid ✅');
        } catch (e) {
          this.notify('JSON tidak valid ❌', true);
        }
      },

      // =============== IMPORT FROM SOP ===============
      bindSopSearch() {
        const select = this.$refs.sopSelect;
        if (!select) return;

        const filter = () => {
          const q = (this.sopSearch || '').toLowerCase().trim();
          [...select.options].forEach(opt => {
            if (!opt.value) return; // keep placeholder
            const text = (opt.dataset.text || opt.textContent || '').toLowerCase();
            opt.hidden = q && !text.includes(q);
          });
        };

        this.$watch('sopSearch', filter);
        filter();
      },

      sopBuilderToTemplate(builderSchema, sop) {
        // builderSchema dari SOP CREATE = array section; atau sudah dalam bentuk {page,blocks}
        if (builderSchema && builderSchema.page && Array.isArray(builderSchema.blocks)) {
          const obj = builderSchema;
          if (!obj.page.theme) obj.page.theme = '#05727d';
          if (!obj.page.title) obj.page.title = sop.title || sop.code || 'SOP';
          if (!obj.page.subtitle) {
            const parts = [];
            if (sop.department) parts.push(sop.department);
            const pl = [sop.product, sop.line].filter(Boolean).join(' / ');
            if (pl) parts.push(pl);
            obj.page.subtitle = parts.join(' / ');
          }
          if (typeof obj.page.show_logo !== 'boolean') obj.page.show_logo = true;
          if (typeof obj.page.show_page_number !== 'boolean') obj.page.show_page_number = true;

          // tambahkan blok foto kalau belum ada dan SOP punya foto
          const photos = Array.isArray(sop.photos) ? sop.photos : [];
          const hasPhotoBlock = (obj.blocks || []).some(b => b.type === 'photos');
          if (photos.length && !hasPhotoBlock) {
            obj.blocks.push({
              id: 'blk_photos_' + Date.now(),
              type: 'photos',
              title: 'FOTO PENDUKUNG',
              photos: photos.map((p, idx) => ({
                url: p.url || p.path || '',
                label: p.desc || ('Foto ' + (idx + 1)),
              })),
            });
          }

          return obj;
        }

        let sections = [];
        if (Array.isArray(builderSchema)) {
          sections = builderSchema;
        } else if (builderSchema && Array.isArray(builderSchema.sections)) {
          sections = builderSchema.sections;
        }

        const parts = [];
        if (sop.department) parts.push(sop.department);
        const prodLine = [sop.product, sop.line].filter(Boolean).join(' / ');
        if (prodLine) parts.push(prodLine);

        const blocks = [];

        // Judul dokumen
        blocks.push({
          id: 'blk_title',
          type: 'heading',
          level: 1,
          align: 'center',
          text: 'JUDUL SOP',
        });

        // Info dokumen
        blocks.push({
          id: 'blk_info',
          type: 'info_table',
          title: 'INFORMASI DOKUMEN',
          rows: [
            { label: 'Kode Dokumen',   value: '@{{ sop.code }}' },
            { label: 'Departemen',     value: '@{{ sop.department }}' },
            { label: 'Produk / Line',  value: '@{{ sop.product }} / @{{ sop.line }}' },
            { label: 'Revisi',         value: '1' },
          ],
        });

        // Section dari builder SOP
        if (sections.length) {
          sections.forEach((sec, idx) => {
            const secName = sec && sec.name ? sec.name : `Section ${idx + 1}`;
            const baseId = `sec_${Date.now()}_${idx}`;

            blocks.push({
              id: baseId + '_h',
              type: 'heading',
              level: 2,
              align: 'left',
              text: secName,
            });

            const items = Array.isArray(sec.items) ? sec.items : [];
            blocks.push({
              id: baseId + '_c',
              type: 'checklist',
              title: 'Checklist ' + secName,
              items: items.map(it => ({
                text: it && it.label ? it.label : '',
                note: '',
              })),
            });
          });
        }

        // Foto dari SOP → blok photos
        const photos = Array.isArray(sop.photos) ? sop.photos : [];
        if (photos.length) {
          blocks.push({
            id: 'blk_photos_' + Date.now(),
            type: 'photos',
            title: 'FOTO PENDUKUNG',
            photos: photos.map((p, idx) => ({
              url: p.url || p.path || '',
              label: p.desc || ('Foto ' + (idx + 1)),
            })),
          });
        }

        return {
          page: {
            theme: '#05727d',
            title: sop.title || sop.code || 'SOP',
            subtitle: parts.join(' / '),
            show_logo: true,
            show_page_number: true,
          },
          blocks,
        };
      },

      async importFromSop() {
        if (!this.importSopId) return;

        this.importLoading = true;
        this.importInfo = '';

        try {
          const url = `{{ url('/sop') }}/${this.importSopId}/json`;
          const res = await fetch(url, { headers: { 'Accept': 'application/json' } });

          if (!res.ok) throw new Error('SOP tidak ditemukan / tidak punya akses');

          const data = await res.json();

          // builder_schema dari SOP (sections) -> di-convert ke format canvas template
          const builderObj = this.sopBuilderToTemplate(data.builder_schema ?? [], data);

          // form_schema & builder_schema
          this.json.form    = JSON.stringify(data.form_schema ?? {}, null, 2);
          this.json.builder = JSON.stringify(builderObj, null, 2);

          // ambil semua atribut SOP dari backend (hasil getAttributes)
          const rawAttrs = data._attributes ?? {};

          // meta asli dari SOP (kalau ada)
          const baseMeta = data.meta ?? {};

          // gabungkan: meta SOP + _sop_attributes berisi FULL kolom SOP
          const mergedMeta = {
            ...baseMeta,
            _sop_attributes: rawAttrs,
          };

          this.json.meta = JSON.stringify(mergedMeta, null, 2);

          this.importInfo = `${data.code ?? '-'} • ${data.title ?? 'SOP'}`;

          // sinkron ke canvas
          this.syncBuilderFromJson();
          this.schemaTab = 'canvas';

          this.notify('Schema & atribut SOP berhasil di-import ✅');
        } catch (e) {
          this.notify(e.message || 'Gagal import SOP ❌', true);
        } finally {
          this.importLoading = false;
        }
      },

      clearImported() {
        this.importSopId = '';
        this.importInfo = '';
        this.notify('Pilihan SOP dibersihkan');
      },

      notify(msg, danger = false) {
        this.toast = {
          show: true,
          msg,
          danger
        };
        clearTimeout(this._t);
        this._t = setTimeout(() => this.toast.show = false, 1600);
      }
    }
  }
</script>
@endsection
