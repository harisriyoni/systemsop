@extends('layouts.app')
@section('title', 'History SOP')

@section('content')
<div class="bg-white rounded-2xl border border-[#05727d]/20 shadow-sm p-5 space-y-4">

  {{-- HEADER --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
      <h2 class="text-base font-semibold text-slate-900">Riwayat SOP</h2>
      <p class="text-xs text-slate-500 mt-1">
        {{ $sop->code }} • {{ $sop->title }}
      </p>
    </div>

    <div class="flex items-center gap-2">
      <a href="{{ route('sop.show', $sop) }}"
         class="inline-flex items-center px-3 py-2 rounded-lg bg-white border border-[#05727d]/30 text-[#05727d]
                hover:bg-[#05727d]/5 text-xs font-semibold transition">
        ← Kembali
      </a>

      @if(\Route::has('sop.download'))
        <a href="{{ route('sop.download', $sop) }}"
           class="inline-flex items-center px-3 py-2 rounded-lg bg-[#05727d] text-white
                  hover:bg-[#0894a0] text-xs font-semibold transition shadow-sm">
          Download PDF
        </a>
      @endif
    </div>
  </div>

  {{-- INFO SOP --}}
  <div class="bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl p-4 text-xs grid md:grid-cols-4 gap-3">
    <div>
      <div class="text-slate-500">Departemen</div>
      <div class="font-semibold text-slate-800">{{ $sop->department }}</div>
    </div>
    <div>
      <div class="text-slate-500">Produk</div>
      <div class="font-semibold text-slate-800">{{ $sop->product ?? '-' }}</div>
    </div>
    <div>
      <div class="text-slate-500">Line</div>
      <div class="font-semibold text-slate-800">{{ $sop->line ?? '-' }}</div>
    </div>
    <div>
      <div class="text-slate-500">Status</div>
      <div class="font-semibold text-[#05727d]">{{ strtoupper($sop->status) }}</div>
    </div>
  </div>

  {{-- TABLE HISTORY --}}
  <div class="overflow-x-auto rounded-xl border border-[#05727d]/20">
    <table class="min-w-full text-xs bg-white">
      <thead class="bg-[#05727d]/5 text-[#05727d] text-[11px] uppercase tracking-wider">
        <tr>
          <th class="px-4 py-3 text-left whitespace-nowrap">Waktu</th>
          <th class="px-4 py-3 text-left whitespace-nowrap">Aksi</th>
          <th class="px-4 py-3 text-left whitespace-nowrap">Oleh</th>
          <th class="px-4 py-3 text-left">Catatan</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-[#05727d]/10">
        @forelse(($logs ?? []) as $log)
          <tr class="hover:bg-[#05727d]/5 transition">
            <td class="px-4 py-3 whitespace-nowrap">
              {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }}
            </td>
            <td class="px-4 py-3 whitespace-nowrap font-semibold text-slate-800">
              {{ $log->action ?? '-' }}
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-slate-700">
              {{ $log->user->name ?? $log->by_name ?? '-' }}
            </td>
            <td class="px-4 py-3 text-slate-700">
              {{ $log->note ?? '-' }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="px-4 py-8 text-center">
              <div class="text-sm font-semibold text-slate-700 mb-1">Belum ada riwayat.</div>
              <div class="text-xs text-slate-400">
                Jika kamu belum bikin tabel log/history, halaman ini tetap aman tampil begini.
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

</div>
@endsection
