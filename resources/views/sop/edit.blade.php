@extends('layouts.app')
@section('title', 'Edit SOP (Builder)')

@section('content')
@php
    use Illuminate\Support\Str;
    $user = auth()->user();

    // ==========================================
    // 1. SETUP URL HELPER (VPS SAFE)
    // ==========================================
    $base = rtrim(config('app.url') ?: url('/'), '/');
    $host = request()->getHost();

    $generateUrl = function ($path) use ($base, $host) {
        if (!$path) return null;
        
        // Kalau sudah URL full, return
        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }
        
        // Bersihkan path
        $clean = preg_replace('#^(public/|storage/app/public/|storage/)+#', '', ltrim($path, '/'));
        
        // Logika Hostinger/VPS
        if (Str::contains($host, 'hostingersite.com')) {
            return $base . '/storage/app/public/' . $clean;
        } else {
            return $base . '/storage/' . $clean;
        }
    };

    // 2. DATA DB
    $dbBuilder = $sop->builder_schema ?? [];
    if (is_string($dbBuilder)) $dbBuilder = json_decode($dbBuilder, true) ?: [];

    $dbMeta = $sop->meta ?? [];
    if (is_string($dbMeta)) $dbMeta = json_decode($dbMeta, true) ?: [];
    $dbExtra = $dbMeta['extra_fields'] ?? [];
    $dbFormValues = $dbMeta['form_values'] ?? [];

    // 3. DATA OLD INPUT
    $oldBuilder = old('builder_schema') ? json_decode(old('builder_schema'), true) : null;
    $oldExtra   = old('extra_fields') ? json_decode(old('extra_fields'), true) : null;
    $oldForm    = old('form_values');

    // 4. INIT DATA
    $initBuilder = $oldBuilder ?? $dbBuilder;
    if (empty($initBuilder)) {
        $initBuilder = [[
            'id' => (string)Str::uuid(),
            'name' => 'Section 1',
            'items' => [['id' => (string)Str::uuid(), 'label' => '', 'type' => 'checkbox', 'required' => true]]
        ]];
    }

    $initExtra = $oldExtra ?? $dbExtra;
    if (empty($initExtra)) {
        $initExtra = [['id' => (string)Str::uuid(), 'label' => '', 'value' => '']];
    } else {
        foreach($initExtra as $k => $v) {
            if(!isset($v['id'])) $initExtra[$k]['id'] = (string)Str::uuid();
        }
    }

    // 5. PHOTOS SOP (Menggunakan Helper URL)
    $rawPhotos = $sop->photos ?? [];
    if (is_string($rawPhotos)) $rawPhotos = json_decode($rawPhotos, true) ?: [];
    $initPhotos = [];
    foreach ($rawPhotos as $p) {
        $path = $p['path'] ?? ($p['url'] ?? null);
        if (!$path) continue;
        $initPhotos[] = [
            'id' => (string)Str::uuid(),
            'name' => $path,
            'preview' => $generateUrl($path), // Pakai Helper
            '__path' => $path,
            'desc' => $p['desc'] ?? ''
        ];
    }

    // ==========================================
    // 6. RAW MATERIALS (LOGIC SAMAIN SEPERTI FOTO SOP)
    // ==========================================
    $dbMaterials = $sop->rawMaterials; 
    $oldMaterials = old('raw_materials'); 

    $initRawMaterials = [];

    if ($oldMaterials && is_array($oldMaterials)) {
        // KASUS: Error Validasi
        foreach ($oldMaterials as $index => $oldItem) {
            $originalId = $oldItem['id'] ?? null;
            $originalRec = $originalId ? $dbMaterials->find($originalId) : null;
            
            // Ambil path lama jika user tidak upload file baru (biar preview tetap muncul)
            $existingPath = $originalRec ? $originalRec->image_path : null;
            $existingUrl  = $existingPath ? $generateUrl($existingPath) : ($originalRec->image_url ?? null);

            $initRawMaterials[] = [
                'id' => $originalId, 
                'alpine_id' => (string)Str::uuid(),
                'name' => $oldItem['name'] ?? '',
                'amount' => $oldItem['amount'] ?? '',
                'unit' => $oldItem['unit'] ?? 'kg',
                'notes' => $oldItem['notes'] ?? '',
                'image_url' => $existingUrl, // URL Aman VPS
                'image_path' => $existingPath,
                'new_image_preview' => null, 
                'is_deleted' => false
            ];
        }
    } else {
        // KASUS: Load Normal dari DB
        foreach ($dbMaterials as $rm) {
            $existingUrl = $rm->image_path ? $generateUrl($rm->image_path) : ($rm->image_url ?? null);

            $initRawMaterials[] = [
                'id' => $rm->id,
                'alpine_id' => (string)Str::uuid(),
                'name' => $rm->name,
                'amount' => $rm->amount,
                'unit' => $rm->unit,
                'notes' => $rm->notes,
                'image_url' => $existingUrl, // URL Aman VPS
                'image_path' => $rm->image_path,
                'new_image_preview' => null,
                'is_deleted' => false
            ];
        }
    }
    
    if (empty($initRawMaterials)) {
        $initRawMaterials[] = [
            'id' => null,
            'alpine_id' => (string)Str::uuid(),
            'name' => '', 'amount' => '', 'unit' => 'kg', 'notes' => '',
            'image_url' => null, 'image_path' => null, 'new_image_preview' => null,
            'is_deleted' => false
        ];
    }

    $fv = function($key) use ($oldForm, $dbFormValues) {
        return $oldForm[$key] ?? ($dbFormValues[$key] ?? '');
    };
@endphp

<div x-data="{
    showFields: {
        product: true,
        line: true,
        effective_from: true,
        effective_to: {{ $sop->effective_to ? 'true' : 'false' }},
        content: true,
        is_public: true,
        pin: true,
    },
    
    builderSections: {{ json_encode($initBuilder) }},
    extraFields: {{ json_encode($initExtra) }},
    photos: {{ json_encode($initPhotos) }}, 
    newPhotos: [], 
    
    rawMaterials: {{ json_encode($initRawMaterials) }},

    // --- BUILDER LOGIC ---
    addSection() {
        this.builderSections.push({
            id: Date.now(),
            name: 'Section ' + (this.builderSections.length + 1),
            items: [{ id: Date.now()+1, label: '', type: 'checkbox', required: true }]
        });
    },
    removeSection(index) {
        if (this.builderSections.length > 1) this.builderSections.splice(index, 1);
    },
    addItem(sIndex) {
        this.builderSections[sIndex].items.push({ id: Date.now(), label: '', type: 'checkbox', required: true });
    },
    removeItem(sIndex, iIndex) {
        if (this.builderSections[sIndex].items.length > 1) {
            this.builderSections[sIndex].items.splice(iIndex, 1);
        }
    },

    // --- EXTRA FIELDS ---
    addExtraField() {
        this.extraFields.push({ id: Date.now(), label: '', value: '' });
    },
    removeExtraField(index) {
        if (this.extraFields.length > 1) this.extraFields.splice(index, 1);
    },

    // --- FOTO UTAMA SOP ---
    addNewPhoto() {
        this.newPhotos.push({ id: Date.now(), name: '', preview: null });
    },
    removeNewPhoto(index) {
        this.newPhotos.splice(index, 1);
    },
    handleNewPhotoChange(e, index) {
        const file = e.target.files[0];
        if(file) {
            this.newPhotos[index].name = file.name;
            this.newPhotos[index].preview = URL.createObjectURL(file);
        }
    },

    // --- RAW MATERIALS ---
    addRawMaterial() {
        this.rawMaterials.push({
            id: null, 
            alpine_id: Date.now(),
            name: '', amount: '', unit: 'kg', notes: '',
            image_url: null, image_path: null, new_image_preview: null,
            is_deleted: false
        });
    },
    deleteRawMaterial(index) {
        this.rawMaterials[index].is_deleted = true;
    },
    handleRawMatImage(e, index) {
        const file = e.target.files[0];
        if(file) {
            this.rawMaterials[index].new_image_preview = URL.createObjectURL(file);
        }
    },

    syncBeforeSubmit() {
        this.$refs.builderSchemaField.value = JSON.stringify(this.builderSections);
        this.$refs.extraFieldsField.value = JSON.stringify(this.extraFields);
    }
}" class="bg-white rounded-2xl border border-[#05727d]/20 shadow-sm overflow-hidden">

    {{-- HEADER --}}
    <div class="bg-gradient-to-r from-[#05727d] to-[#05727d] text-white px-5 md:px-6 py-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 text-[11px] text-white/60 mb-1">
                    <a href="{{ route('sop.index') }}" class="hover:text-white">SOP Management</a>
                    <span>/</span>
                    <span class="font-medium text-white">Edit (Builder)</span>
                </div>
                <h2 class="text-base font-semibold leading-tight">Edit SOP: {{ $sop->code }}</h2>
                <p class="text-xs text-white/80 mt-1">
                    Revisi SOP existing. Versi saat ini: v{{ $sop->version }}
                </p>
            </div>
            <div class="text-right text-xs">
                <div class="text-white/70 mb-1">User</div>
                <div class="font-semibold">{{ $user->name }}</div>
            </div>
        </div>
    </div>

    <div class="px-5 md:px-6 py-5 space-y-4">
        {{-- GLOBAL ERRORS --}}
        @if ($errors->any())
            <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-xs">
                <div class="font-semibold mb-1">Mohon perbaiki kesalahan berikut:</div>
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('sop.update', $sop) }}" enctype="multipart/form-data" 
              x-on:submit.prevent="syncBeforeSubmit(); $el.submit()">
            @csrf
            @method('PUT')

            <input type="hidden" name="builder_schema" x-ref="builderSchemaField">
            <input type="hidden" name="extra_fields" x-ref="extraFieldsField">

            {{-- 1. INFORMASI UTAMA --}}
            <div class="bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl p-4 mb-4">
                <div class="text-xs font-semibold text-[#05727d] mb-3 flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-[#05727d]"></span> Informasi Utama
                </div>
                
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Kode SOP <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $sop->code) }}" 
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-[#05727d]" required>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Judul SOP <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $sop->title) }}" 
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-[#05727d]" required>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Departemen <span class="text-rose-500">*</span></label>
                        <input type="text" name="department" value="{{ old('department', $sop->department) }}" 
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-[#05727d]" required>
                    </div>
                    <div x-show="showFields.product">
                        <label class="block text-xs text-slate-600 mb-1">Produk</label>
                        <input type="text" name="product" value="{{ old('product', $sop->product) }}" 
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-[#05727d]">
                    </div>
                    
                    {{-- Dynamic Form Values --}}
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Nama Lot</label>
                        <input type="text" name="form_values[lot_name]" value="{{ $fv('lot_name') }}"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-[#05727d]">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Nama Operator</label>
                        <input type="text" name="form_values[operator_name]" value="{{ $fv('operator_name') }}"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-[#05727d]">
                    </div>

                    {{-- Dates --}}
                    <div x-show="showFields.effective_from">
                        <label class="block text-xs text-slate-600 mb-1">Efektif Dari</label>
                        <input type="date" name="effective_from" value="{{ old('effective_from', optional($sop->effective_from)->toDateString()) }}"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-[#05727d]">
                    </div>
                    <div x-show="showFields.effective_to">
                        <label class="block text-xs text-slate-600 mb-1">Efektif Sampai</label>
                        <input type="date" name="effective_to" value="{{ old('effective_to', optional($sop->effective_to)->toDateString()) }}"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-[#05727d]">
                    </div>

                    {{-- Checkboxes --}}
                    <div class="md:col-span-2 flex gap-4 mt-2">
                        <label class="inline-flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                            <input type="checkbox" name="is_public" value="1" {{ old('is_public', $sop->is_public) ? 'checked' : '' }}
                                   class="h-4 w-4 rounded border-slate-300 text-[#05727d] focus:ring-[#05727d]">
                            <span>Publik (QR tanpa Login)</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- 2. RAW MATERIALS 🧱 (EDIT MODE - VPS SAFE) --}}
            <div class="bg-white border border-[#05727d]/20 rounded-xl p-4 mb-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs font-semibold text-[#05727d] flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-[#05727d]"></span> Daftar Raw Materials
                    </div>
                    <button type="button" @click="addRawMaterial()" 
                            class="px-3 py-1.5 rounded-lg border border-dashed border-slate-300 text-[11px] text-slate-700 hover:bg-slate-50">
                        + Tambah Material
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(mat, idx) in rawMaterials" :key="mat.alpine_id">
                        <div x-show="!mat.is_deleted" class="border border-slate-200 rounded-lg p-3 bg-slate-50/40 relative">
                            <template x-if="!mat.is_deleted">
                                <div>
                                    <input type="hidden" :name="'raw_materials['+idx+'][id]'" :value="mat.id">
                                    
                                    <div class="grid md:grid-cols-12 gap-3 text-xs">
                                        <div class="md:col-span-4">
                                            <label class="block text-[10px] text-slate-500 mb-0.5">Nama</label>
                                            <input type="text" :name="'raw_materials['+idx+'][name]'" x-model="mat.name" 
                                                   class="w-full rounded border-slate-200 px-2 py-1.5 text-xs" required placeholder="Nama Material">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] text-slate-500 mb-0.5">Jml</label>
                                            <input type="number" step="any" :name="'raw_materials['+idx+'][amount]'" x-model="mat.amount" 
                                                   class="w-full rounded border-slate-200 px-2 py-1.5 text-xs">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] text-slate-500 mb-0.5">Unit</label>
                                            <input type="text" :name="'raw_materials['+idx+'][unit]'" x-model="mat.unit" 
                                                   class="w-full rounded border-slate-200 px-2 py-1.5 text-xs">
                                        </div>
                                        <div class="md:col-span-4">
                                            <label class="block text-[10px] text-slate-500 mb-0.5">Ket</label>
                                            <input type="text" :name="'raw_materials['+idx+'][notes]'" x-model="mat.notes" 
                                                   class="w-full rounded border-slate-200 px-2 py-1.5 text-xs">
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between mt-3 pt-2 border-t border-slate-200/60">
                                        <div class="flex items-center gap-3">
                                            {{-- Preview Image --}}
                                            <div class="h-10 w-10 rounded bg-white border border-slate-200 overflow-hidden flex-shrink-0">
                                                {{-- Preview Foto Baru (Upload) --}}
                                                <template x-if="mat.new_image_preview">
                                                    <img :src="mat.new_image_preview" class="h-full w-full object-cover">
                                                </template>
                                                {{-- Preview Foto Lama (DB - VPS Safe) --}}
                                                <template x-if="!mat.new_image_preview && mat.image_url">
                                                    <img :src="mat.image_url" class="h-full w-full object-cover">
                                                </template>
                                                {{-- Placeholder --}}
                                                <template x-if="!mat.new_image_preview && !mat.image_url">
                                                    <div class="h-full w-full grid place-items-center text-slate-300">
                                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 16l4-4a3 3 0 014 0l4 4M2 20h20M2 12l5-5a3 3 0 014 0l3 3m7-7v8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    </div>
                                                </template>
                                            </div>
                                            
                                            <label class="cursor-pointer text-[11px] text-slate-600 hover:text-[#05727d]">
                                                <span x-text="mat.image_url ? 'Ganti Foto' : 'Upload Foto'"></span>
                                                <input type="file" :name="'raw_materials['+idx+'][image]'" class="hidden" 
                                                       accept="image/*" @change="handleRawMatImage($event, idx)">
                                            </label>
                                        </div>

                                        <button type="button" @click="deleteRawMaterial(idx)" class="text-[11px] text-rose-500 hover:underline">
                                            Hapus Baris
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            {{-- 3. BUILDER SECTION --}}
            <div class="bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl p-4 mb-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs font-semibold text-[#05727d]">SOP Builder</div>
                    <button type="button" @click="addSection()" class="text-[11px] bg-[#05727d] text-white px-2 py-1 rounded">
                        + Section
                    </button>
                </div>
                <template x-for="(section, sIdx) in builderSections" :key="section.id">
                    <div class="bg-white border border-slate-200 rounded-lg p-3 mb-3">
                        <div class="flex gap-2 mb-2">
                            <input type="text" x-model="section.name" class="flex-1 text-xs border p-1 rounded font-semibold" placeholder="Nama Section">
                            <button type="button" @click="removeSection(sIdx)" class="text-rose-500 text-[10px]" x-show="builderSections.length > 1">Hapus</button>
                        </div>
                        <div class="pl-2 border-l-2 border-slate-100 space-y-2">
                            <template x-for="(item, iIdx) in section.items" :key="item.id">
                                <div class="flex gap-2 items-center">
                                    <input type="text" x-model="item.label" class="flex-1 text-xs border p-1 rounded" placeholder="Item check...">
                                    <select x-model="item.type" class="text-[10px] border p-1 rounded">
                                        <option value="checkbox">Check</option>
                                        <option value="text">Teks</option>
                                        <option value="number">Angka</option>
                                    </select>
                                    <button type="button" @click="removeItem(sIdx, iIdx)" class="text-rose-400 text-[10px]">&times;</button>
                                </div>
                            </template>
                            <button type="button" @click="addItem(sIdx)" class="text-[10px] text-slate-500 mt-1">+ Item</button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- 4. EXTRA FIELDS --}}
            <div class="bg-white border border-[#05727d]/20 rounded-xl p-4 mb-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs font-semibold text-[#05727d]">Extra Fields</div>
                    <button type="button" @click="addExtraField()" class="text-[11px] border border-dashed border-slate-300 px-2 py-1 rounded">+ Field</button>
                </div>
                <div class="space-y-2">
                    <template x-for="(ex, idx) in extraFields" :key="ex.id">
                        <div class="flex gap-2">
                            <input type="text" x-model="ex.label" class="w-1/3 text-xs border p-1 rounded" placeholder="Label">
                            <input type="text" x-model="ex.value" class="w-2/3 text-xs border p-1 rounded" placeholder="Value">
                            <button type="button" @click="removeExtraField(idx)" class="text-rose-500 text-[10px]" x-show="extraFields.length > 1">&times;</button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- 5. FOTO SOP (LAMA & BARU) --}}
            <div class="bg-white border border-[#05727d]/20 rounded-xl p-4 mb-4">
                <div class="text-xs font-semibold text-[#05727d] mb-3">Foto SOP</div>
                
                {{-- Foto Lama --}}
                @if(count($initPhotos))
                    <div class="mb-3">
                        <div class="text-[10px] text-slate-500 mb-2">Foto Lama (Centang untuk hapus):</div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            @foreach($initPhotos as $p)
                                <label class="border p-2 rounded cursor-pointer hover:bg-rose-50">
                                    <div class="h-20 bg-slate-100 mb-1"><img src="{{ $p['preview'] }}" class="h-full w-full object-cover"></div>
                                    <div class="flex items-center gap-2 text-[10px]">
                                        <input type="checkbox" name="remove_photos[]" value="{{ $p['__path'] }}" class="text-rose-500">
                                        <span>Hapus</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Foto Baru --}}
                <div class="space-y-2">
                    <template x-for="(ph, idx) in newPhotos" :key="ph.id">
                        <div class="flex gap-2 items-center text-xs border p-2 rounded bg-slate-50">
                            <div class="h-10 w-10 bg-white border"><img :src="ph.preview" class="h-full w-full object-cover" x-show="ph.preview"></div>
                            <input type="file" name="photos[]" accept="image/*" @change="handleNewPhotoChange($event, idx)" class="text-[10px]">
                            <input type="text" name="photo_desc[]" placeholder="Deskripsi..." class="border p-1 rounded flex-1">
                            <button type="button" @click="removeNewPhoto(idx)" class="text-rose-500">Hapus</button>
                        </div>
                    </template>
                    <button type="button" @click="addNewPhoto()" class="text-[11px] border border-dashed border-slate-300 px-3 py-1 rounded w-full">+ Upload Foto Baru</button>
                </div>
            </div>

            {{-- 6. CONTENT --}}
            <div class="bg-white border border-[#05727d]/20 rounded-xl p-4 mb-4" x-show="showFields.content">
                <div class="text-xs font-semibold text-[#05727d] mb-2">Isi SOP</div>
                <textarea name="content" rows="6" class="w-full border border-slate-200 rounded p-2 text-sm">{{ old('content', $sop->content) }}</textarea>
            </div>

            {{-- FOOTER --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('sop.show', $sop) }}" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 rounded-lg">Batal</a>
                <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-[#05727d] rounded-lg shadow-sm hover:bg-[#05727d]/90">Simpan Perubahan</button>
            </div>

        </form>
    </div>
</div>
@endsection