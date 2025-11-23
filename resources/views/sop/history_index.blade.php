@extends('layouts.app')
@section('title', 'History SOP')

@section('content')
@php
  $user = auth()->user();
@endphp

<div class="space-y-4">

  {{-- HEADER --}}
  <div class="bg-white rounded-2xl border border-[#05727d]/20 shadow-sm p-5">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h2 class="text-base font-semibold text-slate-900">History SOP (Semua Perubahan)</h2>
        <p class="text-xs text-slate-500">
          Menampilkan {{ $items->count() }} dari {{ $items->total() }} record SOP.
        </p>
      </div>
    </div>

    {{-- SEARCH --}}
    <form method="GET" action="{{ route('sop.history.index') }}"
          class="mt-4 bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl p-3">
      <div class="grid grid-cols-1 md:grid-cols-6 gap-3 text-xs">
        <div class="md:col-span-3">
          <label class="block mb-1 text-slate-600">Kata Kunci</label>
          <input type="text" name="q" value="{{ request('q') }}"
                 placeholder="Cari kode / judul SOP..."
                 class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                        focus:ring-4 focus:ring-[#05727d]/15 focus:border-[#05727d] outline-none">
        </div>

        <div class="md:col-span-3 flex items-end justify-end gap-2">
          <button
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg
                   bg-[#05727d] hover:bg-[#05727d]/90 text-white font-semibold text-xs transition">
            Cari
          </button>
          <a href="{{ route('sop.history.index') }}"
             class="inline-flex items-center justify-center px-4 py-2 rounded-lg
                    bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
            Reset
          </a>
        </div>
      </div>
    </form>
  </div>


  {{-- TABLE --}}
  <div class="bg-white rounded-2xl border border-[#05727d]/20 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-xs">
        <thead class="bg-[#05727d]/5 text-[#05727d] text-[11px] uppercase tracking-wider">
          <tr>
            <th class="px-3 py-2 text-left whitespace-nowrap">Kode</th>
            <th class="px-3 py-2 text-left">Judul</th>
            <th class="px-3 py-2 text-left whitespace-nowrap">Dept</th>
            <th class="px-3 py-2 text-left whitespace-nowrap">Versi</th>
            <th class="px-3 py-2 text-left whitespace-nowrap">Status</th>
            <th class="px-3 py-2 text-left whitespace-nowrap">Updated</th>
            <th class="px-3 py-2 text-left whitespace-nowrap">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-[#05727d]/10">
          @forelse($items as $sop)
            <tr class="hover:bg-[#05727d]/5 transition">
              <td class="px-3 py-2 font-semibold text-slate-900 whitespace-nowrap">
                {{ $sop->code }}
              </td>
              <td class="px-3 py-2 text-slate-800">
                <div class="font-medium line-clamp-1">{{ $sop->title }}</div>
                <div class="text-[11px] text-slate-400">
                  Dibuat: {{ optional($sop->created_at)->format('d M Y') }}
                </div>
              </td>
              <td class="px-3 py-2 text-slate-700 whitespace-nowrap">
                {{ $sop->department ?? '-' }}
              </td>
              <td class="px-3 py-2 whitespace-nowrap">
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-50 border border-slate-200 text-slate-600">
                  v{{ $sop->version ?? 1 }}
                </span>
              </td>
              <td class="px-3 py-2 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-[10px] font-semibold {{ $sop->status_badge_class }}">
                  {{ $sop->status_label }}
                </span>
              </td>
              <td class="px-3 py-2 whitespace-nowrap text-slate-600">
                {{ optional($sop->updated_at)->format('d M Y H:i') }}
              </td>
              <td class="px-3 py-2 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <a href="{{ route('sop.show', $sop) }}"
                     class="inline-flex items-center px-3 py-1 rounded-lg
                            bg-white border border-[#05727d]/30 text-[#05727d]
                            hover:bg-[#05727d]/5 font-semibold text-[11px] transition">
                    Lihat
                  </a>

                  @if(Route::has('sop.versions'))
                    <a href="{{ route('sop.versions', $sop) }}"
                       class="inline-flex items-center px-3 py-1 rounded-lg
                              bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-[11px] transition">
                      Versi
                    </a>
                  @endif

                  @if(Route::has('sop.history'))
                    <a href="{{ route('sop.history', $sop) }}"
                       class="inline-flex items-center px-3 py-1 rounded-lg
                              bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-[11px] transition">
                      History
                    </a>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-3 py-10 text-center text-slate-500">
                Belum ada record SOP.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- PAGINATION --}}
  <div class="flex justify-end">
    {{ $items->appends(request()->query())->links() }}
  </div>

</div>
@endsection
