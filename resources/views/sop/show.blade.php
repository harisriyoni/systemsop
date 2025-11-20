@extends('layouts.app')

@section('title', 'Detail SOP '.$sop->code)

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 p-5 text-sm space-y-4">
  <div class="flex items-start justify-between gap-3">
    <div>
      <div class="text-xs text-slate-500 mb-1">Kode SOP</div>
      <div class="text-lg font-semibold text-slate-900">{{ $sop->code }}</div>
      <div class="text-sm text-slate-600">{{ $sop->title }}</div>
    </div>
    <div class="text-right">
      <div class="text-xs text-slate-500 mb-1">Status</div>
      <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold
        @if($sop->status === 'approved') bg-emerald-50 text-emerald-700
        @elseif($sop->status === 'waiting_approval') bg-amber-50 text-amber-700
        @elseif($sop->status === 'expired') bg-rose-50 text-rose-700
        @else bg-slate-50 text-slate-700
        @endif">
        {{ strtoupper($sop->status) }}
      </span>
    </div>
  </div>

  <div class="grid md:grid-cols-3 gap-3 text-xs">
    <div>
      <div class="text-slate-500 mb-0.5">Departemen</div>
      <div class="font-medium text-slate-800">{{ $sop->department }}</div>
    </div>
    <div>
      <div class="text-slate-500 mb-0.5">Produk</div>
      <div class="font-medium text-slate-800">{{ $sop->product ?: '-' }}</div>
    </div>
    <div>
      <div class="text-slate-500 mb-0.5">Line Produksi</div>
      <div class="font-medium text-slate-800">{{ $sop->line ?: '-' }}</div>
    </div>
    <div>
      <div class="text-slate-500 mb-0.5">Efektif Dari</div>
      <div class="font-medium text-slate-800">
        {{ $sop->effective_from?->format('d M Y') ?? '-' }}
      </div>
    </div>
    <div>
      <div class="text-slate-500 mb-0.5">Efektif Sampai</div>
      <div class="font-medium text-slate-800">
        {{ $sop->effective_to?->format('d M Y') ?? '-' }}
      </div>
    </div>
    <div>
      <div class="text-slate-500 mb-0.5">Pembuat</div>
      <div class="font-medium text-slate-800">
        {{ $sop->creator->name ?? '-' }}
      </div>
    </div>
  </div>

  <div class="border-t border-slate-100 pt-4">
    <div class="flex items-center justify-between mb-2 text-xs">
      <div class="text-slate-500">Approval Status</div>
    </div>
    <div class="grid md:grid-cols-3 gap-3 text-xs">
      <div class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2">
        <div class="text-slate-500">Produksi</div>
        <div class="font-semibold">
          @if($sop->is_approved_produksi)
            <span class="text-emerald-600">✔ Approved</span>
          @else
            <span class="text-amber-600">Waiting</span>
          @endif
        </div>
      </div>
      <div class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2">
        <div class="text-slate-500">QA</div>
        <div class="font-semibold">
          @if($sop->is_approved_qa)
            <span class="text-emerald-600">✔ Approved</span>
          @else
            <span class="text-amber-600">Waiting</span>
          @endif
        </div>
      </div>
      <div class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2">
        <div class="text-slate-500">Logistik</div>
        <div class="font-semibold">
          @if($sop->is_approved_logistik)
            <span class="text-emerald-600">✔ Approved</span>
          @else
            <span class="text-amber-600">Waiting</span>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="border-t border-slate-100 pt-4">
    <div class="text-xs text-slate-500 mb-1">Isi SOP</div>
    <div class="prose prose-sm max-w-none">
      {!! nl2br(e($sop->content)) !!}
    </div>
  </div>

  <div class="pt-3 flex justify-between text-xs">
    <a href="{{ route('sop.index') }}" class="text-slate-500 hover:underline">
      ← Kembali ke List SOP
    </a>
  </div>
</div>
@endsection
