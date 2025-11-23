@extends('layouts.app')
@section('title', 'Edit Check Sheet')

@section('content')
@php
// asumsi variabel dari controller: $checkSheet
// fields/schema disimpan di kolom form_schema (JSON)
$defaultSchema = old('form_schema') ?: ($checkSheet->form_schema ?? json_encode([
"title" => $checkSheet->title ?? "Check Sheet",
"version" => (int)($checkSheet->version ?? 1),
"fields" => []
], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
@endphp

<div class="max-w-6xl mx-auto space-y-5"
    x-data="{
    schema: @json($defaultSchema),
    showToast:false, toastMsg:'', toastDanger:false,
    formatSchema(){
      try{
        const obj = JSON.parse(this.schema);
        this.schema = JSON.stringify(obj,null,2);
        this.toast('Schema dirapihin ✅');
      }catch(e){
        this.toast('JSON schema masih error 😅', true);
      }
    },
    toast(msg, danger=false){
      this.toastMsg = msg; this.toastDanger = danger;
      this.showToast = true;
      setTimeout(()=> this.showToast=false, 1800);
    }
  }">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Check Sheet</h1>
            <p class="text-sm text-slate-500 mt-1">
                Ubah metadata & schema builder check sheet.
            </p>
        </div>

        <a href="{{ route('check_sheets.index') }}"
            class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold">
            ← Kembali
        </a>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('check_sheets.update', $checkSheet->id) }}"
        class="bg-white rounded-2xl ring-1 ring-slate-200 p-5 space-y-5">
        @csrf
        @method('PUT')

        {{-- Basic fields --}}
        <div class="grid md:grid-cols-3 gap-4">
            <div class="md:col-span-1">
                <label class="text-sm font-semibold text-slate-700">Kode</label>
                <input name="code" value="{{ old('code', $checkSheet->code) }}"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-maroon-500 focus:ring-maroon-500"
                    placeholder="CS-001">
                @error('code') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-700">Judul</label>
                <input name="title" value="{{ old('title', $checkSheet->title) }}"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-maroon-500 focus:ring-maroon-500"
                    placeholder="Contoh Check Sheet Produksi">
                @error('title') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm font-semibold text-slate-700">Department</label>
                <input name="department" value="{{ old('department', $checkSheet->department) }}"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-maroon-500 focus:ring-maroon-500"
                    placeholder="Produksi / HSE / QC">
                @error('department') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Status</label>
                <select name="status"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-maroon-500 focus:ring-maroon-500">
                    @php $st = old('status', $checkSheet->status); @endphp
                    <option value="draft" @selected($st==='draft' )>Draft</option>
                    <option value="approved" @selected($st==='approved' )>Approved</option>
                    <option value="archived" @selected($st==='archived' )>Archived</option>
                </select>
                @error('status') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Aktif?</label>
                @php $ac = old('active', (int)$checkSheet->active); @endphp
                <select name="active"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-maroon-500 focus:ring-maroon-500">
                    <option value="1" @selected($ac==1)>Aktif</option>
                    <option value="0" @selected($ac==0)>Nonaktif</option>
                </select>
                @error('active') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Schema JSON --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-semibold text-slate-700">Form Schema (JSON)</label>
                <button type="button" @click="formatSchema()"
                    class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Rapihin JSON
                </button>
            </div>

            <textarea name="form_schema" x-model="schema" rows="16"
                class="w-full font-mono text-xs rounded-xl border-slate-300 focus:border-maroon-500 focus:ring-maroon-500"></textarea>

            <p class="text-xs text-slate-500 mt-2">
                Isi schema builder untuk check sheet. Format harus valid JSON.
            </p>
            @error('form_schema') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-2 pt-1">
            <a href="{{ route('check_sheets.index') }}"
                class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm">
                Batal
            </a>
            <button type="submit"
                class="px-4 py-2 rounded-xl bg-maroon-700 hover:bg-maroon-800 text-white font-semibold text-sm">
                Simpan Perubahan
            </button>
        </div>
    </form>

    {{-- Toast --}}
    <div x-show="showToast" x-transition
        :class="toastDanger ? 'bg-rose-600' : 'bg-emerald-600'"
        class="fixed bottom-6 right-6 text-white text-sm px-4 py-2 rounded-xl shadow-lg">
        <span x-text="toastMsg"></span>
    </div>
</div>
@endsection