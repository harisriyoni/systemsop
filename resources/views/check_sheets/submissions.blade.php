@extends('layouts.app')
@section('title', 'Submission Check Sheet')

@section('content')
@php
  $user = auth()->user();
  $canReview = $user->isRole(['admin','qa','logistik']); // sesuai kode kamu
@endphp

<div class="max-w-6xl mx-auto space-y-4">

  {{-- ================= HEADER ================= --}}
  <div class="bg-white border border-blue-100 rounded-2xl shadow-sm overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-5 text-white">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

        <div class="flex items-start gap-3">
          <div class="h-11 w-11 rounded-2xl bg-white/15 grid place-items-center shrink-0">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
          </div>
          <div>
            <div class="text-xs text-blue-100">Monitoring</div>
            <div class="text-2xl font-semibold leading-tight">
              Daftar Submission Check Sheet
            </div>
            <div class="text-sm text-blue-50/90 mt-1">
              Total: {{ $submissions->total() }} submission
            </div>
          </div>
        </div>

        {{-- Filter status --}}
        <form method="GET"
              action="{{ Route::has('check_sheets.submissions') ? route('check_sheets.submissions') : url()->current() }}"
              class="flex items-center gap-2 text-xs">
          <select name="status"
                  class="rounded-lg border border-white/30 bg-white/10 text-white px-3 py-2 outline-none
                         focus:ring-2 focus:ring-white/30">
            <option value="">Semua Status</option>
            <option value="submitted" {{ request('status')=='submitted'?'selected':'' }}>Submitted</option>
            <option value="under_review" {{ request('status')=='under_review'?'selected':'' }}>Under Review</option>
            <option value="approved" {{ request('status')=='approved'?'selected':'' }}>Approved</option>
            <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Rejected</option>
          </select>
          <button class="px-3 py-2 rounded-lg bg-white text-blue-700 font-semibold hover:bg-blue-50 transition">
            Terapkan
          </button>
        </form>

      </div>
    </div>
  </div>

  {{-- ================= TABLE ================= --}}
  <div class="bg-white border border-blue-100 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-xs">
        <thead class="bg-blue-50 text-blue-700 text-[11px] uppercase tracking-wider">
          <tr>
            <th class="px-4 py-3 text-left whitespace-nowrap">Form</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Operator</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Status</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Waktu Submit</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Reviewer</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-blue-50">
          @forelse ($submissions as $sub)
            @php
              $statusMap = [
                'submitted'    => ['label'=>'SUBMITTED',    'cls'=>'bg-slate-50 text-slate-700 border-slate-200'],
                'under_review' => ['label'=>'UNDER REVIEW', 'cls'=>'bg-amber-50 text-amber-700 border-amber-200'],
                'approved'     => ['label'=>'APPROVED',     'cls'=>'bg-emerald-50 text-emerald-700 border-emerald-200'],
                'rejected'     => ['label'=>'REJECTED',     'cls'=>'bg-rose-50 text-rose-700 border-rose-200'],
              ];
              $st = $statusMap[$sub->status] ?? [
                'label'=>strtoupper($sub->status ?? '-'),
                'cls'=>'bg-slate-50 text-slate-700 border-slate-200'
              ];

              $data = $sub->data ?? []; // biar aman kalau null
            @endphp

            <tr class="transition align-top {{ $sub->status === 'submitted' ? 'bg-amber-50/30' : 'hover:bg-blue-50/40' }}">

              {{-- FORM --}}
              <td class="px-4 py-3">
                <div class="font-semibold text-slate-900">
                  {{ optional($sub->checkSheet)->title ?? '-' }}
                </div>
                <div class="text-[11px] text-slate-500">
                  Dept: {{ optional($sub->checkSheet)->department ?? '-' }}
                </div>
                @if(optional($sub->checkSheet)->product || optional($sub->checkSheet)->line)
                  <div class="mt-1 text-[11px] text-slate-500">
                    {{ optional($sub->checkSheet)->product ?? '-' }}
                    {{ optional($sub->checkSheet)->line ? ' / '.optional($sub->checkSheet)->line : '' }}
                  </div>
                @endif
              </td>

              {{-- OPERATOR --}}
              <td class="px-4 py-3 whitespace-nowrap text-slate-800">
                {{ optional($sub->operator)->name ?? '-' }}
              </td>

              {{-- STATUS --}}
              <td class="px-4 py-3 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-semibold {{ $st['cls'] }}">
                  {{ $st['label'] }}
                </span>
              </td>

              {{-- SUBMITTED --}}
              <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                {{ optional($sub->submitted_at)->format('d M Y H:i') ?? '-' }}
              </td>

              {{-- REVIEWER --}}
              <td class="px-4 py-3 text-slate-700">
                <div class="font-medium">
                  {{ optional($sub->reviewer)->name ?? '-' }}
                </div>
                @if($sub->reviewed_at)
                  <div class="text-[11px] text-slate-500">
                    {{ optional($sub->reviewed_at)->format('d M Y H:i') }}
                  </div>
                @endif
              </td>

              {{-- AKSI --}}
              <td class="px-4 py-3 w-[280px]">

                {{-- DETAIL DATA --}}
                <div x-data="{ open:false }" class="mb-2">
                  <button type="button"
                          @click="open=!open"
                          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg
                                 bg-white border border-blue-200 text-blue-700
                                 hover:bg-blue-50 font-semibold text-[11px] transition">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Lihat Data
                  </button>

                  <div x-show="open" x-transition.opacity
                       class="mt-2 bg-blue-50/60 border border-blue-100 rounded-xl p-3 text-[11px] text-slate-700 whitespace-pre-wrap">
Shift: {{ data_get($data,'shift','-') }}
Catatan: {{ data_get($data,'notes','-') }}

Hasil:
{{ data_get($data,'result','-') }}
                  </div>
                </div>

                {{-- BUTTON APPROVAL --}}
                @if($canReview && Route::has('check_sheets.submissions.status'))
                  <div class="flex flex-wrap gap-1.5">

                    {{-- TINJAU --}}
                    <form method="POST" action="{{ route('check_sheets.submissions.status', $sub) }}">
                      @csrf
                      @method('PATCH') {{-- kalau route kamu POST, hapus baris ini --}}
                      <input type="hidden" name="status" value="under_review">
                      <button type="submit"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg
                               bg-amber-100 text-amber-800 hover:bg-amber-200 text-[11px] font-semibold transition"
                        onclick="return confirm('Ubah status ke UNDER REVIEW?');"
                      >
                        Tinjau
                      </button>
                    </form>

                    {{-- SETUJUI --}}
                    <form method="POST" action="{{ route('check_sheets.submissions.status', $sub) }}">
                      @csrf
                      @method('PATCH')
                      <input type="hidden" name="status" value="approved">
                      <button type="submit"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg
                               bg-emerald-100 text-emerald-800 hover:bg-emerald-200 text-[11px] font-semibold transition"
                        onclick="return confirm('Setujui submission ini?');"
                      >
                        Setujui
                      </button>
                    </form>

                    {{-- TOLAK --}}
                    <form method="POST" action="{{ route('check_sheets.submissions.status', $sub) }}">
                      @csrf
                      @method('PATCH')
                      <input type="hidden" name="status" value="rejected">
                      <button type="submit"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg
                               bg-rose-100 text-rose-800 hover:bg-rose-200 text-[11px] font-semibold transition"
                        onclick="return confirm('Tolak submission ini?');"
                      >
                        Tolak
                      </button>
                    </form>

                  </div>
                @endif

              </td>

            </tr>

          @empty
            <tr>
              <td colspan="6" class="px-4 py-10 text-center">
                <div class="text-sm font-semibold text-slate-700 mb-1">Belum ada submission</div>
                <div class="text-xs text-slate-400">Submission operator akan tampil di sini.</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- PAGINATION --}}
    <div class="px-4 py-3 border-t border-blue-100">
      {{ $submissions->appends(request()->query())->links() }}
    </div>
  </div>

</div>
@endsection
