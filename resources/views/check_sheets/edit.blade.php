@extends('layouts.app')
@section('title', 'Edit Check Sheet')

@section('content')
@php
  // ----- Schema JSON lama (optional) -----
  $defaultSchema = old('form_schema') ?: (
      $checkSheet->form_schema ?? json_encode([
        "title"   => $checkSheet->title ?? "Check Sheet",
        "version" => (int)($checkSheet->version ?? 1),
        "fields"  => []
      ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)
  );

  // Status
  $st = old('status', $checkSheet->status);

  // ----- Fields (Checklist Builder) -----
  $defaultFields = old('fields', $checkSheet->fields ?? []);
  if (!is_array($defaultFields) || !count($defaultFields)) {
      $defaultFields = [
          ['label' => 'Contoh: Temperatur sesuai', 'key' => 'temperatur_ok'],
      ];
  }
@endphp

<div class="bg-slate-50">
  <div
    class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6"
    x-data="checkSheetEdit(@js($defaultSchema), @js($defaultFields))"
  >

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div class="space-y-1">
        <div class="flex items-center gap-3">
          <div class="h-10 w-1.5 rounded-full bg-[#05727d]"></div>
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">
              Edit Check Sheet
            </h1>
            <p class="text-sm text-slate-500">
              Ubah metadata & schema builder check sheet.
            </p>
          </div>
        </div>
      </div>

      <a href="{{ route('check_sheets.index') }}"
         class="px-3 py-2 rounded-xl bg-white ring-1 ring-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold transition">
        ← Kembali
      </a>
    </div>

    {{-- Form Card --}}
    <form
      method="POST"
      action="{{ route('check_sheets.update', $checkSheet->id) }}"
      class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden"
    >
      @csrf
      @method('PATCH')

      <div class="p-6 md:p-8 space-y-8">

        {{-- Informasi Utama --}}
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Informasi Utama</h2>
            <span class="text-xs px-2 py-1 rounded-full bg-[#05727d]/10 text-[#05727d] font-semibold">
              ID: {{ $checkSheet->id }}
            </span>
          </div>

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-semibold text-slate-700">Judul</label>
              <input name="title"
                     value="{{ old('title', $checkSheet->title) }}"
                     class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50 focus:bg-white focus:border-[#05727d] focus:ring-[#05727d] transition"
                     placeholder="Contoh Check Sheet Produksi">
              @error('title') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
              <label class="text-sm font-semibold text-slate-700">Department</label>
              <input name="department"
                     value="{{ old('department', $checkSheet->department) }}"
                     class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50 focus:bg-white focus:border-[#05727d] focus:ring-[#05727d] transition"
                     placeholder="Produksi / HSE / QC">
              @error('department') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>
          </div>

          <div class="grid md:grid-cols-3 gap-4">
            <div>
              <label class="text-sm font-semibold text-slate-700">Product</label>
              <input name="product"
                     value="{{ old('product', $checkSheet->product) }}"
                     class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50 focus:bg-white focus:border-[#05727d] focus:ring-[#05727d] transition"
                     placeholder="Opsional">
              @error('product') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
              <label class="text-sm font-semibold text-slate-700">Line</label>
              <input name="line"
                     value="{{ old('line', $checkSheet->line) }}"
                     class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50 focus:bg-white focus:border-[#05727d] focus:ring-[#05727d] transition"
                     placeholder="Opsional">
              @error('line') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
              <label class="text-sm font-semibold text-slate-700">Status Form</label>
              <select name="status"
                      class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50 focus:bg-white focus:border-[#05727d] focus:ring-[#05727d] transition">
                <option value="draft"    @selected($st==='draft')>Draft</option>
                <option value="active"   @selected($st==='active')>Active (Published)</option>
                <option value="inactive" @selected($st==='inactive')>Inactive</option>
                <option value="archived" @selected($st==='archived')>Archived</option>
              </select>
              @error('status') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>
          </div>

          <div>
            <label class="text-sm font-semibold text-slate-700">Deskripsi</label>
            <textarea name="description" rows="3"
                      class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50 focus:bg-white focus:border-[#05727d] focus:ring-[#05727d] transition"
                      placeholder="Opsional">{{ old('description', $checkSheet->description) }}</textarea>
            @error('description') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="border-t border-slate-100"></div>

        {{-- =================== CHECKLIST BUILDER =================== --}}
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-base font-bold text-slate-900">Checklist Builder</h2>
              <p class="text-xs text-slate-500 mt-0.5">
                Tambah/hapus item pengecekan. Disimpan di kolom <code>fields</code>.
              </p>
            </div>
            <button type="button"
                    @click="addRow()"
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#05727d] text-white text-xs font-semibold hover:bg-[#0894a0]">
              <span class="text-lg leading-none">+</span>
              Tambah Baris
            </button>
          </div>

          <div class="border border-slate-200 rounded-2xl overflow-hidden text-xs">
            <table class="min-w-full">
              <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide">
                <tr>
                  <th class="px-3 py-2 w-10 text-left">No</th>
                  <th class="px-3 py-2 text-left">Label Item</th>
                  <th class="px-3 py-2 text-left w-48">Key (Name)</th>
                  <th class="px-3 py-2 text-right w-12">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <template x-for="(row, idx) in fields" :key="idx">
                  <tr class="border-t border-slate-100 bg-white">
                    <td class="px-3 py-2 align-top text-slate-500" x-text="idx + 1"></td>

                    {{-- Label --}}
                    <td class="px-3 py-2 align-top">
                      <input type="text"
                             class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs outline-none
                                    focus:ring-[#b7e9ec] focus:border-[#05727d]"
                             :name="`fields[${idx}][label]`"
                             x-model="row.label"
                             placeholder="Contoh: Temperatur sesuai">
                    </td>

                    {{-- Key --}}
                    <td class="px-3 py-2 align-top">
                      <input type="text"
                             class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs outline-none
                                    focus:ring-[#b7e9ec] focus:border-[#05727d]"
                             :name="`fields[${idx}][key]`"
                             x-model="row.key"
                             @input="row.key = slugify(row.key)"
                             @blur="autoKey(idx)"
                             placeholder="contoh: temperatur_ok">
                      <div class="text-[10px] text-slate-400 mt-0.5">
                        Dipakai sebagai nama field di payload (<code>data[key]</code>).
                      </div>
                    </td>

                    {{-- Aksi --}}
                    <td class="px-3 py-2 align-top text-right">
                      <button type="button"
                              @click="removeRow(idx)"
                              class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100">
                        ✕
                      </button>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>

          @error('fields')
            <div class="text-xs text-rose-600 mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="border-t border-slate-100"></div>

        {{-- =================== SCHEMA BUILDER (JSON) =================== --}}
        <div class="space-y-3">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
              <h2 class="text-base font-bold text-slate-900">Schema Builder</h2>
              <p class="text-xs text-slate-500 mt-0.5">
                Format harus valid JSON.
              </p>
            </div>

            <div class="flex items-center gap-2">
              <button type="button" @click="formatSchema()"
                      class="px-3 py-2 rounded-xl bg-[#05727d]/10 hover:bg-[#05727d]/15 text-[#05727d] text-xs font-bold transition">
                Rapihin JSON
              </button>
              <button type="button" @click="validateSchema()"
                      class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                Cek Valid
              </button>
            </div>
          </div>

          <div class="rounded-2xl ring-1 ring-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-4 py-2 flex items-center justify-between">
              <div class="text-xs font-semibold text-slate-600">form_schema.json</div>
              <div class="text-xs"
                   :class="schemaValid ? 'text-emerald-700' : 'text-rose-700'"
                   x-text="schemaValid ? 'VALID ✅' : 'INVALID ❌'"></div>
            </div>

            <textarea name="form_schema"
                      x-model="schema"
                      rows="18"
                      class="w-full min-h-[420px] font-mono text-xs leading-relaxed bg-white border-0 focus:ring-0 p-4"
                      :class="schemaValid ? '' : 'bg-rose-50/40'"></textarea>
          </div>

          @error('form_schema') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>
      </div>

      {{-- Action Bar --}}
      <div class="px-6 md:px-8 py-4 bg-white border-t border-slate-100 flex items-center justify-end gap-2">
        <a href="{{ route('check_sheets.index') }}"
           class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm transition">
          Batal
        </a>

        <button type="submit"
                class="px-4 py-2 rounded-xl bg-[#05727d] hover:bg-[#046168] text-white font-semibold text-sm shadow-sm transition focus:ring-4 focus:ring-[#05727d]/30">
          Simpan Perubahan
        </button>
      </div>
    </form>

    {{-- Toast --}}
    <div x-show="showToast" x-transition
         :class="toastDanger ? 'bg-rose-600' : 'bg-[#05727d]'"
         class="fixed bottom-6 right-6 text-white text-sm px-4 py-2 rounded-xl shadow-lg">
      <span x-text="toastMsg"></span>
    </div>

  </div>
</div>

<script>
  function checkSheetEdit(defaultSchema, defaultFields) {
    return {
      // schema JSON lama
      schema: defaultSchema,
      schemaValid: true,

      // checklist builder
      fields: defaultFields || [],

      showToast: false,
      toastMsg: '',
      toastDanger: false,

      // ---------- Checklist ----------
      addRow() {
        this.fields.push({label: '', key: ''});
      },
      removeRow(idx) {
        if (this.fields.length <= 1) return;
        this.fields.splice(idx, 1);
      },
      autoKey(idx) {
        const row = this.fields[idx];
        if (row && !row.key && row.label) {
          row.key = this.slugify(row.label);
        }
      },
      slugify(str) {
        return (str || '')
          .toString()
          .toLowerCase()
          .replace(/[^a-z0-9]+/g, '_')
          .replace(/^_+|_+$/g, '');
      },

      // ---------- Schema JSON ----------
      validateSchema() {
        try {
          JSON.parse(this.schema);
          this.schemaValid = true;
          this.toast('JSON valid ✅');
        } catch (e) {
          this.schemaValid = false;
          this.toast('JSON masih error ❌', true);
        }
      },

      formatSchema() {
        try {
          const obj = JSON.parse(this.schema);
          this.schema = JSON.stringify(obj, null, 2);
          this.schemaValid = true;
          this.toast('Schema dirapihin ✅');
        } catch (e) {
          this.schemaValid = false;
          this.toast('JSON schema masih error 😅', true);
        }
      },

      toast(msg, danger=false) {
        this.toastMsg = msg;
        this.toastDanger = danger;
        this.showToast = true;
        setTimeout(() => { this.showToast = false; }, 1800);
      }
    }
  }
</script>
@endsection
