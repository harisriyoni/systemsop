@extends('layouts.app')
@section('title', 'Template SOP')

@section('content')
@php
  $brand = '#05727d';
@endphp

<div class="max-w-7xl mx-auto space-y-6">

  {{-- ===== HEADER / HERO ===== --}}
  <div class="bg-white rounded-3xl ring-1 ring-slate-200 p-5 md:p-7">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div class="flex items-start gap-4">
        <div class="shrink-0 w-12 h-12 rounded-2xl bg-[{{ $brand }}]/10 text-[{{ $brand }}] flex items-center justify-center">
          {{-- icon doc --}}
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12h6m-6 4h6M7 4h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900">
            Template SOP
          </h1>
          <p class="text-sm text-slate-500 mt-1">
            Kelola template untuk dipakai saat membuat SOP baru.
          </p>

          <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 ring-1 ring-slate-200">
              Total template: {{ $templates->total() }}
            </span>
            @if(request('q') || request('department') || request('active') !== null)
              <span class="px-2.5 py-1 rounded-full bg-[{{ $brand }}]/10 text-[{{ $brand }}] ring-1 ring-[{{ $brand }}]/20">
                Filter aktif
              </span>
            @endif
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('sop.templates.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-[#05727d] hover:bg-[#045e68] text-white font-semibold text-sm shadow-sm active:scale-[.98] transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          Buat Template
        </a>
      </div>
    </div>
  </div>

  {{-- ===== FLASH ===== --}}
  @if (session('success'))
    <div class="p-3 rounded-2xl bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200">
      {{ session('success') }}
    </div>
  @endif
  @if (session('error'))
    <div class="p-3 rounded-2xl bg-rose-50 text-rose-800 ring-1 ring-rose-200">
      {{ session('error') }}
    </div>
  @endif

  {{-- ===== FILTERS ===== --}}
  <form method="GET" class="bg-white p-4 md:p-5 rounded-3xl ring-1 ring-slate-200">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 md:gap-4 items-end">

      {{-- Search --}}
      <div class="md:col-span-5">
        <label class="text-xs font-semibold text-slate-600">Cari (nama/kode)</label>
        <div class="relative mt-1">
          <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </span>
          <input type="text" name="q" value="{{ request('q') }}"
                 class="w-full pl-10 pr-3 py-2.5 rounded-2xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-sm"
                 placeholder="contoh: mixing / TMP-001">
        </div>
      </div>

      {{-- Department --}}
      <div class="md:col-span-3">
        <label class="text-xs font-semibold text-slate-600">Departemen</label>
        <input type="text" name="department" value="{{ request('department') }}"
               class="mt-1 w-full rounded-2xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-sm py-2.5"
               placeholder="Produksi / HSE / QC">
      </div>

      {{-- Status --}}
      <div class="md:col-span-2">
        <label class="text-xs font-semibold text-slate-600">Status</label>
        <select name="active"
                class="mt-1 w-full rounded-2xl border-slate-300 focus:border-[#05727d] focus:ring-[#05727d] text-sm py-2.5">
          <option value="">Semua</option>
          <option value="1" {{ request('active')==='1' ? 'selected' : '' }}>Aktif</option>
          <option value="0" {{ request('active')==='0' ? 'selected' : '' }}>Nonaktif</option>
        </select>
      </div>

      {{-- Buttons --}}
      <div class="md:col-span-2 flex gap-2">
        <button class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 rounded-2xl bg-[#05727d] text-white text-sm font-semibold hover:bg-[#045e68] shadow-sm active:scale-[.98] transition">
          Filter
        </button>
        <a href="{{ route('sop.templates.index') }}"
           class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-2xl bg-slate-100 text-slate-700 text-sm hover:bg-slate-200 active:scale-[.98] transition">
          Reset
        </a>
      </div>

    </div>
  </form>


  {{-- ===== LIST (DESKTOP TABLE) ===== --}}
  <div class="hidden md:block bg-white rounded-3xl ring-1 ring-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600 sticky top-0 z-10">
          <tr>
            <th class="text-left p-4 font-semibold">Nama</th>
            <th class="text-left p-4 font-semibold">Kode</th>
            <th class="text-left p-4 font-semibold">Dept</th>
            <th class="text-left p-4 font-semibold">Product</th>
            <th class="text-left p-4 font-semibold">Line</th>
            <th class="text-left p-4 font-semibold">Status</th>
            <th class="text-right p-4 font-semibold">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-100">
          @forelse($templates as $t)
            <tr class="hover:bg-slate-50/70 transition">
              <td class="p-4">
                <div class="font-semibold text-slate-900">{{ $t->name }}</div>
                <div class="text-xs text-slate-500 mt-0.5">
                  Updated: {{ $t->updated_at?->format('d M Y H:i') }}
                </div>
              </td>
              <td class="p-4">
                <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold">
                  {{ $t->code ?? '-' }}
                </span>
              </td>
              <td class="p-4">{{ $t->department ?? '-' }}</td>
              <td class="p-4">{{ $t->product ?? '-' }}</td>
              <td class="p-4">{{ $t->line ?? '-' }}</td>
              <td class="p-4">
                @if($t->is_active)
                  <span class="px-2.5 py-1 rounded-full text-xs bg-emerald-100 text-emerald-700 font-semibold">
                    Aktif
                  </span>
                @else
                  <span class="px-2.5 py-1 rounded-full text-xs bg-slate-100 text-slate-600 font-semibold">
                    Nonaktif
                  </span>
                @endif
              </td>
              <td class="p-4 text-right">
                <div class="inline-flex gap-2">
                  {{-- VIEW --}}
                  <a href="{{ route('sop.templates.show', $t) }}"
                     class="px-3 py-2 rounded-xl bg-white text-[#05727d] text-xs font-semibold ring-1 ring-[#05727d]/30 hover:bg-[#05727d]/10 shadow-sm active:scale-[.98] transition">
                    View
                  </a>

                  {{-- EDIT --}}
                  <a href="{{ route('sop.templates.edit', $t) }}"
                     class="px-3 py-2 rounded-xl bg-[#05727d] text-white text-xs font-semibold hover:bg-[#045e68] shadow-sm active:scale-[.98] transition">
                    Edit
                  </a>

                  {{-- DELETE --}}
                  <form action="{{ route('sop.templates.destroy',$t) }}" method="POST"
                        onsubmit="return confirm('Hapus template ini?');">
                    @csrf @method('DELETE')
                    <button class="px-3 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 shadow-sm active:scale-[.98] transition">
                      Hapus
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="p-8 text-center text-slate-500">
                <div class="flex flex-col items-center gap-2">
                  <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 13h6m-6 4h6M7 4h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>
                    </svg>
                  </div>
                  <div class="font-semibold">Belum ada template</div>
                  <div class="text-xs">Klik tombol <b>Buat Template</b> untuk mulai.</div>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ===== LIST (MOBILE CARDS) ===== --}}
  <div class="md:hidden space-y-3">
    @forelse($templates as $t)
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 p-4">
        <div class="flex items-start justify-between gap-3">
          <div>
            <div class="font-semibold text-slate-900">{{ $t->name }}</div>
            <div class="text-xs text-slate-500 mt-0.5">
              {{ $t->code ?? '-' }} • {{ $t->department ?? '-' }}
            </div>
          </div>
          @if($t->is_active)
            <span class="px-2.5 py-1 rounded-full text-xs bg-emerald-100 text-emerald-700 font-semibold">Aktif</span>
          @else
            <span class="px-2.5 py-1 rounded-full text-xs bg-slate-100 text-slate-600 font-semibold">Nonaktif</span>
          @endif
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-600">
          <div class="rounded-xl bg-slate-50 p-2">
            <div class="text-[10px] text-slate-400">Product</div>
            <div class="font-semibold text-slate-800">{{ $t->product ?? '-' }}</div>
          </div>
          <div class="rounded-xl bg-slate-50 p-2">
            <div class="text-[10px] text-slate-400">Line</div>
            <div class="font-semibold text-slate-800">{{ $t->line ?? '-' }}</div>
          </div>
        </div>

        <div class="mt-3 text-[11px] text-slate-500">
          Updated: {{ $t->updated_at?->format('d M Y H:i') }}
        </div>

        <div class="mt-3 flex gap-2">
          {{-- VIEW --}}
          <a href="{{ route('sop.templates.show', $t) }}"
             class="flex-1 px-3 py-2 rounded-xl bg-white text-[#05727d] text-xs font-semibold text-center ring-1 ring-[#05727d]/30 hover:bg-[#05727d]/10 active:scale-[.98] transition">
            View
          </a>

          {{-- EDIT --}}
          <a href="{{ route('sop.templates.edit', $t) }}"
             class="flex-1 px-3 py-2 rounded-xl bg-[#05727d] text-white text-xs font-semibold text-center hover:bg-[#045e68] active:scale-[.98] transition">
            Edit
          </a>

          {{-- DELETE --}}
          <form class="flex-1" action="{{ route('sop.templates.destroy',$t) }}" method="POST"
                onsubmit="return confirm('Hapus template ini?');">
            @csrf @method('DELETE')
            <button class="w-full px-3 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 active:scale-[.98] transition">
              Hapus
            </button>
          </form>
        </div>
      </div>
    @empty
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 p-6 text-center text-slate-500">
        Belum ada template.
      </div>
    @endforelse
  </div>

  {{-- ===== PAGINATION ===== --}}
  <div class="pt-1">
    {{ $templates->links() }}
  </div>

</div>
@endsection
