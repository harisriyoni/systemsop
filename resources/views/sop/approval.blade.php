@extends('layouts.app')
@section('title', 'Approval SOP')

@section('content')
@php
  $role = $userRole ?? auth()->user()->role;

  // helper kecil buat cek apakah user masih bisa approve SOP ini
  $canApprove = function($sop) use ($role) {
    if ($role === 'admin') return true;
    if ($role === 'produksi') return !$sop->is_approved_produksi;
    if ($role === 'qa') return !$sop->is_approved_qa;
    if ($role === 'logistik') return !$sop->is_approved_logistik;
    return false;
  };

  // base reject route string (pakai placeholder :id) biar aman
  $rejectRouteTpl = \Route::has('sop.reject')
      ? route('sop.reject', ':id')
      : null;
@endphp

<div
  x-data="{
    openReject:false,
    rejectId:null,
    rejectTitle:'',
    rejectRouteTpl: @js($rejectRouteTpl),

    openRejectModal(id, title){
      this.rejectId = id;
      this.rejectTitle = title;
      this.openReject = true;
      this.$nextTick(()=> this.$refs.rejectReason?.focus());
    },

    rejectAction(){
      if(!this.rejectRouteTpl || !this.rejectId) return '#';
      return this.rejectRouteTpl.replace(':id', this.rejectId);
    }
  }"
  class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5"
>

  {{-- ================= HEADER ================= --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
    <div>
      <h2 class="text-base font-semibold text-slate-900">Approval SOP</h2>
      <p class="text-xs text-slate-500 mt-1">
        Role Anda:
        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-semibold">
          {{ strtoupper($role) }}
        </span>
      </p>
      <p class="text-[11px] text-slate-400 mt-1">
        Menampilkan SOP dengan status
        <span class="font-semibold text-blue-700">Menunggu Persetujuan</span>.
      </p>
    </div>

    <div class="text-xs text-slate-500">
      <div>Jumlah SOP di antrean:</div>
      <div class="text-right text-sm font-semibold text-blue-700">
        {{ $sops->total() }} SOP
      </div>
    </div>
  </div>

  {{-- ================= SEARCH ================= --}}
  <form method="GET" action="{{ route('sop.approval.index') }}"
        class="bg-blue-50/60 border border-blue-100 rounded-xl p-3 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
      <div class="md:col-span-3">
        <label class="block mb-1 text-slate-600">Cari SOP</label>
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Cari kode / judul SOP..."
               class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                      focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none">
      </div>
      <div class="flex items-end gap-2">
        <button
          class="inline-flex items-center justify-center w-full px-4 py-2 rounded-lg
                 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition">
          Terapkan
        </button>
        <a href="{{ route('sop.approval.index') }}"
           class="inline-flex items-center justify-center w-full px-4 py-2 rounded-lg
                  bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
          Reset
        </a>
      </div>
    </div>
  </form>

  {{-- ================= TABLE ================= --}}
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
          @php
            $photoCount  = is_array($sop->photos ?? null) ? count($sop->photos) : 0;
            $isCanApprove = $canApprove($sop);
          @endphp

          <tr class="hover:bg-blue-50/40 transition">
            {{-- KODE --}}
            <td class="px-4 py-3 align-top whitespace-nowrap">
              <div class="font-semibold text-slate-900 flex items-center gap-2">
                {{ $sop->code }}

                @if(!is_null($sop->version ?? null))
                  <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-50 border border-slate-200 text-slate-600">
                    v{{ $sop->version }}
                  </span>
                @endif
              </div>

              <div class="text-[11px] text-slate-400">
                Dibuat: {{ optional($sop->created_at)->format('d M Y') }}
              </div>

              <div class="mt-1 flex items-center gap-1.5 text-[11px]">
                <span class="px-2 py-0.5 rounded-full border
                  {{ $sop->is_public ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                  {{ $sop->is_public ? 'Publik' : 'Privat' }}
                </span>

                <span class="px-2 py-0.5 rounded-full border bg-white text-slate-600 border-slate-200">
                  {{ $photoCount }} Foto
                </span>

                @if($sop->pin)
                  <span class="px-2 py-0.5 rounded-full border bg-amber-50 text-amber-700 border-amber-200">
                    PIN
                  </span>
                @endif
              </div>
            </td>

            {{-- JUDUL --}}
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

              {{-- status sop --}}
              <div class="mt-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-semibold {{ $sop->status_badge_class }}">
                  {{ $sop->status_label }}
                </span>
              </div>
            </td>

            {{-- DEPARTEMEN --}}
            <td class="px-4 py-3 align-top whitespace-nowrap">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100 text-[11px] font-semibold">
                {{ $sop->department }}
              </span>
            </td>

            {{-- APPROVAL STATUS --}}
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

              @if(!$isCanApprove)
                <div class="mt-2 text-[11px] text-slate-400">
                  Anda sudah menyetujui bagian Anda.
                </div>
              @endif
            </td>

            {{-- AKSI --}}
            <td class="px-4 py-3 align-top whitespace-nowrap">
              <div class="flex flex-col items-start gap-2">

                {{-- Detail --}}
                <a href="{{ route('sop.show', $sop) }}"
                   class="inline-flex items-center px-3 py-1.5 rounded-lg
                          bg-white border border-blue-200 text-blue-700
                          hover:bg-blue-50 font-semibold text-[11px] transition">
                  Lihat Detail
                </a>

                {{-- Download PDF --}}
                @if(\Route::has('sop.download'))
                  <a href="{{ route('sop.download', $sop) }}"
                     class="inline-flex items-center px-3 py-1.5 rounded-lg
                            bg-white border border-slate-200 text-slate-700
                            hover:bg-slate-50 font-semibold text-[11px] transition">
                    Download PDF
                  </a>
                @endif

                {{-- Generate QR --}}
                @if(\Route::has('sop.qr'))
                  <form method="POST" action="{{ route('sop.qr', $sop) }}">
                    @csrf
                    <button
                      class="inline-flex items-center px-3 py-1.5 rounded-lg
                             bg-white border border-slate-200 text-slate-700
                             hover:bg-slate-50 font-semibold text-[11px] transition">
                      Generate QR
                    </button>
                  </form>
                @endif

                {{-- History / Versions --}}
                <div class="flex items-center gap-1">
                  @if(\Route::has('sop.history'))
                    <a href="{{ route('sop.history', $sop) }}"
                       class="inline-flex items-center px-2.5 py-1 rounded-lg
                              bg-blue-50 text-blue-700 border border-blue-100
                              hover:bg-blue-100 font-semibold text-[11px] transition">
                      History
                    </a>
                  @endif
                  @if(\Route::has('sop.versions'))
                    <a href="{{ route('sop.versions', $sop) }}"
                       class="inline-flex items-center px-2.5 py-1 rounded-lg
                              bg-blue-50 text-blue-700 border border-blue-100
                              hover:bg-blue-100 font-semibold text-[11px] transition">
                      Versions
                    </a>
                  @endif
                </div>

                {{-- Approve --}}
                @if(\Route::has('sop.approve'))
                  <form method="POST" action="{{ route('sop.approve', $sop) }}">
                    @csrf
                    <button
                      class="inline-flex items-center px-3 py-1.5 rounded-lg
                             {{ $isCanApprove ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-slate-200 text-slate-500 cursor-not-allowed' }}
                             font-semibold text-[11px] shadow-sm transition"
                      {{ $isCanApprove ? '' : 'disabled' }}
                      onclick="return confirm('Yakin menyetujui SOP ini?');"
                    >
                      {{ $isCanApprove ? 'Setujui SOP' : 'Sudah Disetujui' }}
                    </button>
                  </form>
                @endif

                {{-- Reject --}}
                @if(\Route::has('sop.reject') && $isCanApprove)
                  <button
                    type="button"
                    @click="openRejectModal('{{ $sop->id }}','{{ $sop->code }} - {{ addslashes($sop->title) }}')"
                    class="inline-flex items-center px-3 py-1.5 rounded-lg
                           bg-rose-50 border border-rose-200 text-rose-700
                           hover:bg-rose-100 font-semibold text-[11px] transition"
                  >
                    Tolak SOP
                  </button>
                @endif

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


  {{-- ================= MODAL REJECT ================= --}}
  <div
    x-show="openReject"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center px-3"
    style="display:none;"
  >
    <div class="absolute inset-0 bg-black/40" @click="openReject=false"></div>

    <div class="relative w-full max-w-lg bg-white rounded-2xl border border-blue-100 shadow-2xl overflow-hidden">
      <div class="bg-gradient-to-r from-rose-600 to-rose-500 text-white px-5 py-4">
        <div class="flex items-start justify-between">
          <div>
            <div class="text-sm font-semibold">Tolak SOP</div>
            <div class="text-xs text-rose-100 mt-1" x-text="rejectTitle"></div>
          </div>
          <button class="p-1.5 rounded-lg hover:bg-white/10" @click="openReject=false">✕</button>
        </div>
      </div>

      <form method="POST"
            :action="rejectAction()"
            class="p-5 space-y-3">
        @csrf

        <div>
          <label class="block text-xs text-slate-600 mb-1">Alasan Penolakan (opsional tapi disarankan)</label>
          <textarea x-ref="rejectReason" name="reason" rows="4"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none
                           focus:ring-rose-100 focus:border-rose-500"
                    placeholder="Contoh: revisi langkah safety, lampiran kurang jelas, dsb..."></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button type="button"
                  @click="openReject=false"
                  class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
            Batal
          </button>
          <button
            class="px-5 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm">
            Tolak SOP
          </button>
        </div>
      </form>
    </div>
  </div>
  {{-- =============== END MODAL =============== --}}

</div>
@endsection
