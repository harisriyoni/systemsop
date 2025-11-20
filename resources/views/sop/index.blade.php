@extends('layouts.app')
@section('title', 'SOP List')

@section('content')
<div class="bg-slate-50 rounded-3xl p-5">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-sm font-semibold text-slate-700">List SOP</h2>

    {{-- TOMBOL CREATE SOP: hanya untuk admin & produksi --}}
    @if (auth()->user()->isRole(['admin','produksi']))
      <a href="{{ route('sop.create') }}"
         class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs">
        + Create SOP
      </a>
    @endif
  </div>

  <table class="w-full text-xs bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase">
      <tr>
        <th class="px-3 py-2 text-left">Code</th>
        <th class="px-3 py-2 text-left">Title</th>
        <th class="px-3 py-2 text-left">Dept</th>
        <th class="px-3 py-2 text-left">Status</th>
        <th class="px-3 py-2 text-left">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($sops as $sop)
        <tr class="border-t border-slate-100">
          <td class="px-3 py-2">{{ $sop->code }}</td>
          <td class="px-3 py-2">{{ $sop->title }}</td>
          <td class="px-3 py-2">{{ $sop->department }}</td>
          <td class="px-3 py-2">
            <span class="px-2 py-0.5 rounded-full text-[11px]
              @if($sop->status === 'approved') bg-emerald-50 text-emerald-700
              @elseif($sop->status === 'waiting_approval') bg-amber-50 text-amber-700
              @elseif($sop->status === 'expired') bg-rose-50 text-rose-700
              @else bg-slate-50 text-slate-700
              @endif">
              {{ $sop->status }}
            </span>
          </td>
          <td class="px-3 py-2 space-x-2">
            <a href="{{ route('sop.show', $sop) }}" class="text-sky-600 hover:underline">View</a>

            {{-- Tombol Approve di list, kalau masih waiting --}}
            @if(auth()->user()->isRole(['admin','produksi','qa','logistik']) && $sop->status === 'waiting_approval')
              <form method="POST" action="{{ route('sop.approve', $sop) }}" class="inline">
                @csrf
                <button
                  class="px-3 py-1 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-[11px]">
                  Approve
                </button>
              </form>
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="5" class="px-3 py-4 text-center text-slate-400">Belum ada SOP.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <div class="mt-3">
    {{ $sops->links() }}
  </div>
</div>
@endsection
