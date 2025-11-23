@extends('layouts.app')
@section('title', 'Buat Template SOP')

@section('content')
@php
  $brand = '#05727d';

  $formSchemaStr = old('form_schema') ?? json_encode(
    $template->form_schema ?? [
      "title" => ($template->name ?? "Template SOP"),
      "version" => 1,
      "fields" => []
    ],
    JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
  );

  $builderSchemaStr = old('builder_schema') ?? json_encode(
    $template->builder_schema ?? ["blocks"=>[]],
    JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
  );

  $metaStr = old('meta') ?? json_encode(
    $template->meta ?? ["extra_fields"=>[]],
    JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
  );
@endphp

<div class="max-w-6xl mx-auto space-y-6"
     x-data="tplForm()"
     x-init="init()">

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
            Isi data dasar & schema JSON builder.
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
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('sop.templates.store') }}" method="POST" class="space-y-6">
    @csrf

    {{-- ===== TABS ===== --}}
    <div class="flex flex-wrap gap-2 bg-white ring-1 ring-slate-200 rounded-2xl p-2">
      <button type="button" @click="tab='basic'"
              class="px-4 py-2 rounded-xl text-sm font-semibold transition"
              :class="tab==='basic'
                ? 'bg-[#05727d] text-white shadow-sm'
                : 'bg-transparent text-slate-700 hover:bg-slate-50'">
        Informasi Dasar
      </button>

      <button type="button" @click="tab='schema'"
              class="px-4 py-2 rounded-xl text-sm font-semibold transition"
              :class="tab==='schema'
                ? 'bg-[#05727d] text-white shadow-sm'
                : 'bg-transparent text-slate-700 hover:bg-slate-50'">
        Schema Builder
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
                 placeholder="SOP Penimbangan & Mixing">
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

    {{-- ===== SCHEMA ===== --}}
    <div x-show="tab==='schema'" x-transition
         class="bg-white rounded-3xl ring-1 ring-slate-200 p-5 md:p-6 space-y-5">

      {{-- ===== IMPORT FROM SOP (DROPDOWN) ===== --}}
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
            <select x-model="importSopId"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-sm py-2.5">
              <option value="">-- pilih SOP --</option>

              @foreach($sops ?? [] as $s)
                <option value="{{ $s->id }}"
                  data-text="{{ strtolower(($s->code ?? '').' '.($s->title ?? '').' '.($s->department ?? '').' '.($s->product ?? '').' '.($s->line ?? '')) }}">
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
            <button type="button"
                    @click="importFromSop()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-[#05727d] hover:bg-[#045e68] text-white text-sm font-semibold shadow-sm active:scale-[.98] transition disabled:opacity-60"
                    :disabled="importLoading || !importSopId">
              <svg x-show="!importLoading" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v16h16M4 12h16M12 4v16"/>
              </svg>
              <svg x-show="importLoading" class="w-4 h-4 animate-spin" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity=".25"/>
                <path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="4" fill="none"/>
              </svg>
              <span x-text="importLoading ? 'Mengambil...' : 'Import Schema'"></span>
            </button>

            <button type="button"
                    @click="clearImported()"
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

      {{-- sub tabs --}}
      <div class="flex flex-wrap gap-2">
        <button type="button" @click="schemaTab='form'"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition ring-1"
                :class="schemaTab==='form'
                  ? 'bg-[#05727d] text-white ring-[#05727d]'
                  : 'bg-white text-slate-700 ring-slate-200 hover:bg-slate-50'">
          form_schema
        </button>

        <button type="button" @click="schemaTab='builder'"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition ring-1"
                :class="schemaTab==='builder'
                  ? 'bg-[#05727d] text-white ring-[#05727d]'
                  : 'bg-white text-slate-700 ring-slate-200 hover:bg-slate-50'">
          builder_schema
        </button>

        <button type="button" @click="schemaTab='meta'"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition ring-1"
                :class="schemaTab==='meta'
                  ? 'bg-[#05727d] text-white ring-[#05727d]'
                  : 'bg-white text-slate-700 ring-slate-200 hover:bg-slate-50'">
          meta
        </button>
      </div>

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
          Schema JSON ini dipakai generate form SOP. Pastikan valid sebelum simpan.
        </p>
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
  function tplForm(){
    return {
      tab: 'basic',
      schemaTab: 'form',

      json: {
        form: @json($formSchemaStr),
        builder: @json($builderSchemaStr),
        meta: @json($metaStr),
      },

      // SOP dropdown + search
      importSopId: '',
      importLoading: false,
      importInfo: '',
      sopSearch: '',

      toast: {show:false,msg:'',danger:false},

      init(){
        this.tryFormatAll();
        this.$nextTick(() => this.bindSopSearch());
      },

      bindSopSearch(){
        const select = this.$root.querySelector('select[x-model="importSopId"]');
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

      labelFor(t){
        return t==='form' ? 'form_schema (JSON)'
             : t==='builder' ? 'builder_schema (JSON)'
             : 'meta (JSON)';
      },

      tryFormatAll(){
        ['form','builder','meta'].forEach(k=>{
          try { this.json[k]=JSON.stringify(JSON.parse(this.json[k]),null,2); }catch(e){}
        });
      },

      formatJSON(tab){
        const k = tab==='form'?'form':tab==='builder'?'builder':'meta';
        try {
          this.json[k]=JSON.stringify(JSON.parse(this.json[k]),null,2);
          this.notify('JSON dirapihin ✅');
        } catch(e){ this.notify('JSON masih error 😅',true); }
      },

      validateJSON(tab){
        const k = tab==='form'?'form':tab==='builder'?'builder':'meta';
        try { JSON.parse(this.json[k]); this.notify('JSON valid ✅'); }
        catch(e){ this.notify('JSON tidak valid ❌',true); }
      },

      async importFromSop(){
        if (!this.importSopId) return;

        this.importLoading = true;
        this.importInfo = '';

        try {
          // endpoint SOP json (punya SopController)
          const url = `{{ url('/sop') }}/${this.importSopId}/json`;
          const res = await fetch(url, { headers: { 'Accept': 'application/json' } });

          if (!res.ok) throw new Error('SOP tidak ditemukan / tidak punya akses');

          const data = await res.json();

          this.json.form    = JSON.stringify(data.form_schema ?? {}, null, 2);
          this.json.builder = JSON.stringify(data.builder_schema ?? {}, null, 2);
          this.json.meta    = JSON.stringify(data.meta ?? {}, null, 2);

          this.schemaTab = 'form';
          this.importInfo = `${data.code ?? '-'} • ${data.title ?? 'SOP'}`;
          this.notify('Schema berhasil di-import ✅');
        } catch (e) {
          this.notify(e.message || 'Gagal import SOP ❌', true);
        } finally {
          this.importLoading = false;
        }
      },

      clearImported(){
        this.importSopId = '';
        this.importInfo = '';
        this.notify('Pilihan SOP dibersihkan');
      },

      notify(msg,danger=false){
        this.toast={show:true,msg,danger};
        clearTimeout(this._t);
        this._t=setTimeout(()=>this.toast.show=false,1600);
      }
    }
  }
</script>
@endsection
