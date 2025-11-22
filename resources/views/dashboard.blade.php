@extends('layouts.app')

@section('title', 'Dashboard PT. DIPSOL INDONESIA')

@section('content')
@php
$sopTotalStatus = ($sop['draft'] ?? 0) + ($sop['waiting'] ?? 0) + ($sop['approved'] ?? 0) + ($sop['expired'] ?? 0);
$csTotalHariIni = ($checkSheetToday['submitted'] ?? 0) + ($checkSheetToday['underReview'] ?? 0) + ($checkSheetToday['approved'] ?? 0) + ($checkSheetToday['rejected'] ?? 0);
$needTotal = ($needAction['sop_pending_qa'] ?? 0) + ($needAction['cs_pending_logistik'] ?? 0);

$hasSopData = $sopTotalStatus > 0;
$hasCsData = $csTotalHariIni > 0;
$hasNeedData = $needTotal > 0;

$user = auth()->user();
@endphp

{{-- =========================
  HEADER / HERO
========================= --}}
<div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-500 text-white p-6 md:p-7 shadow-sm">
  <div class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-white/10 blur-2xl"></div>
  <div class="absolute right-10 top-4 w-32 h-32 rounded-full bg-white/10 blur-xl"></div>

  <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <div class="text-xs/relaxed opacity-90">SOP + CheckFlow Center</div>
      <h1 class="text-xl md:text-2xl font-bold tracking-tight">
        Halo, {{ $user->name ?? 'User' }} 👋
      </h1>
      <p class="text-sm opacity-90 mt-1">
        Ringkasan aktivitas produksi & kepatuhan hari ini.
      </p>
    </div>

    {{-- Quick Actions --}}
    <div class="flex flex-wrap gap-2">
      @if($user->isRole(['admin','produksi']))
      <a href="{{ route('sop.create') }}"
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/20 text-sm font-semibold transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Buat SOP
      </a>
      @endif

      @if($user->isRole(['admin','produksi','qa','logistik']))
      <a href="{{ route('check_sheets.create') }}"
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/20 text-sm font-semibold transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Buat Check Sheet
      </a>
      @endif

      <a href="{{ route('qr_center.index') }}"
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white text-blue-700 hover:bg-blue-50 text-sm font-semibold transition shadow-sm">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h3v3h-3zM17 17h3v3h-3z" />
        </svg>
        QR Center
      </a>
    </div>
  </div>
</div>

<div class="mt-5 bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
  <div class="flex items-center justify-between mb-3">
    <div>
      <h2 class="text-sm font-semibold text-slate-800">Filter Dashboard</h2>
      <p class="text-xs text-slate-400">Sesuaikan tampilan data</p>
    </div>
    <a href="{{ route('dashboard') }}" class="text-xs text-slate-500 hover:text-blue-600 font-semibold">
      Reset filter
    </a>
  </div>

  <form method="GET" action="{{ route('dashboard') }}" class="grid gap-3 md:grid-cols-4 text-xs">
    <div>
      <label class="block mb-1 text-slate-500">Tanggal</label>
      <input type="date" name="date" value="{{ $filters['date'] ?? '' }}"
        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5
                    focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition">
    </div>

    <div>
      <label class="block mb-1 text-slate-500">Departemen</label>
      <input type="text" name="department" value="{{ $filters['department'] ?? '' }}"
        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5
                    focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition"
        placeholder="Produksi / QA / Logistik">
    </div>

    <div>
      <label class="block mb-1 text-slate-500">Produk</label>
      <input type="text" name="product" value="{{ $filters['product'] ?? '' }}"
        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5
                    focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition"
        placeholder="Nama produk">
    </div>

    <div>
      <label class="block mb-1 text-slate-500">Lini Produksi</label>
      <input type="text" name="line" value="{{ $filters['line'] ?? '' }}"
        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5
                    focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition"
        placeholder="Line A / B / C">
    </div>

    <div class="md:col-span-4 flex justify-end gap-2 pt-1">
      <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L15 12.414V19a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6.586L3.293 6.707A1 1 0 013 6V4z" />
        </svg>
        Terapkan Filter
      </button>
    </div>
  </form>
</div>  
{{-- =========================
  KPI CARDS
========================= --}}



<div class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-4">

  {{-- KPI SOP --}}
  <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
    <div class="flex items-start justify-between">
      <div>
        <h2 class="text-sm font-semibold text-slate-800">Total SOP</h2>
        <p class="text-xs text-slate-400">Semua status</p>
      </div>

      <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-700 grid place-items-center">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
        </svg>
      </div>
    </div>

    <div class="mt-3 flex items-end justify-between">
      <div class="text-3xl font-bold text-slate-900">{{ $sop['total'] ?? 0 }}</div>
      <a href="{{ route('sop.index') }}"
        class="text-xs text-blue-600 hover:text-blue-700 font-semibold">
        Lihat detail →
      </a>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
      <div class="rounded-xl bg-slate-50 px-3 py-2 flex justify-between">
        <span class="text-slate-500">Draft</span>
        <span class="font-semibold">{{ $sop['draft'] ?? 0 }}</span>
      </div>
      <div class="rounded-xl bg-blue-50 px-3 py-2 flex justify-between">
        <span class="text-slate-500">Waiting</span>
        <span class="font-semibold text-blue-700">{{ $sop['waiting'] ?? 0 }}</span>
      </div>
      <div class="rounded-xl bg-blue-50/60 px-3 py-2 flex justify-between">
        <span class="text-slate-500">Approved</span>
        <span class="font-semibold text-blue-800">{{ $sop['approved'] ?? 0 }}</span>
      </div>
      <div class="rounded-xl bg-slate-50 px-3 py-2 flex justify-between">
        <span class="text-slate-500">Expired</span>
        <span class="font-semibold text-slate-500">{{ $sop['expired'] ?? 0 }}</span>
      </div>
    </div>
  </div>

  {{-- KPI Check Sheet Today --}}
  <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
    <div class="flex items-start justify-between">
      <div>
        <h2 class="text-sm font-semibold text-slate-800">Check Sheet Hari Ini</h2>
        <p class="text-xs text-slate-400">{{ $filters['date'] ?? now()->toDateString() }}</p>
      </div>
      <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-700 grid place-items-center">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
      </div>
    </div>

    <div class="mt-3 flex items-end justify-between">
      <div class="text-3xl font-bold text-slate-900">{{ $csTotalHariIni }}</div>
      @if($user->isRole(['admin','produksi','qa','logistik']))
      <a href="{{ route('check_sheets.submissions') }}"
        class="text-xs text-blue-600 hover:text-blue-700 font-semibold">
        Lihat submissions →
      </a>
      @endif
    </div>

    <div class="mt-4 space-y-2 text-xs">
      <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2">
        <span class="text-slate-600">Submitted</span>
        <span class="font-semibold">{{ $checkSheetToday['submitted'] ?? 0 }}</span>
      </div>
      <div class="flex items-center justify-between rounded-xl bg-blue-50 px-3 py-2">
        <span class="text-slate-600">Under Review</span>
        <span class="font-semibold text-blue-700">{{ $checkSheetToday['underReview'] ?? 0 }}</span>
      </div>
      <div class="flex items-center justify-between rounded-xl bg-blue-50/60 px-3 py-2">
        <span class="text-slate-600">Approved</span>
        <span class="font-semibold text-blue-800">{{ $checkSheetToday['approved'] ?? 0 }}</span>
      </div>
      <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2">
        <span class="text-slate-600">Rejected</span>
        <span class="font-semibold text-slate-500">{{ $checkSheetToday['rejected'] ?? 0 }}</span>
      </div>
    </div>
  </div>

  {{-- KPI Need Action --}}
  <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
    <div class="flex items-start justify-between">
      <div>
        <h2 class="text-sm font-semibold text-slate-800">Perlu Tindakan</h2>
        <p class="text-xs text-slate-400">Approval tertunda</p>
      </div>
      <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-700 grid place-items-center">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
    </div>

    <div class="mt-3 flex items-center justify-between">
      <div class="text-3xl font-bold text-slate-900">{{ $needTotal }}</div>
      <div class="text-xs text-slate-400">total pending</div>
    </div>

    <div class="mt-4 space-y-2 text-xs">
      <div class="flex items-center justify-between rounded-xl bg-blue-50 px-3 py-2">
        <div class="flex items-center gap-2">
          <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
          <span class="text-slate-700">SOP pending QA</span>
        </div>
        <span class="font-semibold text-blue-800">{{ $needAction['sop_pending_qa'] ?? 0 }}</span>
      </div>

      <div class="flex items-center justify-between rounded-xl bg-blue-50 px-3 py-2">
        <div class="flex items-center gap-2">
          <span class="w-2 h-2 bg-sky-500 rounded-full"></span>
          <span class="text-slate-700">CS pending Logistik</span>
        </div>
        <span class="font-semibold text-blue-800">{{ $needAction['cs_pending_logistik'] ?? 0 }}</span>
      </div>

      <div class="pt-2">
        <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
          @php
          $progress = $needTotal > 0 ? min(100, ($needTotal / max(1, $needTotal + 5)) * 100) : 0;
          @endphp
          <div class="h-full bg-gradient-to-r from-blue-600 to-sky-500" style="width: {{ $progress }}%"></div>
        </div>
        <div class="text-[11px] text-slate-400 mt-1">indikator beban approval</div>
      </div>
    </div>
  </div>
</div>


{{-- =========================
  CHARTS SECTION
========================= --}}
<div class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-4">

  {{-- Donut SOP --}}
  <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
    <div class="flex items-center justify-between mb-2">
      <div>
        <h3 class="text-sm font-semibold text-slate-800">Komposisi SOP</h3>
        <p class="text-xs text-slate-400">Per status</p>
      </div>
      <div class="text-[11px] px-2 py-1 bg-blue-50 text-blue-700 rounded-lg">30 hari terakhir</div>
    </div>

    @if($hasSopData)
    <div class="relative h-52">
      <canvas id="sopStatusChart"></canvas>
    </div>
    @else
    <div class="h-52 grid place-items-center text-sm text-slate-400">
      Belum ada data SOP
    </div>
    @endif
  </div>

  {{-- Bar CS Today --}}
  <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
    <div class="flex items-center justify-between mb-2">
      <div>
        <h3 class="text-sm font-semibold text-slate-800">Check Sheet Hari Ini</h3>
        <p class="text-xs text-slate-400">Aktivitas operasional</p>
      </div>
      <div class="text-[11px] px-2 py-1 bg-blue-50 text-blue-700 rounded-lg">harian</div>
    </div>

    @if($hasCsData)
    <div class="relative h-52">
      <canvas id="csTodayChart"></canvas>
    </div>
    @else
    <div class="h-52 grid place-items-center text-sm text-slate-400">
      Belum ada data Check Sheet hari ini
    </div>
    @endif
  </div>

  {{-- Need Action Bar --}}
  <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
    <div class="flex items-center justify-between mb-2">
      <div>
        <h3 class="text-sm font-semibold text-slate-800">Perlu Tindakan</h3>
        <p class="text-xs text-slate-400">Outstanding approval</p>
      </div>
      <div class="text-[11px] px-2 py-1 bg-blue-50 text-blue-700 rounded-lg">pending</div>
    </div>

    @if($hasNeedData)
    <div class="relative h-52">
      <canvas id="needActionChart"></canvas>
    </div>
    @else
    <div class="h-52 grid place-items-center text-sm text-slate-400">
      Tidak ada persetujuan tertunda 🎉
    </div>
    @endif
  </div>

</div>


{{-- =========================
  FILTER SECTION
========================= --}}



{{-- =========================
  CHART.JS
========================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const sop = @json($sop);
  const csHariIni = @json($checkSheetToday);
  const perluTindakan = @json($needAction);

  const hasSopData = @json($hasSopData);
  const hasCsData = @json($hasCsData);
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
        labels: ['Draft', 'Waiting Approval', 'Approved', 'Expired'],
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
        cutout: '70%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 10,
              usePointStyle: true
            }
          },
          tooltip: {
            enabled: true
          }
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
        labels: ['Submitted', 'Under Review', 'Approved', 'Rejected'],
        datasets: [{
          data: csVals,
          backgroundColor: ['#bfdbfe', '#93c5fd', '#2563eb', '#e2e8f0'],
          borderRadius: 10,
          borderSkipped: false
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            enabled: true
          }
        },
        scales: {
          x: {
            grid: {
              display: false
            }
          },
          y: {
            beginAtZero: true,
            suggestedMax: safeMax(csVals),
            ticks: {
              precision: 0,
              stepSize: 1
            }
          }
        }
      }
    });
  }

  // 3) Horizontal Perlu Tindakan
  if (hasNeedData) {
    const needVals = [
      perluTindakan.sop_pending_qa,
      perluTindakan.cs_pending_logistik
    ];

    new Chart(document.getElementById('needActionChart'), {
      type: 'bar',
      data: {
        labels: ['SOP pending QA', 'CS pending Logistik'],
        datasets: [{
          data: needVals,
          backgroundColor: ['#2563eb', '#93c5fd'],
          borderRadius: 10,
          borderSkipped: false
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            enabled: true
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            suggestedMax: safeMax(needVals),
            ticks: {
              precision: 0,
              stepSize: 1
            }
          },
          y: {
            grid: {
              display: false
            }
          }
        }
      }
    });
  }
</script>
@endsection