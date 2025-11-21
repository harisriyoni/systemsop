@extends('layouts.app')
@section('title', 'Approval SOP')

@section('content')
<div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">

  {{-- HEADER --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
    <div>
      <h2 class="text-base font-semibold text-slate-900">Approval SOP</h2>
      <p class="text-xs text-slate-500">
        Role Anda:
        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-semibold">
          {{ strtoupper($userRole) }}
        </span>
      </p>
      <p class="text-[11px] text-slate-400 mt-1">
        Hanya menampilkan SOP dengan status <span class="font-semibold text-blue-700">Menunggu Persetujuan</span>.
      </p>
    </div>

    <div class="text-xs text-slate-500">
      <div>Jumlah SOP di antrean:</div>
      <div class="text-right text-sm font-semibold text-blue-700">
        {{ $sops->total() }} SOP
      </div>
    </div>
  </div>

  {{-- TABEL APPROVAL --}}
  <div class="overflow-x-auto rounded-xl border border-blue-100">
    <table class="min-w-full text-xs bg-white">
      <thead class="bg-blue-50 text-blue-700 text-[11px] uppercase tracking-wider">
        <tr>
          <th class="px-4 py-3 text-left whitespace-nowrap">Kode</th>
          <th class="px-4 py-3 text-left">Judul & Info</th>
          <th class="px-4 py-3 text-left whitespace-nowrap">Departemen</th>
          <th class="px-4 py-3 text-left whitespace-nowrap">Status Persetujuan</th>
          <th class="px-4 py-3 text-left whitespace-nowrap">Aksi</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-blue-50">
        @forelse ($sops as $sop)
          <tr class="hover:bg-blue-50/40 transition">
            {{-- KODE --}}
            <td class="px-4 py-3 align-top whitespace-nowrap">
              <div class="font-semibold text-slate-900">{{ $sop->code }}</div>
              <div class="text-[11px] text-slate-400">
                Dibuat: {{ optional($sop->created_at)->format('d M Y') }}
              </div>
            </td>

            {{-- JUDUL + PRODUK/LINE --}}
            <td class="px-4 py-3 align-top">
              <div class="font-medium text-slate-900">{{ $sop->title }}</div>
              <div class="mt-1 flex flex-wrap gap-1 text-[11px] text-slate-500">
                @if($sop->product)
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-50 border border-slate-200">
                    Produk: {{ $sop->product }}
                  </span>
                @endif
                @if($sop->line)
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-50 border border-slate-200">
                    Line: {{ $sop->line }}
                  </span>
                @endif
              </div>
            </td>

            {{-- DEPARTEMEN --}}
            <td class="px-4 py-3 align-top whitespace-nowrap">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100 text-[11px] font-semibold">
                {{ $sop->department }}
              </span>
            </td>

            {{-- STATUS APPROVAL PER DEPARTEMEN --}}
            <td class="px-4 py-3 align-top">
              <div class="grid grid-cols-1 gap-1 text-[11px]">
                <div class="flex items-center justify-between rounded-lg border px-2 py-1
                  {{ $sop->is_approved_produksi ? 'border-emerald-100 bg-emerald-50' : 'border-slate-200 bg-slate-50' }}">
                  <span class="text-slate-600">Produksi</span>
                  <span class="font-semibold {{ $sop->is_approved_produksi ? 'text-emerald-700' : 'text-slate-500' }}">
                    {{ $sop->is_approved_produksi ? '✔ Disetujui' : 'Menunggu' }}
                  </span>
                </div>

                <div class="flex items-center justify-between rounded-lg border px-2 py-1
                  {{ $sop->is_approved_qa ? 'border-emerald-100 bg-emerald-50' : 'border-slate-200 bg-slate-50' }}">
                  <span class="text-slate-600">QA</span>
                  <span class="font-semibold {{ $sop->is_approved_qa ? 'text-emerald-700' : 'text-slate-500' }}">
                    {{ $sop->is_approved_qa ? '✔ Disetujui' : 'Menunggu' }}
                  </span>
                </div>

                <div class="flex items-center justify-between rounded-lg border px-2 py-1
                  {{ $sop->is_approved_logistik ? 'border-emerald-100 bg-emerald-50' : 'border-slate-200 bg-slate-50' }}">
                  <span class="text-slate-600">Logistik</span>
                  <span class="font-semibold {{ $sop->is_approved_logistik ? 'text-emerald-700' : 'text-slate-500' }}">
                    {{ $sop->is_approved_logistik ? '✔ Disetujui' : 'Menunggu' }}
                  </span>
                </div>
              </div>
            </td>

            {{-- AKSI --}}
            <td class="px-4 py-3 align-top whitespace-nowrap">
              <div class="flex flex-col items-start gap-2">
                <a href="{{ route('sop.show', $sop) }}"
                   class="inline-flex items-center px-3 py-1.5 rounded-lg
                          bg-white border border-blue-200 text-blue-700
                          hover:bg-blue-50 font-semibold text-[11px] transition">
                  Lihat Detail
                </a>

                <form method="POST" action="{{ route('sop.approve', $sop) }}">
                  @csrf
                  <button
                    class="inline-flex items-center px-3 py-1.5 rounded-lg
                           bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[11px] shadow-sm transition"
                    onclick="return confirm('Yakin menyetujui SOP ini?');"
                  >
                    Setujui SOP
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-10 text-center">
              <div class="text-sm font-semibold text-slate-700 mb-1">
                Tidak ada SOP yang perlu di-approve.
              </div>
              <div class="text-xs text-slate-400">
                Semua SOP sudah diproses atau belum ada pengajuan baru.
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINATION --}}
  <div class="mt-4">
    {{ $sops->appends(request()->query())->links() }}
  </div>
</div>
@endsection
