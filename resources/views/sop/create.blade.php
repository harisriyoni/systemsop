@extends('layouts.app')
@section('title', 'Create SOP (Builder)')

@section('content')

@php
    $user = auth()->user();

    // ----- Prefill helpers (safe for create & edit) -----
    $sopMeta = [];
    if (isset($sop) && !empty($sop->meta)) {
        $sopMeta = is_array($sop->meta) ? $sop->meta : (json_decode($sop->meta, true) ?: []);
    }

    $tplMeta = [];
    if (isset($selectedTemplate) && !empty($selectedTemplate->meta)) {
        $tplMeta = is_array($selectedTemplate->meta) ? $selectedTemplate->meta : (json_decode($selectedTemplate->meta, true) ?: []);
    }

    $oldFormValues = old('form_values', []);
    $fv = function($key, $default = '') use ($oldFormValues, $sopMeta, $tplMeta) {
        if (is_array($oldFormValues) && array_key_exists($key, $oldFormValues)) return $oldFormValues[$key];
        if (!empty($sopMeta['form_values'][$key] ?? null)) return $sopMeta['form_values'][$key];
        if (!empty($tplMeta['form_values'][$key] ?? null)) return $tplMeta['form_values'][$key];
        return $default;
    };

    // ----- Init JSON for Alpine -----
    
    $initBuilderRaw = old('builder_schema')
        ?? ($selectedTemplate->builder_schema ?? ($sop->builder_schema ?? null));

    if (is_string($initBuilderRaw) && $initBuilderRaw !== '') {
        $initBuilderArr = json_decode($initBuilderRaw, true) ?: [];
    } elseif (is_array($initBuilderRaw)) {
        $initBuilderArr = $initBuilderRaw;
    } else {
        $initBuilderArr = [];
    }

    $initExtraRaw = old('extra_fields')
        ?? ($tplMeta['extra_fields'] ?? ($sopMeta['extra_fields'] ?? null));

    if (is_string($initExtraRaw) && $initExtraRaw !== '') {
        $initExtraArr = json_decode($initExtraRaw, true) ?: [];
    } elseif (is_array($initExtraRaw)) {
        $initExtraArr = $initExtraRaw;
    } else {
        $initExtraArr = [];
    }

    $initPhotosRaw = $sop->photos ?? null;
    if (is_string($initPhotosRaw) && $initPhotosRaw !== '') {
        $initPhotosArr = json_decode($initPhotosRaw, true) ?: [];
    } elseif (is_array($initPhotosRaw)) {
        $initPhotosArr = $initPhotosRaw;
    } else {
        $initPhotosArr = [];
    }
    
    // RAW MATERIALS: ambil dari old input, atau array kosong jika create
    $initRawMaterialsArr = old('raw_materials', []);
    if (!is_array($initRawMaterialsArr)) $initRawMaterialsArr = [];


    // safe JSON encoding for inlining into x-init
    $initBuilderJson = json_encode($initBuilderArr, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT);
    $initExtraJson   = json_encode($initExtraArr,  JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT);
    $initPhotosJson  = json_encode($initPhotosArr, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT);
    $initRawMaterialsJson = json_encode($initRawMaterialsArr, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT);
@endphp

<div
    x-data="{
        // ---------- OPSI FIELD OPSIONAL ----------
        showFields: {
            product: true,
            line: true,
            effective_from: true,
            effective_to: false,
            content: true,
            is_public: true,
            pin: true,
        },

        toggleField(key) {
            this.showFields[key] = !this.showFields[key];
        },

        // ---------- SOP BUILDER (STRUKTUR / CHECK SHEET) ----------
        builderSections: [],

        addSection() {
            this.builderSections.push({
                id: Date.now() + Math.random(),
                name: 'Section ' + (this.builderSections.length + 1),
                items: [
                    { id: Date.now() + Math.random(), label: '', type: 'checkbox', required: true },
                ],
            });
        },

        removeSection(index) {
            if (this.builderSections.length === 1) return;
            this.builderSections.splice(index, 1);
        },

        addItem(sIndex) {
            this.builderSections[sIndex].items.push({
                id: Date.now() + Math.random(),
                label: '',
                type: 'checkbox',
                required: true,
            });
        },

        removeItem(sIndex, iIndex) {
            const items = this.builderSections[sIndex].items;
            if (items.length === 1) return;
            items.splice(iIndex, 1);
        },

        // ---------- INFORMASI TAMBAHAN (DINAMIS) ----------
        extraFields: [
            { id: Date.now() + 1000, label: '', value: '' },
        ],

        addExtraField() {
            this.extraFields.push({
                id: Date.now() + Math.random(),
                label: '',
                value: '',
            });
        },

        removeExtraField(index) {
            if (this.extraFields.length === 1) return;
            this.extraFields.splice(index, 1);
        },

        // ---------- FOTO UTAMA SOP ----------
        photos: [{ id: Date.now(), name: '', preview: null }],

        addPhoto() {
            this.photos.push({ id: Date.now() + Math.random(), name: '', preview: null });
        },

        removePhoto(i) {
            if (this.photos.length === 1) return;
            if (this.photos[i].preview) URL.revokeObjectURL(this.photos[i].preview);
            this.photos.splice(i, 1);
        },

        handlePhotoChange(e, index) {
            const file = e.target.files?.[0];
            if (!file) return;

            if (this.photos[index].preview) {
                URL.revokeObjectURL(this.photos[index].preview);
            }

            this.photos[index].name = file.name;
            this.photos[index].preview = URL.createObjectURL(file);
        },
        
        // ---------- RAW MATERIALS (INTEGRASI BARU) ----------
        rawMaterials: [],
        
        addRawMaterial() {
            this.rawMaterials.push({ 
                id: Date.now() + Math.random(), 
                name: '', 
                amount: '', 
                unit: 'kg', 
                notes: '',
                image: null,
                image_preview: null,
            });
        },
        
        removeRawMaterial(index) {
            this.rawMaterials.splice(index, 1);
        },
        
        handleRawMaterialImageChange(e, index) {
            const file = e.target.files?.[0];
            if (!file) return;

            if (this.rawMaterials[index].image_preview) {
                URL.revokeObjectURL(this.rawMaterials[index].image_preview);
            }

            this.rawMaterials[index].image = file;
            this.rawMaterials[index].image_preview = URL.createObjectURL(file);
        },

        // ---------- INIT + SYNC ----------
        init() {
            if (!this.builderSections.length) {
                this.builderSections = [
                    {
                        id: Date.now(),
                        name: 'Section 1',
                        items: [
                            { id: Date.now() + 1, label: '', type: 'checkbox', required: true },
                        ],
                    },
                ];
            }
            
            // Init Raw Materials with one empty row if empty
            if (!this.rawMaterials.length) {
                this.addRawMaterial();
            }
        },

        syncBeforeSubmit() {
            if (this.$refs.builderSchemaField) {
                this.$refs.builderSchemaField.value = JSON.stringify(this.builderSections);
            }
            if (this.$refs.extraFieldsField) {
                this.$refs.extraFieldsField.value = JSON.stringify(this.extraFields);
            }
        },
    }"
    x-init="
        // init builderSections / extraFields / photos from server (if available)
        (() => {
            try {
                const b = {{ $initBuilderJson ?: 'null' }};
                const e = {{ $initExtraJson ?: 'null' }};
                const p = {{ $initPhotosJson ?: 'null' }};
                const r = {{ $initRawMaterialsJson ?: 'null' }};

                if (Array.isArray(b) && b.length) this.builderSections = b;
                if (Array.isArray(e) && e.length) {
                    this.extraFields = e.map((r) => ({ id: Date.now() + Math.random(), label: r.label ?? '', value: r.value ?? '' }));
                }
                if (Array.isArray(p) && p.length) {
                    this.photos = p.map((pp) => ({ id: Date.now() + Math.random(), name: pp.path ?? pp.url ?? '', preview: null, __path: pp.path ?? pp.url ?? '' }));
                }
                if (Array.isArray(r) && r.length) {
                    // Raw Materials dari old input (jika validasi gagal)
                    this.rawMaterials = r.map((rm) => ({ 
                        id: rm.id || Date.now() + Math.random(),
                        name: rm.name ?? '',
                        amount: rm.amount ?? '',
                        unit: rm.unit ?? 'kg',
                        notes: rm.notes ?? '',
                        image: null, 
                        image_preview: null,
                    }));
                }
            } catch (err) {
                console.warn('init parse failed', err);
            }
        })()
    "
    class="bg-white rounded-2xl border border-[#05727d]/20 shadow-sm overflow-hidden"
>

    {{-- HEADER --}}
    <div class="bg-gradient-to-r from-[#05727d] to-[#05727d] text-white px-5 md:px-6 py-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 text-[11px] text-white/60 mb-1">
                    <a href="{{ route('sop.index') }}" class="hover:text-white">SOP Management</a>
                    <span>/</span>
                    <span class="font-medium text-white">Create (Builder)</span>
                </div>
                <h2 class="text-base font-semibold leading-tight">Buat SOP Baru dengan Builder</h2>
                <p class="text-xs text-white/80 mt-1">
                    Susun informasi utama, struktur SOP, dan field tambahan. Setelah disimpan, SOP masuk flow approval seperti biasa.
                </p>
            </div>

            <div class="text-right text-xs">
                <div class="text-white/70 mb-1">User</div>
                <div class="font-semibold">{{ $user?->name ?? '-' }}</div>
            </div>
        </div>
    </div>

    {{-- BODY --}}
    <div class="px-5 md:px-6 py-5 space-y-4">

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

        <form id="sopForm"
              method="POST"
              action="{{ route('sop.store') }}"
              enctype="multipart/form-data"
              x-on:submit.prevent="syncBeforeSubmit(); $el.submit()"
              class="space-y-5">

            @csrf

            {{-- HIDDEN: STRUCTURE & EXTRA FIELDS --}}
            <input type="hidden" name="builder_schema" x-ref="builderSchemaField">
            <input type="hidden" name="extra_fields" x-ref="extraFieldsField">

            {{-- =========================
                SECTION: INFORMASI UTAMA
            ========================== --}}
            <div class="bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs font-semibold text-[#05727d] flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-[#05727d]"></span>
                        Informasi Utama SOP
                    </div>

                    {{-- Pengaturan field opsional --}}
                    <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                        <span>Pengaturan Field:</span>

                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" class="rounded border-slate-300"
                                    x-model="showFields.product">
                            <span>Produk</span>
                        </label>

                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" class="rounded border-slate-300"
                                    x-model="showFields.line">
                            <span>Line Produksi</span>
                        </label>

                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" class="rounded border-slate-300"
                                    x-model="showFields.effective_from">
                            <span>Tanggal Berlaku Dari</span>
                        </label>

                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" class="rounded border-slate-300"
                                    x-model="showFields.effective_to">
                            <span>Tanggal Berlaku Sampai</span>
                        </label>

                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" class="rounded border-slate-300"
                                    x-model="showFields.is_public">
                            <span>Tersedia untuk Publik</span>
                        </label>

                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" class="rounded border-slate-300"
                                    x-model="showFields.pin">
                            <span>PIN Akses (Opsional)</span>
                        </label>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    {{-- Kode SOP --}}
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">
                            Kode SOP <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="code" value="{{ old('code') }}"
                            class="w-full rounded-lg border px-3 py-2 outline-none
                                    {{ $errors->has('code')
                                        ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500'
                                        : 'border-slate-200 focus:ring-[#05727d]/15 focus:border-[#05727d]' }}"
                            placeholder="Contoh: SOP-PRD-001" required>
                        @error('code') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
                    </div>

                    {{-- Judul SOP --}}
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">
                            Judul SOP <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}"
                            class="w-full rounded-lg border px-3 py-2 outline-none
                                    {{ $errors->has('title')
                                        ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500'
                                        : 'border-slate-200 focus:ring-[#05727d]/15 focus:border-[#05727d]' }}"
                            placeholder="Contoh: Prosedur Pengecekan Area Produksi" required>
                        @error('title') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
                    </div>

                    {{-- Departemen --}}
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">
                            Departemen <span class="text-rose-500">*</span>
                        </label>
                        <select name="department"
                            class="w-full rounded-lg border px-3 py-2 outline-none
                                    {{ $errors->has('department')
                                        ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500'
                                        : 'border-slate-200 focus:ring-[#05727d]/15 focus:border-[#05727d]' }}"
                            required>
                            <option value="">-- Pilih --</option>
                            <option value="Produksi" {{ old('department') === 'Produksi' ? 'selected' : '' }}>Produksi</option>
                            <option value="QA" {{ old('department') === 'QA' ? 'selected' : '' }}>QA</option>
                            <option value="Logistik" {{ old('department') === 'Logistik' ? 'selected' : '' }}>Logistik</option>
                            <option value="Lainnya" {{ old('department') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('department') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
                    </div>

                    {{-- Produk --}}
                    <div x-show="showFields.product">
                        <label class="block text-xs text-slate-600 mb-1">Produk</label>
                        <input type="text" name="product" value="{{ old('product') }}"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none
                                    focus:ring-[#05727d]/15 focus:border-[#05727d]"
                            placeholder="Contoh: Chemical A / Produk X">
                        @error('product') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
                    </div>

                    {{-- Nama Lot (dinamis) --}}
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Nama Lot</label>
                        <input type="text" name="form_values[lot_name]"
                                value="{{ $fv('lot_name', '') }}"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none focus:ring-[#05727d]/15 focus:border-[#05727d]"
                                placeholder="Contoh: LOT-2025-001">
                        @if($errors->has('form_values.lot_name'))
                            <div class="text-[11px] text-rose-600 mt-1">{{ $errors->first('form_values.lot_name') }}</div>
                        @endif
                    </div>

                    {{-- Nama Operator (dinamis) --}}
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Nama Operator</label>
                        <input type="text" name="form_values[operator_name]"
                                value="{{ $fv('operator_name', '') }}"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none focus:ring-[#05727d]/15 focus:border-[#05727d]"
                                placeholder="Contoh: Budi Santoso">
                        @if($errors->has('form_values.operator_name'))
                            <div class="text-[11px] text-rose-600 mt-1">{{ $errors->first('form_values.operator_name') }}</div>
                        @endif
                    </div>

                    {{-- Line Produksi --}}
                    <div x-show="showFields.line">
                        <label class="block text-xs text-slate-600 mb-1">Line Produksi</label>
                        <input type="text" name="line" value="{{ old('line') }}"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none
                                    focus:ring-[#05727d]/15 focus:border-[#05727d]"
                            placeholder="Contoh: Line A / VE-01">
                        @error('line') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
                    </div>

                    {{-- Tanggal Berlaku Dari --}}
                    <div x-show="showFields.effective_from">
                        <label class="block text-xs text-slate-600 mb-1">Tanggal Berlaku Dari</label>
                        <input type="date" name="effective_from" value="{{ old('effective_from') }}"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none
                                    focus:ring-[#05727d]/15 focus:border-[#05727d]">
                        @error('effective_from') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
                    </div>

                    {{-- Tanggal Berlaku Sampai --}}
                    <div x-show="showFields.effective_to">
                        <label class="block text-xs text-slate-600 mb-1">Tanggal Berlaku Sampai</label>
                        <input type="date" name="effective_to" value="{{ old('effective_to') }}"
                            class="w-full rounded-lg border px-3 py-2 outline-none
                                    {{ $errors->has('effective_to')
                                        ? 'border-rose-300 focus:border-rose-400 focus:ring-rose-200'
                                        : 'border-slate-200 focus:border-[#05727d] focus:ring-[#05727d]/15' }}">
                        @error('effective_to') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
                    </div>

                    {{-- Tersedia untuk Publik --}}
                    <div class="md:col-span-2" x-show="showFields.is_public">
                        <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                            <input id="is_public" type="checkbox" name="is_public" value="1"
                                    class="h-4 w-4 rounded border-slate-300 text-[#05727d] focus:ring-[#05727d]"
                                    {{ old('is_public') ? 'checked' : '' }}>
                            <span>Tersedia untuk publik (QR tanpa login)</span>
                        </label>
                        @error('is_public') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
                    </div>

                    {{-- PIN Akses --}}
                    <div x-show="showFields.pin">
                        <label class="block text-xs text-slate-600 mb-1">PIN Akses (Opsional)</label>
                        <input type="text" name="pin" value="{{ old('pin') }}"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none
                                    focus:ring-[#05727d]/15 focus:border-[#05727d]"
                            placeholder="Contoh: 1234">
                        @error('pin') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
            
            {{-- =========================
                SECTION: RAW MATERIALS 🧱 (INTEGRASI BARU)
            ========================== --}}
            <div class="bg-white border border-[#05727d]/20 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs font-semibold text-[#05727d] flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-[#05727d]"></span>
                        Daftar Raw Materials
                    </div>
                    <button type="button"
                            @click="addRawMaterial()"
                            class="px-3 py-1.5 rounded-lg border border-dashed border-slate-300 text-[11px] text-slate-700 hover:bg-slate-50">
                        + Tambah Material
                    </button>
                </div>
                
                <p class="text-[11px] text-slate-500 mb-3">
                    Input daftar bahan baku yang digunakan dalam SOP ini.
                </p>

                <div class="space-y-4">
                    <template x-for="(material, index) in rawMaterials" :key="material.id">
                        <div class="border border-slate-200 rounded-lg p-3 bg-slate-50/40">
                            
                            {{-- Input Fields --}}
                            <div class="grid md:grid-cols-12 gap-3 text-xs mb-3">
                                
                                {{-- Nama Material --}}
                                <div class="md:col-span-4">
                                    <label class="block text-xs text-slate-600 mb-1">Nama Material <span class="text-rose-500">*</span></label>
                                    <input type="text" :name="'raw_materials[' + index + '][name]'" x-model="material.name"
                                           class="w-full rounded-md border border-slate-200 px-2 py-1 text-xs"
                                           placeholder="Contoh: Phosphorous Acid">
                                    @if($errors->has('raw_materials.*.name'))
                                        <div class="text-[11px] text-rose-600 mt-1">Nama material wajib diisi.</div>
                                    @endif
                                </div>
                                
                                {{-- Jumlah --}}
                                <div class="md:col-span-2">
                                    <label class="block text-xs text-slate-600 mb-1">Jumlah</label>
                                    <input type="number" step="any" :name="'raw_materials[' + index + '][amount]'" x-model="material.amount"
                                           class="w-full rounded-md border border-slate-200 px-2 py-1 text-xs"
                                           placeholder="49.86">
                                    @error('raw_materials.*.amount') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
                                </div>
                                
                                {{-- Satuan --}}
                                <div class="md:col-span-2">
                                    <label class="block text-xs text-slate-600 mb-1">Satuan</label>
                                    <input type="text" :name="'raw_materials[' + index + '][unit]'" x-model="material.unit"
                                           class="w-full rounded-md border border-slate-200 px-2 py-1 text-xs"
                                           placeholder="kg / liter">
                                    @error('raw_materials.*.unit') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
                                </div>
                                
                                {{-- Keterangan --}}
                                <div class="md:col-span-4">
                                    <label class="block text-xs text-slate-600 mb-1">Keterangan (Opsional)</label>
                                    <input type="text" :name="'raw_materials[' + index + '][notes]'" x-model="material.notes"
                                           class="w-full rounded-md border border-slate-200 px-2 py-1 text-xs"
                                           placeholder="Contoh: Caustic Soda Flake 98%">
                                    @error('raw_materials.*.notes') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
                                </div>
                                
                            </div>
                            
                            {{-- Image and Actions --}}
                            <div class="flex items-end justify-between gap-3 text-xs border-t border-slate-200 pt-3">
                                
                                {{-- Image Upload --}}
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-md bg-white border border-slate-200 overflow-hidden grid place-items-center shrink-0">
                                        <template x-if="material.image_preview">
                                            <img :src="material.image_preview" class="h-full w-full object-cover" alt="preview">
                                        </template>
                                        <template x-if="!material.image_preview">
                                            <svg class="w-5 h-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4-4a3 3 0 014 0l4 4M2 20h20M2 12l5-5a3 3 0 014 0l3 3m7-7v8"/></svg>
                                        </template>
                                    </div>
                                    
                                    <label class="w-full cursor-pointer">
                                        <span class="block text-slate-600 mb-1">Foto Material</span>
                                        <input type="file" :name="'raw_materials[' + index + '][image]'" accept="image/*"
                                            @change="handleRawMaterialImageChange($event, index)"
                                            class="block w-full text-[11px] text-slate-600
                                                    file:mr-2 file:py-1 file:px-2 file:rounded-md
                                                    file:border-0 file:text-[11px]
                                                    file:bg-[#05727d]/10 file:text-[#05727d]
                                                    hover:file:bg-[#05727d]/20">
                                    </label>
                                </div>
                                
                                {{-- Remove Button --}}
                                <button type="button"
                                        @click="removeRawMaterial(index)"
                                        class="text-[11px] text-rose-600 hover:underline px-2 py-1"
                                        x-show="rawMaterials.length > 1">
                                    Hapus Baris
                                </button>
                            </div>
                            
                            @error('raw_materials.*.image') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
                        </div>
                    </template>
                    
                </div>
            </div>

            {{-- =========================
                SECTION: SOP BUILDER – STRUKTUR & CHECK SHEET
            ========================== --}}
            <div class="bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs font-semibold text-[#05727d] flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-[#05727d]"></span>
                        SOP Builder – Struktur & Check Sheet
                    </div>
                    <button type="button"
                            @click="addSection()"
                            class="px-3 py-1.5 rounded-lg bg-[#05727d] hover:bg-[#05727d]/90 text-white text-[11px] font-semibold shadow-sm">
                        + Tambah Section
                    </button>
                </div>

                <p class="text-[11px] text-slate-500 mb-3">
                    Section = blok besar (Persiapan, Proses, Pembersihan, dst).
                    Item = baris yang nanti diisi operator di check sheet.
                </p>

                <template x-for="(section, sIndex) in builderSections" :key="section.id">
                    <div class="mb-4 border border-slate-200 rounded-lg p-3 bg-white">
                        <div class="flex items-center gap-2 mb-2">
                            <input type="text"
                                    x-model="section.name"
                                    class="flex-1 rounded-md border border-slate-200 px-2 py-1 text-xs"
                                    placeholder="Nama Section, contoh: Persiapan Area">

                            <button type="button"
                                    @click="removeSection(sIndex)"
                                    class="text-[11px] text-rose-600 hover:underline"
                                    x-show="builderSections.length > 1">
                                Hapus Section
                            </button>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(item, iIndex) in section.items" :key="item.id">
                                <div class="flex items-center gap-2">
                                    <input type="text"
                                            x-model="item.label"
                                            class="flex-1 rounded-md border border-slate-200 px-2 py-1 text-xs"
                                            placeholder="Isi item ceklis, contoh: Cek tekanan tanki VE-01">

                                    <select x-model="item.type"
                                            class="rounded-md border border-slate-200 px-2 py-1 text-xs">
                                        <option value="checkbox">Checklist (Ya/Tidak)</option>
                                        <option value="number">Angka</option>
                                        <option value="text">Teks</option>
                                    </select>

                                    <label class="inline-flex items-center gap-1 text-[11px] text-slate-600">
                                        <input type="checkbox" x-model="item.required" class="rounded border-slate-300">
                                        <span>Wajib</span>
                                    </label>

                                    <button type="button"
                                            @click="removeItem(sIndex, iIndex)"
                                            class="text-[11px] text-rose-600 hover:underline"
                                            x-show="section.items.length > 1">
                                        Hapus
                                    </button>
                                </div>
                            </template>

                            <button type="button"
                                    @click="addItem(sIndex)"
                                    class="mt-1 px-2 py-1 rounded-md border border-dashed border-slate-300 text-[11px] text-slate-600 hover:bg-slate-50">
                                + Tambah Item
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- =========================
                SECTION: INFORMASI TAMBAHAN (DINAMIS)
            ========================== --}}
            <div class="bg-white border border-[#05727d]/20 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs font-semibold text-[#05727d] flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-[#05727d]"></span>
                        Informasi Tambahan (Opsional)
                    </div>
                    <button type="button"
                            @click="addExtraField()"
                            class="px-3 py-1.5 rounded-lg border border-dashed border-slate-300 text-[11px] text-slate-700 hover:bg-slate-50">
                        + Tambah Field
                    </button>
                </div>

                <p class="text-[11px] text-slate-500 mb-3">
                    Gunakan ini untuk kebutuhan khusus tiap SOP (contoh: Nama Mesin, Area, Shift, Nomor Dokumen Lama, dsb).
                    Field ini tidak memaksa perubahan schema database karena disimpan di kolom <code>meta</code>.
                </p>

                <div class="space-y-2">
                    <template x-for="(f, idx) in extraFields" :key="f.id">
                        <div class="grid md:grid-cols-12 gap-2 items-center">
                            <div class="md:col-span-4">
                                <input type="text"
                                        x-model="f.label"
                                        class="w-full rounded-md border border-slate-200 px-2 py-1 text-xs"
                                        placeholder="Label, contoh: Nama Mesin">
                            </div>
                            <div class="md:col-span-7">
                                <input type="text"
                                        x-model="f.value"
                                        class="w-full rounded-md border border-slate-200 px-2 py-1 text-xs"
                                        placeholder="Nilai, contoh: Reactor 01">
                            </div>
                            <div class="md:col-span-1 text-right">
                                <button type="button"
                                        @click="removeExtraField(idx)"
                                        class="text-[11px] text-rose-500 hover:underline"
                                        x-show="extraFields.length > 1">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- =========================
                SECTION: FOTO & LAMPIRAN UTAMA
            ========================== --}}
            <div class="bg-white border border-[#05727d]/20 rounded-xl p-4">
                <div class="text-xs font-semibold text-[#05727d] mb-3 flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-[#05727d]"></span>
                    Foto & Lampiran Utama SOP
                </div>

                <div class="mb-2 text-[11px] text-slate-500">
                    Foto area kerja / layout untuk dilampirkan di SOP.
                </div>

                <div class="grid md:grid-cols-3 gap-3">
                    <template x-for="(photo, index) in photos" :key="photo.id">
                        <div class="border border-slate-200 rounded-lg p-2 flex flex-col gap-2 bg-slate-50/40">
                            <div class="text-[11px] text-slate-600 flex items-center justify-between">
                                <span>Foto <span x-text="index + 1"></span></span>
                                <button type="button"
                                        @click="removePhoto(index)"
                                        class="text-rose-500 hover:text-rose-600 text-[11px]"
                                        x-show="photos.length > 1">
                                    Hapus
                                </button>
                            </div>

                            <input
                                type="file"
                                :name="'photos[' + index + ']'"
                                accept="image/*"
                                @change="handlePhotoChange($event, index)"
                                class="block w-full text-[11px] text-slate-600
                                         file:mr-2 file:py-1 file:px-2 file:rounded-md
                                         file:border-0 file:text-[11px]
                                         file:bg-[#05727d]/10 file:text-[#05727d]
                                         hover:file:bg-[#05727d]/20">
                            @error('photos.*') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror

                            <input
                                type="text"
                                :name="'photo_desc[' + index + ']'"
                                class="w-full rounded-md border border-slate-200 px-2 py-1 text-[11px]"
                                placeholder="Deskripsi foto (opsional)">
                            @error('photo_desc.*') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror

                            <div
                                class="mt-1 rounded-md bg-slate-100 flex items-center justify-center overflow-hidden"
                                x-show="photo.preview">
                                <img :src="photo.preview" alt="" class="max-h-32 object-contain">
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-3">
                    <button type="button"
                            @click="addPhoto()"
                            class="px-3 py-1.5 rounded-lg border border-dashed border-slate-300 text-[11px] text-slate-700 hover:bg-slate-50">
                        + Tambah Foto
                    </button>
                </div>
            </div>

            {{-- =========================
                SECTION: ISI SOP (NARASI)
            ========================== --}}
            <div class="bg-white border border-[#05727d]/20 rounded-xl p-4" x-show="showFields.content">
                <div class="text-xs font-semibold text-[#05727d] mb-3 flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-[#05727d]"></span>
                    Isi SOP (Deskripsi Naratif)
                </div>

                <textarea name="content" rows="7"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none
                                    focus:ring-[#05727d]/15 focus:border-[#05727d]"
                            placeholder="Tuliskan isi SOP atau ringkasan langkah-langkahnya...">{{ old('content') }}</textarea>
                @error('content') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>

            {{-- BUTTONS --}}
            <div class="flex items-center justify-end gap-2 pt-1">
                <a href="{{ route('sop.index') }}"
                    class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold
                            hover:bg-slate-50">
                    Batal
                </a>

                <button type="submit"
                        class="px-5 py-2 rounded-lg bg-[#05727d] hover:bg-[#05727d]/90 text-white text-xs font-semibold shadow-sm">
                    Simpan SOP
                </button>
            </div>

        </form>
    </div>
</div>
@endsection