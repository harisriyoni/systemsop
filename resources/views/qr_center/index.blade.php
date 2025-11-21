@extends('layouts.app')
@section('title', 'QR Center')

@section('content')
<div class="max-w-6xl mx-auto space-y-4">

  {{-- HEADER --}}
  <div class="bg-white border border-blue-100 rounded-2xl shadow-sm overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-5 text-white">
      <div class="flex items-center justify-between">
        <div class="flex items-start gap-3">
          <div class="h-11 w-11 rounded-2xl bg-white/15 grid place-items-center shrink-0">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h4v4H3V7zm0 6h4v4H3v-4zm6-6h4v4H9V7zm0 6h4v4H9v-4zm6-6h6v4h-6V7zm0 6h6v4h-6v-4z"/>
            </svg>
          </div>
          <div>
            <div class="text-xs text-blue-100">Display Operator</div>
            <div class="text-2xl font-semibold leading-tight">QR Center</div>
            <div class="text-sm text-blue-50/90 mt-1">
              SOP Approved: {{ $sops->count() }} • Form Active: {{ $forms->count() }}
            </div>
          </div>
        </div>

        <a href="{{ route('dashboard') }}"
           class="hidden md:inline-flex items-center px-3 py-2 rounded-lg bg-white text-blue-700 text-xs font-semibold hover:bg-blue-50 transition">
          ← Dashboard
        </a>
      </div>
    </div>
  </div>

  {{-- GRID 2 KOLOM --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- ================= SOP QR ================= --}}
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <div>
          <h2 class="text-sm font-semibold text-slate-900">QR SOP (Display Operator)</h2>
          <p class="text-[11px] text-slate-500">Hanya SOP yang sudah <b>Approved</b></p>
        </div>
      </div>

      @if($sops->count())
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
          @foreach($sops as $sop)
            @php
              // kalau ada route publik dan SOP public, pakai link publik biar bisa tanpa login
              $hasPublicRoute = \Illuminate\Support\Facades\Route::has('sop.public.show');
              $url = ($sop->is_public && $hasPublicRoute)
                ? route('sop.public.show', $sop)
                : route('sop.show', $sop);
            @endphp

            <div class="border border-blue-100 rounded-xl p-3 text-center bg-blue-50/30 hover:bg-blue-50 transition">
              <div class="font-semibold text-slate-900 text-xs mb-0.5">{{ $sop->code }}</div>
              <div class="text-[11px] text-slate-500 line-clamp-2 min-h-[28px]">
                {{ $sop->title }}
              </div>

              <div class="my-2 flex justify-center">
                <img
                  class="w-32 h-32 object-contain bg-white rounded-lg border border-blue-100 p-1"
                  src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($url) }}"
                  alt="QR SOP {{ $sop->code }}">
              </div>

              <div class="text-[10px] text-slate-400 break-all">
                {{ $url }}
              </div>

              <div class="mt-2 text-[10px]">
                @if($sop->is_public)
                  <span class="px-2 py-0.5 rounded-full bg-blue-600 text-white font-semibold">PUBLIC</span>
                @else
                  <span class="px-2 py-0.5 rounded-full bg-white border border-blue-200 text-blue-700 font-semibold">INTERNAL</span>
                @endif
                @if($sop->pin)
                  <span class="ml-1 px-2 py-0.5 rounded-full bg-white border border-slate-200 text-slate-700 font-semibold">
                    PIN
                  </span>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="h-40 grid place-items-center text-slate-400 text-sm bg-blue-50/40 border border-dashed border-blue-200 rounded-xl">
          Belum ada SOP yang approved.
        </div>
      @endif
    </div>

    {{-- ================= CHECK SHEET QR ================= --}}
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <div>
          <h2 class="text-sm font-semibold text-slate-900">QR Check Sheet (Isi via Operator)</h2>
          <p class="text-[11px] text-slate-500">Hanya Form yang berstatus <b>Active</b></p>
        </div>
      </div>

      @if($forms->count())
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
          @foreach($forms as $form)
            @php $url = route('check_sheets.fill', $form); @endphp

            <div class="border border-blue-100 rounded-xl p-3 text-center bg-blue-50/30 hover:bg-blue-50 transition">
              <div class="font-semibold text-slate-900 text-xs mb-0.5">{{ $form->title }}</div>
              <div class="text-[11px] text-slate-500 line-clamp-1">
                {{ $form->department }}
              </div>

              <div class="my-2 flex justify-center">
                <img
                  class="w-32 h-32 object-contain bg-white rounded-lg border border-blue-100 p-1"
                  src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($url) }}"
                  alt="QR Form {{ $form->title }}">
              </div>

              <div class="text-[10px] text-slate-400 break-all">
                {{ $url }}
              </div>

              <div class="mt-2 text-[10px]">
                <span class="px-2 py-0.5 rounded-full bg-emerald-600 text-white font-semibold">ACTIVE</span>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="h-40 grid place-items-center text-slate-400 text-sm bg-blue-50/40 border border-dashed border-blue-200 rounded-xl">
          Belum ada form aktif.
        </div>
      @endif
    </div>

  </div>
</div>
@endsection
