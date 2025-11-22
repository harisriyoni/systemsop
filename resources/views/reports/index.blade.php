@extends('layouts.app')
@section('title', 'Report')

@section('content')
@php
  // ====== SAFE FALLBACKS ======
  $rangeFrom = request('from');
  $rangeTo   = request('to');
  $dept      = request('department');
  $type      = request('type');

  // totals SOP by status
  $sopTotals = $sopTotals ?? [
    'draft' => 0,
    'waiting_approval' => 0,
    'approved' => 0,
    'expired' => 0,
  ];

  // totals submission by status
  $subTotals = $subTotals ?? [
    'submitted' => 0,
    'under_review' => 0,
    'approved' => 0,
    'rejected' => 0,
  ];

  // totals check sheet forms
  $formTotals = $formTotals ?? [
    'active' => 0,
    'draft' => 0,
    'inactive' => 0,
  ];

  $recentSops = $recentSops ?? collect();
  $recentSubs = $recentSubs ?? collect();

  $grandSop = array_sum($sopTotals);
  $grandSub = array_sum($subTotals);

  $statusMapSop = [
    'draft' => ['label'=>'Draf', 'cls'=>'bg-slate-50 text-slate-700 border-slate-200'],
    'waiting_approval' => ['label'=>'Waiting Approval', 'cls'=>'bg-blue-50 text-blue-700 border-blue-200'],
    'approved' => ['label'=>'Approved', 'cls'=>'bg-blue-600 text-white border-blue-600'],
    'expired' => ['label'=>'Expired', 'cls'=>'bg-slate-100 text-slate-500 border-slate-200'],
  ];

  $statusMapSub = [
    'submitted' => ['label'=>'Submitted', 'cls'=>'bg-slate-50 text-slate-700 border-slate-200'],
    'under_review' => ['label'=>'Under Review', 'cls'=>'bg-amber-50 text-amber-700 border-amber-200'],
    'approved' => ['label'=>'Approved', 'cls'=>'bg-emerald-50 text-emerald-700 border-emerald-200'],
    'rejected' => ['label'=>'Rejected', 'cls'=>'bg-rose-50 text-rose-700 border-rose-200'],
  ];
@endphp

<div class="max-w-7xl mx-auto space-y-5">

  {{-- ================= HEADER ================= --}}
  <div class="bg-white border border-[#05727d]/20 rounded-2xl shadow-sm overflow-hidden">
    <div class="bg-gradient-to-r from-[#05727d] to-[#0894a0] px-6 py-5 text-white">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-11 w-11 rounded-2xl bg-white/15 grid place-items-center shrink-0">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M7 15l3-3 3 2 5-5"/>
            </svg>
          </div>
          <div>
            <div class="text-xs text-[#d5f3f4]">Analytics</div>
            <div class="text-2xl font-semibold leading-tight">
              Report SOP & Check Sheet
            </div>
            <div class="text-sm text-[#e8fbfc] mt-1">
              Ringkasan performa dokumen & submission operator.
            </div>
          </div>
        </div>

        {{-- FILTERS --}}
        <form method="GET" action="{{ route('reports.index') }}"
              class="flex flex-wrap items-center gap-2 text-xs">
          <input type="date" name="from" value="{{ $rangeFrom }}"
                 class="rounded-lg border border-white/30 bg-white/10 text-white px-3 py-2 outline-none
                        focus:ring-2 focus:ring-white/40">

          <input type="date" name="to" value="{{ $rangeTo }}"
                 class="rounded-lg border border-white/30 bg-white/10 text-white px-3 py-2 outline-none
                        focus:ring-2 focus:ring-white/40">

          <input type="text" name="department" value="{{ $dept }}"
                 placeholder="Dept (opsional)"
                 class="rounded-lg border border-white/30 bg-white/10 text-white px-3 py-2 outline-none
                        placeholder:text-white/70 focus:ring-2 focus:ring-white/40">

          <select name="type"
                  class="rounded-lg border border-white/30 bg-white/10 text-white px-3 py-2 outline-none
                         focus:ring-2 focus:ring-white/40">
            <option value="">Semua Data</option>
            <option value="sop" {{ $type=='sop'?'selected':'' }}>SOP</option>
            <option value="checksheet" {{ $type=='checksheet'?'selected':'' }}>Check Sheet</option>
          </select>

          <button class="px-3 py-2 rounded-lg bg-white text-[#04535b] font-semibold hover:bg-[#e0f4f6] transition">
            Terapkan
          </button>

          <a href="{{ route('reports.index') }}"
             class="px-3 py-2 rounded-lg bg-white/10 border border-white/30 text-white font-semibold hover:bg-white/20 transition">
            Reset
          </a>
        </form>
      </div>
    </div>
  </div>

  {{-- ================= SUMMARY CARDS ================= --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

    {{-- SOP SUMMARY --}}
    <div class="bg-white border border-[#05727d]/20 rounded-2xl shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <div class="text-sm font-semibold text-slate-900">SOP Summary</div>
        <div class="text-xs text-slate-500">Total: {{ $grandSop }}</div>
      </div>

      <div class="space-y-2 text-xs">
        @foreach($sopTotals as $key => $val)
          @php $s = $statusMapSop[$key] ?? ['label'=>strtoupper($key),'cls'=>'bg-slate-50 text-slate-700 border-slate-200']; @endphp
          <div class="flex items-center justify-between rounded-xl border border-[#b7e9ec] px-3 py-2 bg-[#e0f4f6]">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[11px] font-semibold {{ $s['cls'] }}">
              {{ $s['label'] }}
            </span>
            <div class="font-semibold text-slate-900">{{ $val }}</div>
          </div>
        @endforeach

        @if($grandSop === 0)
          <div class="text-[11px] text-slate-400 italic pt-1">
            Belum ada data SOP pada filter ini.
          </div>
        @endif
      </div>
    </div>

    {{-- CHECK SHEET FORMS SUMMARY --}}
    <div class="bg-white border border-[#05727d]/20 rounded-2xl shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <div class="text-sm font-semibold text-slate-900">Forms Summary</div>
        <div class="text-xs text-slate-500">Total: {{ array_sum($formTotals) }}</div>
      </div>

      <div class="grid grid-cols-3 gap-2 text-xs">
        <div class="rounded-xl border border-[#b7e9ec] bg-[#e0f4f6] px-3 py-2">
          <div class="text-slate-500 mb-1">Active</div>
          <div class="text-lg font-semibold text-slate-900">{{ $formTotals['active'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl border border-[#b7e9ec] bg-white px-3 py-2">
          <div class="text-slate-500 mb-1">Draft</div>
          <div class="text-lg font-semibold text-slate-900">{{ $formTotals['draft'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl border border-[#b7e9ec] bg-white px-3 py-2">
          <div class="text-slate-500 mb-1">Inactive</div>
          <div class="text-lg font-semibold text-slate-900">{{ $formTotals['inactive'] ?? 0 }}</div>
        </div>
      </div>

      <div class="text-[11px] text-slate-500 mt-3">
        Form aktif bisa diakses operator via QR.
      </div>
    </div>

    {{-- SUBMISSIONS SUMMARY --}}
    <div class="bg-white border border-[#05727d]/20 rounded-2xl shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <div class="text-sm font-semibold text-slate-900">Submissions Summary</div>
        <div class="text-xs text-slate-500">Total: {{ $grandSub }}</div>
      </div>

      <div class="space-y-2 text-xs">
        @foreach($subTotals as $key => $val)
          @php $s = $statusMapSub[$key] ?? ['label'=>strtoupper($key),'cls'=>'bg-slate-50 text-slate-700 border-slate-200']; @endphp
          <div class="flex items-center justify-between rounded-xl border border-[#b7e9ec] px-3 py-2 bg-[#e0f4f6]">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[11px] font-semibold {{ $s['cls'] }}">
              {{ $s['label'] }}
            </span>
            <div class="font-semibold text-slate-900">{{ $val }}</div>
          </div>
        @endforeach

        @if($grandSub === 0)
          <div class="text-[11px] text-slate-400 italic pt-1">
            Belum ada submission pada filter ini.
          </div>
        @endif
      </div>
    </div>

  </div>

  {{-- ================= RECENT TABLES ================= --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- RECENT SOP --}}
    <div class="bg-white border border-[#05727d]/20 rounded-2xl shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-[#e0f4f6] flex items-center justify-between">
        <div class="text-sm font-semibold text-slate-900">SOP Terbaru</div>
        <a href="{{ route('sop.index') }}" class="text-xs text-[#04535b] font-semibold hover:underline">
          Lihat semua →
        </a>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-xs">
          <thead class="bg-[#e0f4f6] text-[#04535b] text-[11px] uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3 text-left whitespace-nowrap">Kode</th>
              <th class="px-4 py-3 text-left">Judul</th>
              <th class="px-4 py-3 text-left whitespace-nowrap">Dept</th>
              <th class="px-4 py-3 text-left whitespace-nowrap">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#e0f4f6]">
            @forelse($recentSops as $sop)
              @php $s = $statusMapSop[$sop->status] ?? ['label'=>$sop->status,'cls'=>'bg-slate-50 text-slate-700 border-slate-200']; @endphp
              <tr class="hover:bg-[#e0f4f6]/70 transition">
                <td class="px-4 py-3 font-semibold text-slate-900 whitespace-nowrap">
                  {{ $sop->code }}
                </td>
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-900">{{ $sop->title }}</div>
                  <div class="text-[11px] text-slate-400">
                    {{ optional($sop->created_at)->format('d M Y') }}
                  </div>
                </td>
                <td class="px-4 py-3 text-slate-700 whitespace-nowrap">
                  {{ $sop->department }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-semibold {{ $s['cls'] }}">
                    {{ $s['label'] }}
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-4 py-10 text-center">
                  <div class="text-sm font-semibold text-slate-700 mb-1">Belum ada SOP terbaru</div>
                  <div class="text-xs text-slate-400">Data akan muncul setelah ada SOP dibuat.</div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- RECENT SUBMISSIONS --}}
    <div class="bg-white border border-[#05727d]/20 rounded-2xl shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-[#e0f4f6] flex items-center justify-between">
        <div class="text-sm font-semibold text-slate-900">Submission Terbaru</div>
        <a href="{{ route('check_sheets.submissions') }}" class="text-xs text-[#04535b] font-semibold hover:underline">
          Lihat semua →
        </a>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-xs">
          <thead class="bg-[#e0f4f6] text-[#04535b] text-[11px] uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3 text-left">Form</th>
              <th class="px-4 py-3 text-left whitespace-nowrap">Operator</th>
              <th class="px-4 py-3 text-left whitespace-nowrap">Status</th>
              <th class="px-4 py-3 text-left whitespace-nowrap">Waktu</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#e0f4f6]">
            @forelse($recentSubs as $sub)
              @php $s = $statusMapSub[$sub->status] ?? ['label'=>$sub->status,'cls'=>'bg-slate-50 text-slate-700 border-slate-200']; @endphp
              <tr class="hover:bg-[#e0f4f6]/70 transition align-top">
                <td class="px-4 py-3">
                  <div class="font-semibold text-slate-900">{{ $sub->checkSheet->title ?? '-' }}</div>
                  <div class="text-[11px] text-slate-500">
                    Dept: {{ $sub->checkSheet->department ?? '-' }}
                  </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-slate-800">
                  {{ $sub->operator->name ?? '-' }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-semibold {{ $s['cls'] }}">
                    {{ strtoupper($s['label']) }}
                  </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                  {{ optional($sub->submitted_at)->format('d M Y H:i') ?? '-' }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-4 py-10 text-center">
                  <div class="text-sm font-semibold text-slate-700 mb-1">Belum ada submission terbaru</div>
                  <div class="text-xs text-slate-400">Submission operator akan tampil di sini.</div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>

</div>
@endsection
