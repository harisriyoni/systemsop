@extends('layouts.app')

@section('title', 'Detail SOP '.$sop->code)

@section('content')
@php
  // --- Normalisasi photos biar aman ---
  $rawPhotos = $sop->photos ?? [];
  if (is_string($rawPhotos)) {
    $rawPhotos = json_decode($rawPhotos, true) ?: [];
  }

  $photos = [];
  foreach ($rawPhotos as $p) {
    if (is_string($p)) {
      $path = $p; $desc = null;
    } elseif (is_array($p)) {
      $path = $p['path'] ?? $p['url'] ?? $p['photo'] ?? null;
      $desc = $p['desc'] ?? $p['description'] ?? $p['keterangan'] ?? null;
    } else {
      $path = null; $desc = null;
    }

    if ($path) {
      $isHttp = \Illuminate\Support\Str::startsWith($path, ['http://','https://','//']);
      $url = $isHttp ? $path : \Illuminate\Support\Facades\Storage::url($path);
      $photos[] = ['url'=>$url, 'desc'=>$desc];
    }
  }

  // === Mapping status pakai warna brand teal ===
  $statusMap = [
    'draft' => [
      'label' => 'Draf',
      'cls'   => 'bg-slate-50 text-slate-700 border-slate-200',
    ],
    'waiting_approval' => [
      'label' => 'Menunggu Persetujuan',
      'cls'   => 'bg-[#05727d]/5 text-[#05727d] border-[#05727d]/40',
    ],
    'approved' => [
      'label' => 'Disetujui',
      'cls'   => 'bg-[#05727d] text-white border-[#05727d]',
    ],
    'expired' => [
      'label' => 'Kedaluwarsa',
      'cls'   => 'bg-slate-100 text-slate-500 border-slate-200',
    ],
  ];
  $st = $statusMap[$sop->status] ?? [
    'label' => $sop->status,
    'cls'   => 'bg-slate-50 text-slate-700 border-slate-200',
  ];

  $appr = [
    ['label'=>'Produksi', 'ok'=>$sop->is_approved_produksi],
    ['label'=>'QA',       'ok'=>$sop->is_approved_qa],
    ['label'=>'Logistik', 'ok'=>$sop->is_approved_logistik],
  ];

  // ===== URL QR (publik kalau ada routenya, kalau tidak fallback ke show biasa) =====
  $hasPublicRoute = \Illuminate\Support\Facades\Route::has('sop.public.show');
  $qrUrl = ($sop->is_public && $hasPublicRoute)
      ? route('sop.public.show', $sop)
      : route('sop.show', $sop);

  // ===== Meta / extra fields / builder schema =====
  $meta = $sop->meta ?? [];
  if (is_string($meta)) {
      $meta = json_decode($meta, true) ?: [];
  }

  // extra_fields = array of [label, value]
  $extraFields = [];
  if (!empty($meta['extra_fields']) && is_array($meta['extra_fields'])) {
      foreach ($meta['extra_fields'] as $row) {
          if (!is_array($row)) continue;
          $label = trim($row['label'] ?? '');
          $value = trim($row['value'] ?? '');
          if ($label === '' && $value === '') continue;
          $extraFields[] = [
              'label' => $label ?: '-',
              'value' => $value ?: '-',
          ];
      }
  }

  // builder_schema bisa disimpan di meta['builder_schema'] atau kolom langsung
  $builderSchema = $meta['builder_schema'] ?? ($sop->builder_schema ?? []);
  if (is_string($builderSchema)) {
      $builderSchema = json_decode($builderSchema, true) ?: [];
  }
  if (!is_array($builderSchema)) {
      $builderSchema = [];
  }
@endphp

<div class="space-y-5">

  {{-- ================= HEADER ================= --}}
  <div class="bg-white border border-[#05727d]/20 rounded-2xl shadow-sm overflow-hidden">
    <div class="bg-gradient-to-r from-[#05727d] to-[#0894a0] px-6 py-5 text-white">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex items-start gap-3">
          <div class="h-12 w-12 rounded-2xl bg-white/15 grid place-items-center shrink-0">
            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
            </svg>
          </div>
          <div>
            <div class="text-xs text-white/80 mb-0.5">Kode SOP</div>
            <div class="text-2xl font-semibold leading-tight">{{ $sop->code }}</div>
            <div class="text-sm text-white/90 mt-1">{{ $sop->title }}</div>

            <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px]">
              <span class="inline-flex items-center px-2.5 py-1 rounded-full border border-white/30 bg-white/10">
                Status: {{ $st['label'] }}
              </span>
              <span class="inline-flex items-center px-2.5 py-1 rounded-full border border-white/30 bg-white/10">
                {{ $sop->is_public ? 'Publik' : 'Privat' }}
              </span>
              @if($sop->pin)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full border border-white/30 bg-white/10">
                  PIN: {{ $sop->pin }}
                </span>
              @endif
            </div>
          </div>
        </div>

        <div class="md:text-right">
          <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-semibold border {{ $st['cls'] }}">
            {{ strtoupper($st['label']) }}
          </span>
          <div class="text-xs text-white/80 mt-2">
            Dibuat oleh: <span class="font-semibold text-white">{{ $sop->creator->name ?? '-' }}</span>
          </div>
        </div>
      </div>
    </div>

    {{-- RINGKASAN CEPAT --}}
    <div class="px-6 py-4">
      <div class="grid grid-cols-2 md:grid-cols-6 gap-3 text-xs">
        <div class="bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl px-3 py-2">
          <div class="text-slate-500 mb-0.5">Departemen</div>
          <div class="font-semibold text-slate-900 truncate">{{ $sop->department }}</div>
        </div>
        <div class="bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl px-3 py-2">
          <div class="text-slate-500 mb-0.5">Produk</div>
          <div class="font-semibold text-slate-900 truncate">{{ $sop->product ?: '-' }}</div>
        </div>
        <div class="bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl px-3 py-2">
          <div class="text-slate-500 mb-0.5">Line Produksi</div>
          <div class="font-semibold text-slate-900 truncate">{{ $sop->line ?: '-' }}</div>
        </div>
        <div class="bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl px-3 py-2">
          <div class="text-slate-500 mb-0.5">Efektif Dari</div>
          <div class="font-semibold text-slate-900">
            {{ $sop->effective_from?->format('d M Y') ?? '-' }}
          </div>
        </div>
        <div class="bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl px-3 py-2">
          <div class="text-slate-500 mb-0.5">Efektif Sampai</div>
          <div class="font-semibold text-slate-900">
            {{ $sop->effective_to?->format('d M Y') ?? '-' }}
          </div>
        </div>
        <div class="bg-white border border-[#05727d]/20 rounded-xl px-3 py-2">
          <div class="text-slate-500 mb-0.5">Jumlah Foto</div>
          <div class="font-semibold text-slate-900">{{ count($photos) }} Foto</div>
        </div>
      </div>
    </div>
  </div>

  {{-- ================= FOTO + APPROVAL ================= --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- FOTO CAROUSEL --}}
    <div class="lg:col-span-2 bg-white border border-[#05727d]/20 rounded-2xl shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <div class="text-sm font-semibold text-slate-900">Foto SOP / Lampiran</div>
        <div class="text-xs text-slate-500">{{ count($photos) }} foto</div>
      </div>

      @if(count($photos))
        <div x-data="{ idx:0, total: {{ count($photos) }} }" class="space-y-3">

          {{-- Main Slide --}}
          <div class="relative border border-[#05727d]/20 rounded-xl overflow-hidden bg-slate-50">
            <div class="aspect-video md:aspect-[16/7] grid place-items-center bg-white">
              <template x-for="(p, i) in {{ json_encode($photos) }}" :key="i">
                <div x-show="idx===i" x-transition.opacity class="w-full h-full">
                  <img :src="p.url" class="w-full h-full object-contain bg-white" alt="">
                </div>
              </template>
            </div>

            {{-- Prev/Next --}}
            <button type="button"
              class="absolute left-3 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-white/95 border border-[#05727d]/30 shadow grid place-items-center hover:bg-white text-[#05727d] text-lg"
              @click="idx=(idx-1+total)%total" aria-label="Sebelumnya">‹</button>

            <button type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-white/95 border border-[#05727d]/30 shadow grid place-items-center hover:bg-white text-[#05727d] text-lg"
              @click="idx=(idx+1)%total" aria-label="Berikutnya">›</button>

            {{-- Counter --}}
            <div class="absolute bottom-3 right-3 px-2 py-1 rounded-lg bg-black/60 text-white text-[11px]">
              <span x-text="idx+1"></span>/<span x-text="total"></span>
            </div>
          </div>

          {{-- Deskripsi --}}
          <div class="text-xs text-slate-700 bg-[#05727d]/5 border border-[#05727d]/20 rounded-lg px-3 py-2">
            <template x-for="(p, i) in {{ json_encode($photos) }}" :key="'d'+i">
              <div x-show="idx===i" x-transition.opacity>
                <span class="text-slate-500">Keterangan:</span>
                <span x-text="p.desc || '-'"></span>
              </div>
            </template>
          </div>

          {{-- Thumbs --}}
          <div class="flex gap-2 overflow-x-auto pb-1">
            <template x-for="(p, i) in {{ json_encode($photos) }}" :key="'t'+i">
              <button type="button"
                class="shrink-0 rounded-lg border overflow-hidden w-24 h-16 md:w-28 md:h-20"
                :class="idx===i
                  ? 'border-[#05727d] ring-2 ring-[#05727d]/30'
                  : 'border-[#05727d]/20 hover:border-[#05727d]/60'"
                @click="idx=i">
                <img :src="p.url" class="w-full h-full object-cover" alt="">
              </button>
            </template>
          </div>

        </div>
      @else
        <div class="h-52 grid place-items-center text-slate-400 text-sm bg-white border border-[#05727d]/20 rounded-xl">
          Belum ada foto SOP
        </div>
      @endif
    </div>

    {{-- APPROVAL CARD + QR --}}
    <div class="bg-white border border-[#05727d]/20 rounded-2xl shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <div class="text-sm font-semibold text-slate-900">Status Persetujuan</div>
        <div class="text-[11px] text-slate-500">Flow 3 Departemen</div>
      </div>

      <div class="space-y-2">
        @foreach($appr as $a)
          <div class="flex items-center justify-between rounded-xl border border-[#05727d]/25 px-3 py-2 bg-[#05727d]/5">
            <div class="text-slate-700 font-medium">{{ $a['label'] }}</div>
            @if($a['ok'])
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#05727d] text-white text-[11px] font-semibold">
                ✔ Disetujui
              </span>
            @else
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white border border-[#05727d]/30 text-[#05727d] text-[11px] font-semibold">
                Menunggu
              </span>
            @endif
          </div>
        @endforeach
      </div>

      <div class="mt-4 text-xs text-slate-500 bg-white border border-[#05727d]/20 rounded-lg px-3 py-2">
        SOP akan otomatis <span class="font-semibold text-[#05727d]">Disetujui</span> jika Produksi, QA, dan Logistik sudah approve.
      </div>

      {{-- QR SOP --}}
      @if($sop->status === 'approved')
        <div class="mt-4 border-t border-[#05727d]/20 pt-4">
          <div class="text-sm font-semibold text-slate-900 mb-2">QR SOP (Display Operator)</div>

          <div class="border border-[#05727d]/20 rounded-xl p-3 text-center text-xs bg-white">
            <div class="font-semibold text-slate-700 mb-1">{{ $sop->code }}</div>
            <div class="text-[11px] text-slate-500 mb-2">{{ $sop->title }}</div>

            @if(class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class))
              <div class="flex justify-center mb-2">
                {!! \SimpleSoftwareIO\QrCode::size(150)->margin(1)->generate($qrUrl) !!}
              </div>
            @else
              <img
                class="mx-auto mb-2 w-36 h-36"
                src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($qrUrl) }}"
                alt="QR SOP {{ $sop->code }}">
            @endif

            <div class="text-[10px] text-slate-400 break-all">{{ $qrUrl }}</div>

            @if($sop->is_public)
              <div class="mt-2 text-[11px] text-slate-500">
                Akses publik via QR
                @if($sop->pin) (butuh PIN) @endif
              </div>
            @else
              <div class="mt-2 text-[11px] text-slate-500">
                Akses internal (butuh login)
              </div>
            @endif
          </div>
        </div>
      @else
        <div class="mt-4 border-t border-[#05727d]/20 pt-4">
          <div class="text-sm font-semibold text-slate-900 mb-2">QR SOP</div>

          <div class="rounded-xl border border-dashed border-[#05727d]/30 bg-[#05727d]/5 p-4 flex items-center gap-3">
            <div class="h-12 w-12 rounded-xl bg-white border border-[#05727d]/30 grid place-items-center text-[#05727d]">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 11V7a4 4 0 10-8 0v4m0 0h8m-8 0a2 2 0 00-2 2v4a2 2 0 002 2h8a2 2 0 002-2v-4a2 2 0 00-2-2"/>
              </svg>
            </div>
            <div class="text-xs text-slate-700">
              <div class="font-semibold text-slate-900">QR masih dikunci</div>
              <div class="text-slate-500 mt-0.5">
                QR akan muncul otomatis setelah SOP berstatus
                <span class="font-semibold text-[#05727d]">Disetujui</span>.
              </div>
            </div>
          </div>
        </div>
      @endif
    </div>

  </div>

  {{-- ================= INFORMASI TAMBAHAN + STRUKTUR ================= --}}
  @if(count($extraFields) || count($builderSchema))
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      {{-- INFORMASI TAMBAHAN --}}
      <div class="bg-white border border-[#05727d]/20 rounded-2xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-3">
          <div class="text-sm font-semibold text-slate-900">Informasi Tambahan</div>
          <div class="text-xs text-slate-500">Dari form builder</div>
        </div>

        @if(count($extraFields))
          <div class="border border-slate-200 rounded-xl overflow-hidden text-xs">
            <table class="min-w-full divide-y divide-slate-200">
              <tbody class="divide-y divide-slate-200">
                @foreach($extraFields as $row)
                  <tr class="bg-white">
                    <td class="px-3 py-2 font-medium text-slate-700 w-40">{{ $row['label'] }}</td>
                    <td class="px-3 py-2 text-slate-800">{{ $row['value'] }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-xs text-slate-500">
            Tidak ada informasi tambahan untuk SOP ini.
          </div>
        @endif
      </div>

      {{-- STRUKTUR SOP / CHECK SHEET PREVIEW --}}
      <div class="bg-white border border-[#05727d]/20 rounded-2xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-3">
          <div class="text-sm font-semibold text-slate-900">Struktur SOP (Preview Check Sheet)</div>
          <div class="text-xs text-slate-500">Template dari builder</div>
        </div>

        @if(count($builderSchema))
          <div class="space-y-3 text-xs">
            @foreach($builderSchema as $section)
              @php
                $secName = $section['name'] ?? 'Section';
                $items   = $section['items'] ?? [];
                if (!is_array($items)) {
                    $items = [];
                }
              @endphp
              <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/50">
                <div class="font-semibold text-slate-800 mb-2">{{ $secName }}</div>

                @if(count($items))
                  <ul class="space-y-1">
                    @foreach($items as $item)
                      @php
                        $label    = $item['label'] ?? '-';
                        $type     = $item['type'] ?? 'checkbox';
                        $required = !empty($item['required']);

                        // Tanpa match biar simpel
                        if ($type === 'number') {
                            $typeLabel = 'Angka';
                        } elseif ($type === 'text') {
                            $typeLabel = 'Teks';
                        } else {
                            $typeLabel = 'Checklist';
                        }
                      @endphp
                      <li class="flex items-start gap-2">
                        <span class="mt-[3px] h-3.5 w-3.5 rounded border border-slate-400 inline-block"></span>
                        <div>
                          <div class="text-slate-800">{{ $label }}</div>
                          <div class="text-[10px] text-slate-500">
                            Tipe: {{ $typeLabel }}
                            @if($required)
                              • Wajib
                            @endif
                          </div>
                        </div>
                      </li>
                    @endforeach
                  </ul>
                @else
                  <div class="text-[11px] text-slate-500">
                    Belum ada item dalam section ini.
                  </div>
                @endif
              </div>
            @endforeach
          </div>
        @else
          <div class="text-xs text-slate-500">
            Belum ada struktur builder untuk SOP ini.
          </div>
        @endif
      </div>
    </div>
  @endif

  {{-- ================= ISI SOP ================= --}}
  <div class="bg-white border border-[#05727d]/20 rounded-2xl shadow-sm p-5">
    <div class="flex items-center justify-between mb-3">
      <div class="text-sm font-semibold text-slate-900">Isi SOP</div>
      <div class="text-xs text-slate-500">Dokumen Prosedur</div>
    </div>

    <div class="prose prose-sm max-w-none">
      {!! nl2br(e($sop->content)) !!}
    </div>
  </div>

  {{-- FOOTER --}}
  <div class="flex justify-between items-center text-xs">
    <a href="{{ route('sop.index') }}"
       class="inline-flex items-center gap-2 text-[#05727d] hover:underline font-semibold">
      ← Kembali ke Daftar SOP
    </a>
  </div>

</div>
@endsection
