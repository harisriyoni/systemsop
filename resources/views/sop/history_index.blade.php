@extends('layouts.app')
@section('title', 'History SOP')

@section('content')
@php
  $user = auth()->user();
  // Helper sederhana untuk cek role di view (sesuaikan dengan logic middleware role aplikasi Anda)
  // Misalnya admin dan produksi boleh edit/delete
  $canManage = $user->isRole(['admin', 'produksi']); 
  $isAdmin = $user->isRole(['admin']);
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
            <th class="px-3 py-2 text-center whitespace-nowrap w-48">Aksi</th>
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
              
              {{-- KOLOM AKSI --}}
              <td class="px-3 py-2 whitespace-nowrap text-center">
                <div class="flex items-center justify-center gap-2">
                  
                  {{-- 1. Tombol LIHAT (Semua Role) --}}
                  <a href="{{ route('sop.show', $sop) }}"
                     class="inline-flex items-center px-2 py-1 rounded-md
                            bg-slate-50 border border-slate-200 text-slate-600
                            hover:bg-slate-100 hover:text-slate-900 transition"
                     title="Lihat Detail">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </a>

                  @if($canManage)
                    {{-- 2. Tombol EDIT (Admin & Produksi) --}}
                    {{-- Ini akan mengarah ke SopController@edit -> update --}}
                    <a href="{{ route('sop.edit', $sop) }}"
                       class="inline-flex items-center px-2 py-1 rounded-md
                              bg-amber-50 border border-amber-200 text-amber-600
                              hover:bg-amber-100 hover:text-amber-700 transition"
                       title="Edit / Revisi">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                  @endif

                  @if($isAdmin)
                    {{-- 3. Tombol DELETE (Hanya Admin) --}}
                    {{-- Mengarah ke SopController@destroy --}}
                    <form action="{{ route('sop.destroy', $sop) }}" method="POST" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus SOP {{ $sop->code }}? Data tidak dapat dikembalikan.');"
                          class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center px-2 py-1 rounded-md
                                       bg-rose-50 border border-rose-200 text-rose-600
                                       hover:bg-rose-100 hover:text-rose-700 transition"
                                title="Hapus Permanen">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                  @endif

                  {{-- Tombol Link Tambahan (Versi/History Log) --}}
                  {{-- Bisa disembunyikan dalam dropdown jika terlalu penuh, atau dibiarkan icon --}}
                  @if(Route::has('sop.versions'))
                    <a href="{{ route('sop.versions', $sop) }}"
                       class="inline-flex items-center px-2 py-1 rounded-md
                              bg-indigo-50 border border-indigo-200 text-indigo-600
                              hover:bg-indigo-100 hover:text-indigo-700 transition"
                       title="Daftar Versi">
                       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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