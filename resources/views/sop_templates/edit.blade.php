@extends('layouts.app')
@section('title', 'Edit Template SOP')

@section('content')
@php
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

<div class="max-w-6xl mx-auto space-y-5"
     x-data="tplForm()"
     x-init="init()">

  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Edit Template SOP</h1>
      <p class="text-sm text-slate-500 mt-1">Update info dasar & schema JSON.</p>
    </div>

    <a href="{{ route('sop.templates.index') }}"
       class="px-3 py-2 rounded-lg text-sm bg-slate-100 hover:bg-slate-200 text-slate-700">
      ← Kembali
    </a>
  </div>

  {{-- Flash / Errors --}}
  @if (session('success'))
    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="p-3 rounded-xl bg-rose-50 text-rose-800 ring-1 ring-rose-200">
      <div class="font-semibold mb-1">Ada error:</div>
      <ul class="list-disc ml-5 text-sm space-y-1">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('sop.templates.update',$template) }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

    {{-- Tabs --}}
    <div class="flex gap-2">
      <button type="button" @click="tab='basic'"
              :class="tab==='basic' ? 'bg-maroon-700 text-white' : 'bg-white text-slate-700 hover:bg-slate-50'"
              class="px-4 py-2 rounded-xl text-sm font-semibold ring-1 ring-slate-200">
        Informasi Dasar
      </button>

      <button type="button" @click="tab='schema'"
              :class="tab==='schema' ? 'bg-maroon-700 text-white' : 'bg-white text-slate-700 hover:bg-slate-50'"
              class="px-4 py-2 rounded-xl text-sm font-semibold ring-1 ring-slate-200">
        Schema Builder
      </button>
    </div>

    {{-- BASIC --}}
    <div x-show="tab==='basic'" x-transition
         class="bg-white rounded-2xl ring-1 ring-slate-200 p-5 space-y-4">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold text-slate-700">Nama Template *</label>
          <input type="text" name="name"
                 value="{{ old('name', $template->name) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 focus:border-maroon-600 focus:ring-maroon-600">
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Kode Template (unik)</label>
          <input type="text" name="code"
                 value="{{ old('code', $template->code) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 focus:border-maroon-600 focus:ring-maroon-600">
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Departemen</label>
          <input type="text" name="department"
                 value="{{ old('department', $template->department) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 focus:border-maroon-600 focus:ring-maroon-600">
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Product</label>
          <input type="text" name="product"
                 value="{{ old('product', $template->product) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 focus:border-maroon-600 focus:ring-maroon-600">
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Line</label>
          <input type="text" name="line"
                 value="{{ old('line', $template->line) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 focus:border-maroon-600 focus:ring-maroon-600">
        </div>

        <div class="flex items-center gap-3 mt-6">
          <input type="hidden" name="is_active" value="0">
          <input id="is_active" type="checkbox" name="is_active" value="1"
                 {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                 class="rounded border-slate-300 text-maroon-700 focus:ring-maroon-600">
          <label for="is_active" class="text-sm font-semibold text-slate-700">
            Aktifkan Template
          </label>
        </div>
      </div>
    </div>

    {{-- SCHEMA --}}
    <div x-show="tab==='schema'" x-transition
         class="bg-white rounded-2xl ring-1 ring-slate-200 p-5 space-y-5">

      <div class="flex gap-2">
        <button type="button" @click="schemaTab='form'"
                :class="schemaTab==='form' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold">
          form_schema
        </button>
        <button type="button" @click="schemaTab='builder'"
                :class="schemaTab==='builder' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold">
          builder_schema
        </button>
        <button type="button" @click="schemaTab='meta'"
                :class="schemaTab==='meta' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold">
          meta
        </button>
      </div>

      <div class="space-y-2">
        <div class="flex justify-between items-center">
          <div class="text-sm font-semibold text-slate-700" x-text="labelFor(schemaTab)"></div>
          <div class="flex gap-2">
            <button type="button" @click="formatJSON(schemaTab)"
                    class="px-3 py-1.5 rounded-lg text-xs bg-slate-100 hover:bg-slate-200">
              Rapihin JSON
            </button>
            <button type="button" @click="validateJSON(schemaTab)"
                    class="px-3 py-1.5 rounded-lg text-xs bg-slate-100 hover:bg-slate-200">
              Cek JSON
            </button>
          </div>
        </div>

        <textarea x-show="schemaTab==='form'" name="form_schema" x-model="json.form" rows="14"
                  class="w-full font-mono text-xs rounded-xl border-slate-300 focus:border-maroon-600 focus:ring-maroon-600"></textarea>

        <textarea x-show="schemaTab==='builder'" name="builder_schema" x-model="json.builder" rows="14"
                  class="w-full font-mono text-xs rounded-xl border-slate-300 focus:border-maroon-600 focus:ring-maroon-600"></textarea>

        <textarea x-show="schemaTab==='meta'" name="meta" x-model="json.meta" rows="14"
                  class="w-full font-mono text-xs rounded-xl border-slate-300 focus:border-maroon-600 focus:ring-maroon-600"></textarea>
      </div>

      <div x-show="toast.show" x-transition
           class="fixed bottom-5 right-5 px-4 py-2 rounded-xl text-sm shadow-lg ring-1"
           :class="toast.danger ? 'bg-rose-600 text-white ring-rose-700' : 'bg-emerald-600 text-white ring-emerald-700'">
        <span x-text="toast.msg"></span>
      </div>
    </div>

    {{-- Actions --}}
    <div class="flex justify-end gap-2">
      <a href="{{ route('sop.templates.index') }}"
         class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700">
        Batal
      </a>
      <button type="submit"
              class="px-5 py-2 rounded-xl bg-maroon-700 hover:bg-maroon-800 text-white font-semibold">
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
