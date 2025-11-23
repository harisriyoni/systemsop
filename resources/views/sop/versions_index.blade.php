@extends('layouts.app')
@section('title', 'Versi SOP (Latest)')

@section('content')
@php
  $user = auth()->user();
  $canManage  = $user->isRole(['admin','produksi']);
  $canApprove = $user->isRole(['admin','produksi','qa','logistik']);
@endphp

<div class="space-y-4">

  {{-- HEADER --}}
  <div class="bg-white rounded-2xl border border-[#05727d]/20 shadow-sm p-5">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h2 class="text-base font-semibold text-slate-900">Versi SOP (Latest per Kode)</h2>
        <p class="text-xs text-slate-500">
          Menampilkan {{ $latestSops->count() }} dari {{ $latestSops->total() }} SOP (yang paling terbaru tiap kode).
        </p>
      </div>
    </div>

    {{-- SEARCH --}}
    <form method="GET" action="{{ route('sop.versions.index') }}"
          class="mt-4 bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl p-3">
      <div class="grid grid-cols-1 md:grid-cols-6 gap-3 text-xs">
        <div class="md:col-span-3">
          <label class="block mb-1 text-slate-600">Kata Kunci</label>
          <input type="text" name="q" value="{{ request('q') }}"
                 placeholder="Cari kode / judul SOP..."
                 class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                        focus:ring-4 focus:ring-[#05727d]/15 focus:border-[#05727d] outline-none">
        </div>

        <div class="md:col-span-3 flex items-end justify-end gap-2">
          <button
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg
                   bg-[#05727d] hover:bg-[#05727d]/90 text-white font-semibold text-xs transition">
            Cari
          </button>
          <a href="{{ route('sop.versions.index') }}"
             class="inline-flex items-center justify-center px-4 py-2 rounded-lg
                    bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
            Reset
          </a>
        </div>
      </div>
    </form>
  </div>


  {{-- LIST CARD (biar action gak turun) --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    @forelse($latestSops as $sop)
      @php
        $photoCount = is_array($sop->photos ?? null) ? count($sop->photos) : 0;
      @endphp

      <div class="bg-white rounded-2xl border border-[#05727d]/20 shadow-sm p-4 flex flex-col gap-3">
        {{-- TOP --}}
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <div class="font-semibold text-slate-900 truncate">{{ $sop->code }}</div>
              <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-50 border border-slate-200 text-slate-600">
                v{{ $sop->version ?? 1 }}
              </span>

              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-[10px] font-semibold {{ $sop->status_badge_class }}">
                {{ $sop->status_label }}
              </span>
            </div>

            <div class="mt-1 text-xs font-medium text-slate-800 line-clamp-2">
              {{ $sop->title }}
            </div>

            <div class="mt-1 text-[11px] text-slate-500">
              Dept: {{ $sop->department ?? '-' }}
              @if($sop->product || $sop->line)
                • {{ $sop->product ?? '-' }} {{ $sop->line ? ' / '.$sop->line : '' }}
              @endif
            </div>

            <div class="mt-1 flex items-center gap-1.5 text-[11px]">
              <span class="px-2 py-0.5 rounded-full border
                {{ $sop->is_public ? 'bg-[#05727d]/10 text-[#05727d] border-[#05727d]/30' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                {{ $sop->is_public ? 'Publik' : 'Privat' }}
              </span>

              <span class="px-2 py-0.5 rounded-full border bg-white text-slate-600 border-slate-200">
                {{ $photoCount }} Foto
              </span>

              @if($sop->pin)
                <span class="px-2 py-0.5 rounded-full border bg-amber-50 text-amber-700 border-amber-200">
                  PIN
                </span>
              @endif
            </div>
          </div>

          <div class="text-[11px] text-slate-500 whitespace-nowrap">
            Updated<br>
            <span class="font-semibold text-slate-700">
              {{ optional($sop->updated_at)->format('d M Y H:i') }}
            </span>
          </div>
        </div>

        {{-- ACTIONS (sejajar, no turun) --}}
        <div class="flex flex-wrap items-center gap-2">
          <a href="{{ route('sop.show', $sop) }}"
             class="inline-flex items-center px-3 py-1.5 rounded-lg
                    bg-white border border-[#05727d]/30 text-[#05727d]
                    hover:bg-[#05727d]/5 font-semibold text-[11px] transition">
            Lihat
          </a>

          @if(Route::has('sop.versions'))
            <a href="{{ route('sop.versions', $sop) }}"
               class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-[11px] transition">
              Versi
            </a>
          @endif

          @if(Route::has('sop.history'))
            <a href="{{ route('sop.history', $sop) }}"
               class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-[11px] transition">
              History
            </a>
          @endif

          {{-- tombol revisi kalau approved --}}
          @if($canManage && $sop->status === 'approved' && Route::has('sop.edit'))
            <a href="{{ route('sop.edit', $sop) }}"
               class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#05727d] text-white hover:bg-[#05727d]/90 font-semibold text-[11px] transition">
              Revisi v{{ $sop->version ?? 1 }}
            </a>
          @endif
        </div>
      </div>
    @empty
      <div class="col-span-full bg-white rounded-2xl border border-[#05727d]/20 shadow-sm p-10 text-center">
        <div class="text-sm font-semibold text-slate-800">Belum ada data SOP.</div>
        <div class="text-xs text-slate-500 mt-1">Coba reset filter atau buat SOP baru.</div>
      </div>
    @endforelse
  </div>

  {{-- PAGINATION --}}
  <div class="flex justify-end">
    {{ $latestSops->appends(request()->query())->links() }}
  </div>
</div>
@endsection
