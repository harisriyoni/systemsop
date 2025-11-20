@extends('layouts.app')
@section('title', 'QR Center')

@section('content')
<div class="grid md:grid-cols-2 gap-4">
  <div class="bg-white rounded-2xl border border-slate-200 p-4">
    <h2 class="text-sm font-semibold text-slate-700 mb-3">QR SOP (Display Operator)</h2>
    <div class="grid grid-cols-2 gap-3">
      @forelse ($sops as $sop)
        @php
          $url = route('sop.show', $sop);
        @endphp
        <div class="border border-slate-200 rounded-xl p-2 text-center text-xs">
          <div class="font-semibold text-slate-700 mb-1">{{ $sop->code }}</div>
          <div class="text-[11px] text-slate-500 mb-2">{{ $sop->title }}</div>
          <img
            class="mx-auto mb-1"
            src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($url) }}"
            alt="QR SOP {{ $sop->code }}">
          <div class="text-[10px] text-slate-400 break-all">{{ $url }}</div>
        </div>
      @empty
        <p class="text-xs text-slate-400">Belum ada SOP approved.</p>
      @endforelse
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 p-4">
    <h2 class="text-sm font-semibold text-slate-700 mb-3">QR Check Sheet (Isi via Operator)</h2>
    <div class="grid grid-cols-2 gap-3">
      @forelse ($forms as $form)
        @php
          $url = route('check_sheets.fill', $form);
        @endphp
        <div class="border border-slate-200 rounded-xl p-2 text-center text-xs">
          <div class="font-semibold text-slate-700 mb-1">{{ $form->title }}</div>
          <div class="text-[11px] text-slate-500 mb-2">{{ $form->department }}</div>
          <img
            class="mx-auto mb-1"
            src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($url) }}"
            alt="QR Form {{ $form->title }}">
          <div class="text-[10px] text-slate-400 break-all">{{ $url }}</div>
        </div>
      @empty
        <p class="text-xs text-slate-400">Belum ada form aktif.</p>
      @endforelse
    </div>
  </div>
</div>
@endsection
