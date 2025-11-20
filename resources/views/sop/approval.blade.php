@extends('layouts.app')
@section('title', 'Approval SOP')

@section('content')
<h2 class="text-sm font-semibold text-slate-700 mb-3">
  Approval SOP (Role: {{ strtoupper($userRole) }})
</h2>

<table class="w-full text-xs bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase">
    <tr>
      <th class="px-3 py-2 text-left">Code</th>
      <th class="px-3 py-2 text-left">Title</th>
      <th class="px-3 py-2 text-left">Dept</th>
      <th class="px-3 py-2 text-left">Approvals</th>
      <th class="px-3 py-2 text-left">Action</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($sops as $sop)
      <tr class="border-t border-slate-100">
        <td class="px-3 py-2">{{ $sop->code }}</td>
        <td class="px-3 py-2">{{ $sop->title }}</td>
        <td class="px-3 py-2">{{ $sop->department }}</td>
        <td class="px-3 py-2">
          P: {{ $sop->is_approved_produksi ? '✔' : '✖' }} |
          QA: {{ $sop->is_approved_qa ? '✔' : '✖' }} |
          L: {{ $sop->is_approved_logistik ? '✔' : '✖' }}
        </td>
        <td class="px-3 py-2">
          <form method="POST" action="{{ route('sop.approve', $sop) }}">
            @csrf
            <button class="px-3 py-1 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-[11px]">
              Approve
            </button>
          </form>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="5" class="px-3 py-4 text-center text-slate-400">
          Tidak ada SOP yang perlu di-approve.
        </td>
      </tr>
    @endforelse
  </tbody>
</table>

<div class="mt-3">
  {{ $sops->links() }}
</div>
@endsection
