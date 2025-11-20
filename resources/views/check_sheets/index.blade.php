@extends('layouts.app')
@section('title', 'Check Sheet Forms')

@section('content')
<div class="flex items-center justify-between mb-4">
  <h2 class="text-sm font-semibold text-slate-700">List Check Sheet Forms</h2>
  @if (auth()->user()->isRole(['admin','produksi','qa','logistik']))
    <a href="{{ route('check_sheets.create') }}"
       class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs">
      + Create Form
    </a>
  @endif
</div>

<table class="w-full text-xs bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase">
    <tr>
      <th class="px-3 py-2 text-left">Title</th>
      <th class="px-3 py-2 text-left">Dept</th>
      <th class="px-3 py-2 text-left">Product</th>
      <th class="px-3 py-2 text-left">Line</th>
      <th class="px-3 py-2 text-left">Status</th>
      <th class="px-3 py-2 text-left">Actions</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($forms as $form)
      <tr class="border-t border-slate-100">
        <td class="px-3 py-2">{{ $form->title }}</td>
        <td class="px-3 py-2">{{ $form->department }}</td>
        <td class="px-3 py-2">{{ $form->product ?: '-' }}</td>
        <td class="px-3 py-2">{{ $form->line ?: '-' }}</td>
        <td class="px-3 py-2">
          <span class="px-2 py-0.5 rounded-full text-[11px]
            @if($form->status === 'active') bg-emerald-50 text-emerald-700
            @elseif($form->status === 'draft') bg-slate-50 text-slate-700
            @else bg-slate-100 text-slate-500
            @endif">
            {{ $form->status }}
          </span>
        </td>
        <td class="px-3 py-2 space-x-2">
          @if($form->status === 'active')
            <a href="{{ route('check_sheets.fill', $form) }}"
               class="text-sky-600 hover:underline">
              Fill
            </a>
          @endif
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="6" class="px-3 py-4 text-center text-slate-400">
          Belum ada form check sheet.
        </td>
      </tr>
    @endforelse
  </tbody>
</table>

<div class="mt-3">
  {{ $forms->links() }}
</div>
@endsection
