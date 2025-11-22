@extends('layouts.app')
@section('title', 'QR Center')

@section('content')
@php
  // SAFE FALLBACKS
  $sops  = $sops  ?? collect();
  $forms = $forms ?? collect();
  $user  = auth()->user();
@endphp

<div class="max-w-6xl mx-auto space-y-4">

  {{-- HEADER --}}
  <div class="bg-white border border-blue-100 rounded-2xl shadow-sm overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-5 text-white">
      <div class="flex items-center justify-between">
        <div class="flex items-start gap-3">
          <div class="h-11 w-11 rounded-2xl bg-white/15 grid place-items-center shrink-0">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 7h4v4H3V7zm0 6h4v4H3v-4zm6-6h4v4H9V7zm0 6h4v4H9v-4zm6-6h6v4h-6V7zm0 6h6v4h-6v-4z"/>
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

        @if(\Illuminate\Support\Facades\Route::has('dashboard'))
          <a href="{{ route('dashboard') }}"
             class="hidden md:inline-flex items-center px-3 py-2 rounded-lg bg-white text-blue-700 text-xs font-semibold hover:bg-blue-50 transition">
            ← Dashboard
          </a>
        @endif
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

        {{-- quick link approval sop --}}
        @if(\Illuminate\Support\Facades\Route::has('sop.approval.index'))
          <a href="{{ route('sop.approval.index') }}"
             class="text-[11px] font-semibold text-blue-700 hover:underline">
            Approval SOP →
          </a>
        @endif
      </div>

      @if($sops->count())
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
          @foreach($sops as $sop)
            @php
              $hasPublicRoute = \Illuminate\Support\Facades\Route::has('sop.public.show');
              $hasInternalShow = \Illuminate\Support\Facades\Route::has('sop.show');

              $urlPublic = ($sop->is_public && $hasPublicRoute)
                ? route('sop.public.show', $sop)
                : null;

              $urlInternal = $hasInternalShow
                ? route('sop.show', $sop)
                : null;

              // default QR pakai public kalau ada, else internal
              $qrUrl = $urlPublic ?? $urlInternal ?? '#';
            @endphp

            <div class="border border-blue-100 rounded-xl p-3 text-center bg-blue-50/30 hover:bg-blue-50 transition">
              <div class="font-semibold text-slate-900 text-xs mb-0.5">{{ $sop->code }}</div>
              <div class="text-[11px] text-slate-500 line-clamp-2 min-h-[28px]">
                {{ $sop->title }}
              </div>

              <div class="my-2 flex justify-center">
                <img
                  class="w-32 h-32 object-contain bg-white rounded-lg border border-blue-100 p-1"
                  src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($qrUrl) }}"
                  alt="QR SOP {{ $sop->code }}">
              </div>

              <div class="text-[10px] text-slate-400 break-all">
                {{ $qrUrl }}
              </div>

              {{-- badges --}}
              <div class="mt-2 text-[10px] flex flex-wrap justify-center gap-1">
                @if($sop->is_public)
                  <span class="px-2 py-0.5 rounded-full bg-blue-600 text-white font-semibold">PUBLIC</span>
                @else
                  <span class="px-2 py-0.5 rounded-full bg-white border border-blue-200 text-blue-700 font-semibold">INTERNAL</span>
                @endif

                @if($sop->pin)
                  <span class="px-2 py-0.5 rounded-full bg-white border border-slate-200 text-slate-700 font-semibold">
                    PIN
                  </span>
                @endif
              </div>

              {{-- ACTIONS sesuai routes --}}
              <div class="mt-3 grid grid-cols-2 gap-1.5 text-[11px]">
                @if($urlInternal)
                  <a href="{{ $urlInternal }}"
                     class="px-2 py-1 rounded-lg bg-white border border-blue-200 text-blue-700 hover:bg-blue-50 font-semibold transition">
                    Lihat SOP
                  </a>
                @endif

                @if($urlPublic)
                  <a href="{{ $urlPublic }}" target="_blank"
                     class="px-2 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold transition">
                    Lihat Public
                  </a>
                @endif

                @if(\Illuminate\Support\Facades\Route::has('sop.download'))
                  <a href="{{ route('sop.download', $sop) }}"
                     class="px-2 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold transition">
                    Download PDF
                  </a>
                @endif

                {{-- regen QR (POST sop.qr) --}}
                @if(\Illuminate\Support\Facades\Route::has('sop.qr'))
                  <form method="POST" action="{{ route('sop.qr', $sop) }}">
                    @csrf
                    <button type="submit"
                      class="w-full px-2 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 hover:bg-amber-100 font-semibold transition">
                      Regenerate QR
                    </button>
                  </form>
                @endif

                @if(\Illuminate\Support\Facades\Route::has('sop.versions'))
                  <a href="{{ route('sop.versions', $sop) }}"
                     class="px-2 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold transition">
                    Versions
                  </a>
                @endif

                @if(\Illuminate\Support\Facades\Route::has('sop.history'))
                  <a href="{{ route('sop.history', $sop) }}"
                     class="px-2 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold transition">
                    History
                  </a>
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

        @if(\Illuminate\Support\Facades\Route::has('check_sheets.submissions'))
          <a href="{{ route('check_sheets.submissions') }}"
             class="text-[11px] font-semibold text-blue-700 hover:underline">
            Lihat Submissions →
          </a>
        @endif
      </div>

      @if($forms->count())
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
          @foreach($forms as $form)
            @php
              $urlFill = \Illuminate\Support\Facades\Route::has('check_sheets.fill')
                ? route('check_sheets.fill', $form)
                : '#';
            @endphp

            <div class="border border-blue-100 rounded-xl p-3 text-center bg-blue-50/30 hover:bg-blue-50 transition">
              <div class="font-semibold text-slate-900 text-xs mb-0.5">{{ $form->title }}</div>
              <div class="text-[11px] text-slate-500 line-clamp-1">
                {{ $form->department }}
              </div>

              <div class="my-2 flex justify-center">
                <img
                  class="w-32 h-32 object-contain bg-white rounded-lg border border-blue-100 p-1"
                  src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($urlFill) }}"
                  alt="QR Form {{ $form->title }}">
              </div>

              <div class="text-[10px] text-slate-400 break-all">
                {{ $urlFill }}
              </div>

              <div class="mt-2 text-[10px]">
                <span class="px-2 py-0.5 rounded-full bg-emerald-600 text-white font-semibold">
                  {{ strtoupper($form->status ?? 'ACTIVE') }}
                </span>
              </div>

              {{-- ACTIONS sesuai routes --}}
              <div class="mt-3 grid grid-cols-2 gap-1.5 text-[11px]">

                @if(\Illuminate\Support\Facades\Route::has('check_sheets.edit'))
                  <a href="{{ route('check_sheets.edit', $form) }}"
                     class="px-2 py-1 rounded-lg bg-white border border-blue-200 text-blue-700 hover:bg-blue-50 font-semibold transition">
                    Edit Form
                  </a>
                @endif

                @if(\Illuminate\Support\Facades\Route::has('check_sheets.fill'))
                  <a href="{{ $urlFill }}" target="_blank"
                     class="px-2 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold transition">
                    Buka QR Link
                  </a>
                @endif

                {{-- regen QR form --}}
                @if(\Illuminate\Support\Facades\Route::has('check_sheets.qr'))
                  <form method="POST" action="{{ route('check_sheets.qr', $form) }}">
                    @csrf
                    <button type="submit"
                      class="w-full px-2 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 hover:bg-amber-100 font-semibold transition">
                      Regenerate QR
                    </button>
                  </form>
                @endif

                {{-- publish/unpublish --}}
                @if(($form->status ?? null) !== 'active' && \Illuminate\Support\Facades\Route::has('check_sheets.publish'))
                  <form method="POST" action="{{ route('check_sheets.publish', $form) }}">
                    @csrf
                    <button type="submit"
                      class="w-full px-2 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 font-semibold transition">
                      Publish
                    </button>
                  </form>
                @elseif(($form->status ?? null) === 'active' && \Illuminate\Support\Facades\Route::has('check_sheets.unpublish'))
                  <form method="POST" action="{{ route('check_sheets.unpublish', $form) }}">
                    @csrf
                    <button type="submit"
                      class="w-full px-2 py-1 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 font-semibold transition">
                      Unpublish
                    </button>
                  </form>
                @endif

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
