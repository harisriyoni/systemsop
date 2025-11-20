@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

  {{-- SOP SUMMARY --}}
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
    <div class="flex items-center justify-between mb-2">
      <h2 class="text-sm font-semibold text-slate-700">Total SOP</h2>
      <span class="text-xs text-slate-400">All Status</span>
    </div>
    <div class="text-3xl font-bold text-slate-900 mb-3">{{ $sop['total'] }}</div>
    <dl class="grid grid-cols-2 gap-2 text-xs">
      <div class="flex justify-between">
        <dt class="text-slate-500">Draft</dt>
        <dd class="font-semibold text-slate-800">{{ $sop['draft'] }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Waiting Approval</dt>
        <dd class="font-semibold text-amber-600">{{ $sop['waiting'] }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Approved</dt>
        <dd class="font-semibold text-emerald-600">{{ $sop['approved'] }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Expired</dt>
        <dd class="font-semibold text-rose-600">{{ $sop['expired'] }}</dd>
      </div>
    </dl>
  </div>

  {{-- CHECK SHEET TODAY --}}
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
    <div class="flex items-center justify-between mb-2">
      <h2 class="text-sm font-semibold text-slate-700">Check Sheet Today</h2>
      <span class="text-xs text-slate-400">
        {{ $filters['date'] ?? now()->toDateString() }}
      </span>
    </div>
    <dl class="space-y-1 text-xs">
      <div class="flex justify-between">
        <dt class="text-slate-500">Submitted</dt>
        <dd class="font-semibold text-slate-800">{{ $checkSheetToday['submitted'] }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Under Review</dt>
        <dd class="font-semibold text-amber-600">{{ $checkSheetToday['underReview'] }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Approved</dt>
        <dd class="font-semibold text-emerald-600">{{ $checkSheetToday['approved'] }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Rejected</dt>
        <dd class="font-semibold text-rose-600">{{ $checkSheetToday['rejected'] }}</dd>
      </div>
    </dl>
  </div>

  {{-- NEED ACTION --}}
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
    <h2 class="text-sm font-semibold text-slate-700 mb-3">Need Action</h2>
    <ul class="space-y-2 text-xs">
      <li class="flex items-center justify-between">
        <span class="text-slate-600">SOP pending QA approval</span>
        <span class="inline-flex items-center justify-center min-w-[28px] h-7 rounded-full bg-amber-50 text-amber-700 font-semibold">
          {{ $needAction['sop_pending_qa'] }}
        </span>
      </li>
      <li class="flex items-center justify-between">
        <span class="text-slate-600">Check Sheet pending Logistik approval</span>
        <span class="inline-flex items-center justify-center min-w-[28px] h-7 rounded-full bg-sky-50 text-sky-700 font-semibold">
          {{ $needAction['cs_pending_logistik'] }}
        </span>
      </li>
    </ul>
  </div>
</div>

{{-- FILTER BAR --}}
<div class="mt-6 bg-white rounded-2xl border border-slate-200 p-4">
  <h2 class="text-sm font-semibold text-slate-700 mb-3">Filter</h2>
  <form method="GET" action="{{ route('dashboard') }}" class="grid gap-3 md:grid-cols-4 text-xs">
    <div>
      <label class="block mb-1 text-slate-500">Tanggal</label>
      <input type="date" name="date" value="{{ $filters['date'] }}"
             class="w-full rounded-lg border border-slate-300 px-2 py-1.5">
    </div>
    <div>
      <label class="block mb-1 text-slate-500">Departemen</label>
      <input type="text" name="department" value="{{ $filters['department'] }}"
             class="w-full rounded-lg border border-slate-300 px-2 py-1.5" placeholder="Produksi / QA / ...">
    </div>
    <div>
      <label class="block mb-1 text-slate-500">Produk</label>
      <input type="text" name="product" value="{{ $filters['product'] }}"
             class="w-full rounded-lg border border-slate-300 px-2 py-1.5">
    </div>
    <div>
      <label class="block mb-1 text-slate-500">Line Produksi</label>
      <input type="text" name="line" value="{{ $filters['line'] }}"
             class="w-full rounded-lg border border-slate-300 px-2 py-1.5">
    </div>
    <div class="md:col-span-4 flex justify-end">
      <button class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-medium">
        Apply Filter
      </button>
    </div>
  </form>
</div>
@endsection
