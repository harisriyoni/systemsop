@extends('layouts.app')
@section('title', 'Versi SOP')

@section('content')
@php
  $user = auth()->user();

  // SOP yang lagi kamu buka sekarang
  $currentId = $sop->id;

  // helper kecil
  $photoCount = fn($x) => is_array($x->photos ?? null) ? count($x->photos) : 0;
@endphp

<div class="space-y-4">

  {{-- ===== HEADER ===== --}}
  <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h2 class="text-base font-semibold text-slate-900">
          Riwayat Versi SOP
        </h2>
        <p class="text-xs text-slate-500 mt-1">
          Kode SOP:
          <span class="font-semibold text-blue-700">{{ $sop->code }}</span>
          • Total versi: <span class="font-semibold">{{ $versions->count() }}</span>
        </p>
        <p class="text-[11px] text-slate-400 mt-1">
          Ini adalah daftar semua versi SOP dengan kode yang sama.
        </p>
      </div>

      <div class="flex items-center gap-2">
        @if(Route::has('sop.show'))
          <a href="{{ route('sop.show', $sop) }}"
             class="inline-flex items-center px-4 py-2 rounded-xl
                    bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition">
            Kembali ke Detail
          </a>
        @endif
      </div>
    </div>
  </div>

  {{-- ===== LIST / TABLE VERSIONS ===== --}}
  <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-xs bg-white">
        <thead class="bg-blue-50 text-blue-700 text-[11px] uppercase tracking-wider">
          <tr>
            <th class="px-4 py-3 text-left whitespace-nowrap">Versi</th>
            <th class="px-4 py-3 text-left">Judul & Info</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Status</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Effective</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Updated</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-blue-50">
          @forelse($versions as $v)
            @php
              $isCurrent = $v->id === $currentId;
            @endphp

            <tr class="hover:bg-blue-50/40 transition {{ $isCurrent ? 'bg-blue-50/30' : '' }}">
              {{-- VERSI --}}
              <td class="px-4 py-3 whitespace-nowrap align-top">
                <div class="font-semibold text-slate-900 flex items-center gap-2">
                  v{{ $v->version ?? 1 }}

                  @if($isCurrent)
                    <span class="px-2 py-0.5 rounded-full text-[10px]
                                 bg-blue-600 text-white border border-blue-600">
                      Versi Aktif
                    </span>
                  @endif
                </div>

                <div class="text-[11px] text-slate-400 mt-1">
                  ID: {{ $v->id }}
                </div>
              </td>

              {{-- JUDUL & META --}}
              <td class="px-4 py-3 align-top">
                <div class="font-medium text-slate-900">{{ $v->title }}</div>

                <div class="text-[11px] text-slate-500 mt-1 flex flex-wrap gap-1">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-50 border border-slate-200">
                    Dept: {{ $v->department }}
                  </span>

                  @if($v->product)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-50 border border-slate-200">
                      Produk: {{ $v->product }}
                    </span>
                  @endif

                  @if($v->line)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-50 border border-slate-200">
                      Line: {{ $v->line }}
                    </span>
                  @endif
                </div>

                <div class="mt-2 flex items-center gap-1.5 text-[11px]">
                  <span class="px-2 py-0.5 rounded-full border
                    {{ $v->is_public ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                    {{ $v->is_public ? 'Publik' : 'Privat' }}
                  </span>

                  <span class="px-2 py-0.5 rounded-full border bg-white text-slate-600 border-slate-200">
                    {{ $photoCount($v) }} Foto
                  </span>

                  @if($v->pin)
                    <span class="px-2 py-0.5 rounded-full border bg-amber-50 text-amber-700 border-amber-200">
                      PIN
                    </span>
                  @endif
                </div>
              </td>

              {{-- STATUS --}}
              <td class="px-4 py-3 whitespace-nowrap align-top">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full border
                             text-[11px] font-semibold {{ $v->status_badge_class }}">
                  {{ $v->status_label }}
                </span>

                <div class="mt-2 grid gap-1 text-[11px] text-slate-500">
                  <div>
                    Produksi:
                    <span class="font-semibold {{ $v->is_approved_produksi ? 'text-emerald-700' : 'text-slate-500' }}">
                      {{ $v->is_approved_produksi ? '✔' : '•' }}
                    </span>
                  </div>
                  <div>
                    QA:
                    <span class="font-semibold {{ $v->is_approved_qa ? 'text-emerald-700' : 'text-slate-500' }}">
                      {{ $v->is_approved_qa ? '✔' : '•' }}
                    </span>
                  </div>
                  <div>
                    Logistik:
                    <span class="font-semibold {{ $v->is_approved_logistik ? 'text-emerald-700' : 'text-slate-500' }}">
                      {{ $v->is_approved_logistik ? '✔' : '•' }}
                    </span>
                  </div>
                </div>
              </td>

              {{-- EFFECTIVE DATE --}}
              <td class="px-4 py-3 whitespace-nowrap align-top text-slate-700">
                <div>
                  Mulai:
                  <span class="font-semibold">
                    {{ $v->effective_from ? $v->effective_from->format('d M Y') : '-' }}
                  </span>
                </div>
                <div class="mt-1">
                  Sampai:
                  <span class="font-semibold">
                    {{ $v->effective_to ? $v->effective_to->format('d M Y') : '-' }}
                  </span>
                </div>
              </td>

              {{-- UPDATED --}}
              <td class="px-4 py-3 whitespace-nowrap align-top text-slate-600">
                {{ optional($v->updated_at)->format('d M Y H:i') }}
              </td>

              {{-- AKSI --}}
              <td class="px-4 py-3 whitespace-nowrap align-top">
                <div class="flex flex-col gap-2">

                  {{-- lihat versi --}}
                  @if(Route::has('sop.show'))
                    <a href="{{ route('sop.show', $v) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg
                              bg-white border border-blue-200 text-blue-700
                              hover:bg-blue-50 font-semibold text-[11px] transition">
                      Lihat Versi Ini
                    </a>
                  @endif

                  {{-- edit hanya untuk draft / waiting, admin/produksi --}}
                  @if(
                      Route::has('sop.edit') &&
                      $user->isRole(['admin','produksi']) &&
                      in_array($v->status, ['draft','waiting_approval'])
                  )
                    <a href="{{ route('sop.edit', $v) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg
                              bg-white border border-slate-200 text-slate-700
                              hover:bg-slate-50 font-semibold text-[11px] transition">
                      Edit Versi Ini
                    </a>
                  @endif

                  {{-- download pdf (kalau approved) --}}
                  @if(Route::has('sop.download') && $v->status === 'approved')
                    <a href="{{ route('sop.download', $v) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg
                              bg-white border border-slate-200 text-slate-700
                              hover:bg-slate-50 font-semibold text-[11px] transition">
                      Download PDF
                    </a>
                  @endif

                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-4 py-12 text-center">
                <div class="text-sm font-semibold text-slate-800 mb-1">
                  Belum ada versi lain
                </div>
                <div class="text-xs text-slate-400">
                  SOP ini baru memiliki satu versi.
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection
