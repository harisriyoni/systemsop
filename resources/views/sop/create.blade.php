@extends('layouts.app')
@section('title', 'Create SOP')

@section('content')
<form method="POST" action="{{ route('sop.store') }}"
      class="bg-white rounded-2xl border border-slate-200 p-4 text-sm space-y-3">
  @csrf

  @if ($errors->any())
    <div class="rounded-md bg-rose-50 border border-rose-200 text-rose-800 px-3 py-2 text-xs">
      {{ $errors->first() }}
    </div>
  @endif

  <div class="grid md:grid-cols-2 gap-3">
    <div>
      <label class="block text-xs text-slate-500 mb-1">Code</label>
      <input type="text" name="code" value="{{ old('code') }}"
             class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
    </div>
    <div>
      <label class="block text-xs text-slate-500 mb-1">Title</label>
      <input type="text" name="title" value="{{ old('title') }}"
             class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
    </div>
    <div>
      <label class="block text-xs text-slate-500 mb-1">Department</label>
      <input type="text" name="department" value="{{ old('department') }}"
             class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs" placeholder="Produksi / QA / ...">
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
    <div class="grid grid-cols-2 gap-2">
      <div>
        <label class="block text-xs text-slate-500 mb-1">Effective From</label>
        <input type="date" name="effective_from" value="{{ old('effective_from') }}"
               class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Effective To</label>
        <input type="date" name="effective_to" value="{{ old('effective_to') }}"
               class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
      </div>
    </div>
  </div>

  <div>
    <label class="block text-xs text-slate-500 mb-1">Content</label>
    <textarea name="content" rows="5"
              class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">{{ old('content') }}</textarea>
  </div>

  <div class="flex justify-end gap-2">
    <a href="{{ route('sop.index') }}" class="text-xs text-slate-500 hover:underline">Cancel</a>
    <button class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs">
      Save SOP
    </button>
  </div>
</form>
@endsection
