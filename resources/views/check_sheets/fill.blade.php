@extends('layouts.app')
@section('title', 'Isi Check Sheet - '.$checkSheet->title)

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 p-4 text-sm space-y-3">
  <div class="mb-2">
    <div class="text-xs text-slate-500">Form Check Sheet</div>
    <div class="text-base font-semibold text-slate-900">{{ $checkSheet->title }}</div>
    <div class="text-xs text-slate-500">
      Dept: {{ $checkSheet->department }} |
      Product: {{ $checkSheet->product ?: '-' }} |
      Line: {{ $checkSheet->line ?: '-' }}
    </div>
    @if($checkSheet->description)
      <div class="mt-2 text-xs text-slate-600">
        {{ $checkSheet->description }}
      </div>
    @endif
  </div>

  <form method="POST" action="{{ route('check_sheets.submit', $checkSheet) }}" class="space-y-3">
    @csrf

    @if ($errors->any())
      <div class="rounded-md bg-rose-50 border border-rose-200 text-rose-800 px-3 py-2 text-xs">
        {{ $errors->first() }}
      </div>
    @endif

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-xs text-slate-500 mb-1">Shift</label>
        <input type="text" name="shift" value="{{ old('shift') }}"
               class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs" placeholder="Shift 1 / Shift A / etc">
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Catatan Tambahan (Opsional)</label>
        <input type="text" name="notes" value="{{ old('notes') }}"
               class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs" placeholder="Opsional">
      </div>
    </div>

    <div>
      <label class="block text-xs text-slate-500 mb-1">Hasil Pengecekan</label>
      <textarea name="result" rows="6"
                class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
                placeholder="- Contoh: Temperatur sesuai\n- Pressure OK\n- dll...">{{ old('result') }}</textarea>
    </div>

    <div class="flex justify-between text-xs pt-2">
      <a href="{{ route('dashboard') }}" class="text-slate-500 hover:underline">
        ← Dashboard
      </a>
      <button class="inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs">
        Submit Check Sheet
      </button>
    </div>
  </form>
</div>
@endsection
