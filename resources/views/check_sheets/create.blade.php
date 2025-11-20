@extends('layouts.app')
@section('title', 'Create Check Sheet Form')

@section('content')
<form method="POST" action="{{ route('check_sheets.store') }}"
      class="bg-white rounded-2xl border border-slate-200 p-4 text-sm space-y-3">
  @csrf

  @if ($errors->any())
    <div class="rounded-md bg-rose-50 border border-rose-200 text-rose-800 px-3 py-2 text-xs">
      {{ $errors->first() }}
    </div>
  @endif

  <div class="grid md:grid-cols-2 gap-3">
    <div>
      <label class="block text-xs text-slate-500 mb-1">Title</label>
      <input type="text" name="title" value="{{ old('title') }}"
             class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
    </div>
    <div>
      <label class="block text-xs text-slate-500 mb-1">Department</label>
      <input type="text" name="department" value="{{ old('department') }}"
             class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs" placeholder="QA / Logistik / Produksi">
    </div>
    <div>
      <label class="block text-xs text-slate-500 mb-1">Product</label>
      <input type="text" name="product" value="{{ old('product') }}"
             class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
    </div>
    <div>
      <label class="block text-xs text-slate-500 mb-1">Line</label>
      <input type="text" name="line" value="{{ old('line') }}"
             class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
    </div>
  </div>

  <div>
    <label class="block text-xs text-slate-500 mb-1">Description / Instruksi Singkat</label>
    <textarea name="description" rows="4"
              class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">{{ old('description') }}</textarea>
  </div>

  <div class="flex justify-between text-xs pt-2">
    <a href="{{ route('check_sheets.index') }}" class="text-slate-500 hover:underline">
      ← Kembali
    </a>
    <button class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs">
      Save Form
    </button>
  </div>
</form>
@endsection
