@extends('layouts.app')
@section('title', 'Check Sheet Submissions')

@section('content')
<div class="flex items-center justify-between mb-4">
  <h2 class="text-sm font-semibold text-slate-700">Submissions Check Sheet</h2>
</div>

<table class="w-full text-xs bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase">
    <tr>
      <th class="px-3 py-2 text-left">Form</th>
      <th class="px-3 py-2 text-left">Operator</th>
      <th class="px-3 py-2 text-left">Status</th>
      <th class="px-3 py-2 text-left">Submitted At</th>
      <th class="px-3 py-2 text-left">Reviewed By</th>
      <th class="px-3 py-2 text-left">Action</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($submissions as $sub)
      <tr class="border-t border-slate-100 align-top">
        <td class="px-3 py-2">
          <div class="font-semibold text-slate-800">{{ $sub->checkSheet->title ?? '-' }}</div>
          <div class="text-[11px] text-slate-500">
            Dept: {{ $sub->checkSheet->department ?? '-' }}
          </div>
        </td>
        <td class="px-3 py-2">
          {{ $sub->operator->name ?? '-' }}
        </td>
        <td class="px-3 py-2">
          <span class="px-2 py-0.5 rounded-full text-[11px]
            @if($sub->status === 'approved') bg-emerald-50 text-emerald-700
            @elseif($sub->status === 'rejected') bg-rose-50 text-rose-700
            @elseif($sub->status === 'under_review') bg-amber-50 text-amber-700
            @else bg-slate-50 text-slate-700
            @endif">
            {{ strtoupper($sub->status) }}
          </span>
        </td>
        <td class="px-3 py-2">
          {{ optional($sub->submitted_at)->format('d M Y H:i') ?? '-' }}
        </td>
        <td class="px-3 py-2">
          {{ $sub->reviewer->name ?? '-' }}<br>
          @if($sub->reviewed_at)
            <span class="text-[11px] text-slate-500">
              {{ $sub->reviewed_at->format('d M Y H:i') }}
            </span>
          @endif
        </td>
        <td class="px-3 py-2">
          <details class="mb-2">
            <summary class="cursor-pointer text-[11px] text-sky-600">Lihat Data</summary>
            <pre class="mt-1 bg-slate-50 border border-slate-200 rounded-lg p-2 text-[11px] text-slate-700 whitespace-pre-wrap">
Shift: {{ $sub->data['shift'] ?? '-' }}
Notes: {{ $sub->data['notes'] ?? '-' }}

Result:
{{ $sub->data['result'] ?? '-' }}
            </pre>
          </details>

          @if(auth()->user()->isRole(['admin','qa','logistik']))
            <div class="space-x-1">
              <form method="POST" action="{{ route('check_sheets.submissions.status', $sub) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="under_review">
                <button class="px-2 py-1 rounded bg-amber-100 text-amber-700 text-[11px]">
                  Under Review
                </button>
              </form>
              <form method="POST" action="{{ route('check_sheets.submissions.status', $sub) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="approved">
                <button class="px-2 py-1 rounded bg-emerald-100 text-emerald-700 text-[11px]">
                  Approve
                </button>
              </form>
              <form method="POST" action="{{ route('check_sheets.submissions.status', $sub) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="rejected">
                <button class="px-2 py-1 rounded bg-rose-100 text-rose-700 text-[11px]">
                  Reject
                </button>
              </form>
            </div>
          @endif
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="6" class="px-3 py-4 text-center text-slate-400">
          Belum ada submission.
        </td>
      </tr>
    @endforelse
  </tbody>
</table>

<div class="mt-3">
  {{ $submissions->links() }}
</div>
@endsection
