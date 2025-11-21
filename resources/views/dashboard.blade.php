@extends('layouts.app')

@section('title', 'Dashbord PT. DIPSOL INDONESIA ')

@section('content')
@php
  $sopTotalStatus = ($sop['draft'] ?? 0) + ($sop['waiting'] ?? 0) + ($sop['approved'] ?? 0) + ($sop['expired'] ?? 0);
  $csTotalHariIni = ($checkSheetToday['submitted'] ?? 0) + ($checkSheetToday['underReview'] ?? 0) + ($checkSheetToday['approved'] ?? 0) + ($checkSheetToday['rejected'] ?? 0);
  $needTotal      = ($needAction['sop_pending_qa'] ?? 0) + ($needAction['cs_pending_logistik'] ?? 0);

  $hasSopData  = $sopTotalStatus > 0;
  $hasCsData   = $csTotalHariIni > 0;
  $hasNeedData = $needTotal > 0;
@endphp

{{-- ===== RINGKASAN ATAS ===== --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

  {{-- RINGKASAN SOP --}}
  <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
    <div class="flex items-center justify-between mb-2">
      <div>
        <h2 class="text-sm font-semibold text-slate-800">Total SOP</h2>
        <p class="text-xs text-slate-400">Semua Status</p>
      </div>
      <div class="h-9 w-9 rounded-xl bg-blue-50 text-blue-600 grid place-items-center">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
        </svg>
      </div>
    </div>

    <div class="text-3xl font-bold text-slate-900 mb-3">{{ $sop['total'] }}</div>

    <dl class="grid grid-cols-2 gap-2 text-xs">
      <div class="flex justify-between">
        <dt class="text-slate-500">Draf</dt>
        <dd class="font-semibold text-slate-800">{{ $sop['draft'] }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Menunggu Persetujuan</dt>
        <dd class="font-semibold text-blue-600">{{ $sop['waiting'] }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Disetujui</dt>
        <dd class="font-semibold text-blue-700">{{ $sop['approved'] }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Kedaluwarsa</dt>
        <dd class="font-semibold text-slate-400">{{ $sop['expired'] }}</dd>
      </div>
    </dl>
  </div>

  {{-- CHECK SHEET HARI INI --}}
  <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
    <div class="flex items-center justify-between mb-2">
      <div>
        <h2 class="text-sm font-semibold text-slate-800">Check Sheet Hari Ini</h2>
        <p class="text-xs text-slate-400">{{ $filters['date'] ?? now()->toDateString() }}</p>
      </div>
      <div class="h-9 w-9 rounded-xl bg-blue-50 text-blue-600 grid place-items-center">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
      </div>
    </div>

    <dl class="space-y-1 text-xs">
      <div class="flex justify-between">
        <dt class="text-slate-500">Terkirim</dt>
        <dd class="font-semibold text-slate-800">{{ $checkSheetToday['submitted'] }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Dalam Tinjauan</dt>
        <dd class="font-semibold text-blue-600">{{ $checkSheetToday['underReview'] }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Disetujui</dt>
        <dd class="font-semibold text-blue-700">{{ $checkSheetToday['approved'] }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Ditolak</dt>
        <dd class="font-semibold text-slate-400">{{ $checkSheetToday['rejected'] }}</dd>
      </div>
    </dl>
  </div>

  {{-- PERLU TINDAKAN --}}
  <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-sm font-semibold text-slate-800">Perlu Tindakan</h2>
      <div class="h-9 w-9 rounded-xl bg-blue-50 text-blue-600 grid place-items-center">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
    </div>

    <ul class="space-y-2 text-xs">
      <li class="flex items-center justify-between">
        <span class="text-slate-600">SOP menunggu persetujuan QA</span>
        <span class="inline-flex items-center justify-center min-w-[28px] h-7 rounded-full bg-blue-50 text-blue-700 font-semibold">
          {{ $needAction['sop_pending_qa'] }}
        </span>
      </li>
      <li class="flex items-center justify-between">
        <span class="text-slate-600">Check Sheet menunggu persetujuan Logistik</span>
        <span class="inline-flex items-center justify-center min-w-[28px] h-7 rounded-full bg-blue-50 text-blue-700 font-semibold">
          {{ $needAction['cs_pending_logistik'] }}
        </span>
      </li>
    </ul>
  </div>
</div>


{{-- ===== GRAFIK (pendek + ada empty state) ===== --}}
<div class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-4">

  {{-- Grafik Donat SOP --}}
  <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
    <div class="flex items-center justify-between mb-2">
      <h3 class="text-sm font-semibold text-slate-800">Komposisi SOP</h3>
      <span class="text-xs text-slate-400">Status SOP</span>
    </div>

    @if($hasSopData)
      <div class="relative h-48 md:h-52">
        <canvas id="sopStatusChart"></canvas>
      </div>
    @else
      <div class="h-48 md:h-52 grid place-items-center text-sm text-slate-400">
        Belum ada data SOP
      </div>
    @endif
  </div>

  {{-- Grafik Batang Check Sheet Hari Ini --}}
  <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
    <div class="flex items-center justify-between mb-2">
      <h3 class="text-sm font-semibold text-slate-800">Check Sheet Hari Ini</h3>
      <span class="text-xs text-slate-400">Ringkasan</span>
    </div>

    @if($hasCsData)
      <div class="relative h-48 md:h-52">
        <canvas id="csTodayChart"></canvas>
      </div>
    @else
      <div class="h-48 md:h-52 grid place-items-center text-sm text-slate-400">
        Belum ada data Check Sheet hari ini
      </div>
    @endif
  </div>

  {{-- Grafik Tindakan Tertunda --}}
  <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
    <div class="flex items-center justify-between mb-2">
      <h3 class="text-sm font-semibold text-slate-800">Perlu Tindakan</h3>
      <span class="text-xs text-slate-400">Tertunda</span>
    </div>

    @if($hasNeedData)
      <div class="relative h-48 md:h-52">
        <canvas id="needActionChart"></canvas>
      </div>
    @else
      <div class="h-48 md:h-52 grid place-items-center text-sm text-slate-400">
        Tidak ada persetujuan yang tertunda
      </div>
    @endif
  </div>

</div>


{{-- ===== FILTER ===== --}}
<div class="mt-5 bg-white rounded-2xl border border-blue-100 p-5">
  <h2 class="text-sm font-semibold text-slate-800 mb-3">Filter</h2>
  <form method="GET" action="{{ route('dashboard') }}" class="grid gap-3 md:grid-cols-4 text-xs">
    <div>
      <label class="block mb-1 text-slate-500">Tanggal</label>
      <input type="date" name="date" value="{{ $filters['date'] }}"
             class="w-full rounded-lg border border-slate-200 px-3 py-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none">
    </div>
    <div>
      <label class="block mb-1 text-slate-500">Departemen</label>
      <input type="text" name="department" value="{{ $filters['department'] }}"
             class="w-full rounded-lg border border-slate-200 px-3 py-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none"
             placeholder="Produksi / QA / ...">
    </div>
    <div>
      <label class="block mb-1 text-slate-500">Produk</label>
      <input type="text" name="product" value="{{ $filters['product'] }}"
             class="w-full rounded-lg border border-slate-200 px-3 py-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none">
    </div>
    <div>
      <label class="block mb-1 text-slate-500">Lini Produksi</label>
      <input type="text" name="line" value="{{ $filters['line'] }}"
             class="w-full rounded-lg border border-slate-200 px-3 py-2 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none">
    </div>
    <div class="md:col-span-4 flex justify-end">
      <button class="inline-flex items-center px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition">
        Terapkan Filter
      </button>
    </div>
  </form>
</div>


{{-- ===== CHART.JS ===== --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const sop = @json($sop);
  const csHariIni = @json($checkSheetToday);
  const perluTindakan = @json($needAction);

  const hasSopData  = @json($hasSopData);
  const hasCsData   = @json($hasCsData);
  const hasNeedData = @json($hasNeedData);

  const safeMax = (arr) => {
    const m = Math.max(...arr);
    return m <= 3 ? 3 : m + 1;
  };

  // 1) Donat SOP
  if (hasSopData) {
    new Chart(document.getElementById('sopStatusChart'), {
      type: 'doughnut',
      data: {
        labels: ['Draf', 'Menunggu Persetujuan', 'Disetujui', 'Kedaluwarsa'],
        datasets: [{
          data: [sop.draft, sop.waiting, sop.approved, sop.expired],
          backgroundColor: ['#e2e8f0', '#93c5fd', '#2563eb', '#cbd5e1'],
          borderWidth: 0,
          hoverOffset: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: {
          legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } }
        }
      }
    });
  }

  // 2) Batang Check Sheet Hari Ini
  if (hasCsData) {
    const csVals = [
      csHariIni.submitted,
      csHariIni.underReview,
      csHariIni.approved,
      csHariIni.rejected
    ];

    new Chart(document.getElementById('csTodayChart'), {
      type: 'bar',
      data: {
        labels: ['Terkirim', 'Dalam Tinjauan', 'Disetujui', 'Ditolak'],
        datasets: [{
          data: csVals,
          backgroundColor: ['#bfdbfe', '#93c5fd', '#2563eb', '#e2e8f0'],
          borderRadius: 8,
          borderSkipped: false
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false } },
          y: {
            beginAtZero: true,
            suggestedMax: safeMax(csVals),
            ticks: { precision: 0, stepSize: 1 }
          }
        }
      }
    });
  }

  // 3) Batang Horizontal Perlu Tindakan
  if (hasNeedData) {
    const needVals = [
      perluTindakan.sop_pending_qa,
      perluTindakan.cs_pending_logistik
    ];

    new Chart(document.getElementById('needActionChart'), {
      type: 'bar',
      data: {
        labels: ['SOP pending QA', 'Check Sheet pending Logistik'],
        datasets: [{
          data: needVals,
          backgroundColor: ['#2563eb', '#93c5fd'],
          borderRadius: 8,
          borderSkipped: false
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: {
            beginAtZero: true,
            suggestedMax: safeMax(needVals),
            ticks: { precision: 0, stepSize: 1 }
          },
          y: { grid: { display: false } }
        }
      }
    });
  }
</script>
@endsection
