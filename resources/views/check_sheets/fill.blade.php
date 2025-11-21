@extends('layouts.app')
@section('title', 'Isi Check Sheet - '.$checkSheet->title)

@section('content')
<div class="max-w-4xl mx-auto space-y-4">

  {{-- ================= HEADER / INFO FORM ================= --}}
  <div class="bg-white border border-blue-100 rounded-2xl shadow-sm overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-5 text-white">
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-11 w-11 rounded-2xl bg-white/15 grid place-items-center shrink-0">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
          </div>
          <div>
            <div class="text-xs text-blue-100">Form Check Sheet</div>
            <div class="text-2xl font-semibold leading-tight">
              {{ $checkSheet->title }}
            </div>
            <div class="text-sm text-blue-50/90 mt-1">
              Departemen: <span class="font-semibold">{{ $checkSheet->department }}</span>
              @if($checkSheet->product) • Produk: <span class="font-semibold">{{ $checkSheet->product }}</span> @endif
              @if($checkSheet->line) • Line: <span class="font-semibold">{{ $checkSheet->line }}</span> @endif
            </div>
          </div>
        </div>

        <div class="text-right text-xs text-blue-100 hidden md:block">
          Status: 
          <span class="inline-flex px-2 py-1 rounded-full bg-white/10 border border-white/20 text-white font-semibold">
            {{ strtoupper($checkSheet->status) }}
          </span>
        </div>
      </div>
    </div>

    @if($checkSheet->description)
      <div class="px-6 py-4 bg-blue-50/40 border-t border-blue-100 text-sm text-slate-700">
        <div class="text-xs font-semibold text-blue-700 mb-1">Instruksi Singkat</div>
        {{ $checkSheet->description }}
      </div>
    @endif
  </div>


  {{-- ================= FORM INPUT ================= --}}
  <form method="POST" action="{{ route('check_sheets.submit', $checkSheet) }}"
        class="bg-white border border-blue-100 rounded-2xl shadow-sm p-6 space-y-5">
    @csrf

    {{-- ERROR GLOBAL --}}
    @if ($errors->any())
      <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-xs">
        <div class="font-semibold mb-1">Periksa input kamu:</div>
        <ul class="list-disc pl-4 space-y-0.5">
          @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- SECTION: DATA SHIFT --}}
    <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4">
      <div class="text-xs font-semibold text-blue-700 mb-3 flex items-center gap-2">
        <span class="h-2 w-2 rounded-full bg-blue-600"></span>
        Data Pengecekan
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- SHIFT --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">
            Shift <span class="text-rose-500">*</span>
          </label>
          <input type="text" name="shift" value="{{ old('shift') }}"
                 placeholder="Contoh: Shift 1 / Shift A"
                 class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                        {{ $errors->has('shift') ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500' : 'border-slate-200 focus:ring-blue-100 focus:border-blue-500' }}">
          @error('shift')
            <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div>
          @enderror
        </div>

        {{-- NOTES --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">
            Catatan Tambahan (Opsional)
          </label>
          <input type="text" name="notes" value="{{ old('notes') }}"
                 placeholder="Misal: ada temuan kecil, dll"
                 class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                        focus:ring-blue-100 focus:border-blue-500">
          @error('notes')
            <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div>
          @enderror
        </div>

      </div>
    </div>


    {{-- SECTION: HASIL --}}
    <div>
      <label class="block text-xs text-slate-600 mb-1">
        Hasil Pengecekan <span class="text-rose-500">*</span>
      </label>

      <textarea name="result" rows="7"
                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none
                       focus:ring-4 focus:ring-blue-100 focus:border-blue-500"
                placeholder="- Contoh: Temperatur sesuai
- Pressure OK
- Kebocoran tidak ada
- dll...">{{ old('result') }}</textarea>

      <div class="text-[11px] text-slate-400 mt-1">
        Tuliskan hasil secara poin biar mudah dibaca.
      </div>

      @error('result')
        <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div>
      @enderror
    </div>


    {{-- ACTIONS --}}
    <div class="pt-2 flex flex-col-reverse md:flex-row md:items-center md:justify-between gap-2">
      <a href="{{ route('dashboard') }}"
         class="inline-flex items-center gap-2 text-slate-600 hover:text-blue-700 text-xs font-semibold">
        ← Kembali ke Dashboard
      </a>

      <button
        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl
               bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Submit Check Sheet
      </button>
    </div>

  </form>

</div>
@endsection
