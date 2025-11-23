@extends('layouts.app')
@section('title', 'Edit Template SOP')

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
            Edit Template SOP
          </h1>
          <p class="text-sm text-slate-500 mt-1">
            Update informasi dasar & schema JSON builder.
          </p>

          <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 ring-1 ring-slate-200">
              Kode: {{ $template->code ?? '-' }}
            </span>
            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 ring-1 ring-slate-200">
              Dept: {{ $template->department ?? '-' }}
            </span>
            @if($template->is_active)
              <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 font-semibold">
                Aktif
              </span>
            @else
              <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-semibold">
                Nonaktif
              </span>
            @endif
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('sop.templates.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl text-sm bg-slate-100 hover:bg-slate-200 text-slate-700 active:scale-[.98] transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
          Kembali
        </a>

        {{-- optional view PDF / view detail kalau sudah ada route --}}
        <a href="{{ route('sop.templates.show', $template) }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl text-sm bg-white text-[#05727d] ring-1 ring-[#05727d]/30 hover:bg-[#05727d]/10 active:scale-[.98] transition">
          View
        </a>
      </div>
    </div>
  </div>

  {{-- ===== FLASH / ERRORS ===== --}}
  @if (session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200">
      {{ session('success') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 text-rose-800 ring-1 ring-rose-200">
      <div class="font-semibold mb-1">Ada error:</div>
      <ul class="list-disc ml-5 text-sm space-y-1">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('sop.templates.update',$template) }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

    {{-- ===== MAIN TABS ===== --}}
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

    {{-- ===== BASIC CARD ===== --}}
    <div x-show="tab==='basic'" x-transition
         class="bg-white rounded-3xl ring-1 ring-slate-200 p-5 md:p-6 space-y-5">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">

        {{-- Nama --}}
        <div>
          <label class="text-sm font-semibold text-slate-700">Nama Template *</label>
          <div class="relative mt-1">
            <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6h9M12 12h9M12 18h9M3 6h.01M3 12h.01M3 18h.01"/>
              </svg>
            </span>
            <input type="text" name="name"
                   value="{{ old('name', $template->name) }}"
                   class="w-full pl-10 pr-3 py-2.5 rounded-2xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-sm"
                   placeholder="SOP Penimbangan & Mixing">
          </div>
        </div>

        {{-- Kode --}}
        <div>
          <label class="text-sm font-semibold text-slate-700">Kode Template (unik)</label>
          <div class="relative mt-1">
            <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M6 21h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2z"/>
              </svg>
            </span>
            <input type="text" name="code"
                   value="{{ old('code', $template->code) }}"
                   class="w-full pl-10 pr-3 py-2.5 rounded-2xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-sm"
                   placeholder="TMP-SOP-001">
          </div>
        </div>

        {{-- Dept --}}
        <div>
          <label class="text-sm font-semibold text-slate-700">Departemen</label>
          <input type="text" name="department"
                 value="{{ old('department', $template->department) }}"
                 class="mt-1 w-full rounded-2xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-sm py-2.5"
                 placeholder="Produksi / HSE / QC">
        </div>

        {{-- Product --}}
        <div>
          <label class="text-sm font-semibold text-slate-700">Product</label>
          <input type="text" name="product"
                 value="{{ old('product', $template->product) }}"
                 class="mt-1 w-full rounded-2xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-sm py-2.5"
                 placeholder="Nama produk">
        </div>

        {{-- Line --}}
        <div>
          <label class="text-sm font-semibold text-slate-700">Line / Area</label>
          <input type="text" name="line"
                 value="{{ old('line', $template->line) }}"
                 class="mt-1 w-full rounded-2xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-sm py-2.5"
                 placeholder="Line A / Area">
        </div>

        {{-- Active --}}
        <div class="flex items-center gap-3 md:mt-6">
          <input type="hidden" name="is_active" value="0">
          <input id="is_active" type="checkbox" name="is_active" value="1"
                 {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                 class="rounded border-slate-300 text-[#05727d] focus:ring-[#05727d]">
          <label for="is_active" class="text-sm font-semibold text-slate-700">
            Aktifkan Template
          </label>
        </div>

      </div>

      <div class="text-xs text-slate-500">
        * Wajib diisi. Kode template harus unik.
      </div>
    </div>

    {{-- ===== SCHEMA CARD ===== --}}
    <div x-show="tab==='schema'" x-transition
         class="bg-white rounded-3xl ring-1 ring-slate-200 p-5 md:p-6 space-y-5">

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

        <div class="ml-auto flex gap-2">
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

      {{-- editor wrapper --}}
      <div class="rounded-2xl ring-1 ring-slate-200 overflow-hidden bg-slate-50">
        <textarea x-show="schemaTab==='form'" name="form_schema" x-model="json.form" rows="16"
                  class="w-full font-mono text-xs bg-transparent border-0 focus:ring-0 p-3 md:p-4"></textarea>

        <textarea x-show="schemaTab==='builder'" name="builder_schema" x-model="json.builder" rows="16"
                  class="w-full font-mono text-xs bg-transparent border-0 focus:ring-0 p-3 md:p-4"></textarea>

        <textarea x-show="schemaTab==='meta'" name="meta" x-model="json.meta" rows="16"
                  class="w-full font-mono text-xs bg-transparent border-0 focus:ring-0 p-3 md:p-4"></textarea>
      </div>

      <p class="text-xs text-slate-500">
        Schema JSON dipakai generate form SOP. Pastikan valid sebelum simpan.
      </p>

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
        Simpan Perubahan
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
      toast: {show:false,msg:'',danger:false},

      init(){ this.tryFormatAll(); },

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

      notify(msg,danger=false){
        this.toast={show:true,msg,danger};
        clearTimeout(this._t);
        this._t=setTimeout(()=>this.toast.show=false,1600);
      }
    }
  }
</script>
@endsection
