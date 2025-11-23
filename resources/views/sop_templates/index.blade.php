@extends('layouts.app')
@section('title','Template SOP')

@section('content')
@php
  $templates = $templates ?? new \Illuminate\Pagination\LengthAwarePaginator([],0,12);
@endphp

<div class="max-w-6xl mx-auto space-y-4">

  <div class="bg-white rounded-2xl border border-[#05727d]/20 shadow-sm p-5">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-base font-semibold text-slate-900">Template SOP</h2>
        <p class="text-xs text-slate-500">Kelola template builder SOP.</p>
      </div>

      <a href="{{ route('sop.templates.create') }}"
         class="px-4 py-2 rounded-xl bg-[#05727d] hover:bg-[#04616a] text-white text-xs font-semibold">
        + Buat Template
      </a>
    </div>

    <form method="GET" class="mt-4 bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl p-3">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
        <input name="q" value="{{ request('q') }}"
               placeholder="Cari nama/kode template..."
               class="rounded-lg border border-slate-200 bg-white px-3 py-2
                      focus:ring-4 focus:ring-[#05727d]/15 focus:border-[#05727d] outline-none">

        <input name="department" value="{{ request('department') }}"
               placeholder="Departemen..."
               class="rounded-lg border border-slate-200 bg-white px-3 py-2
                      focus:ring-4 focus:ring-[#05727d]/15 focus:border-[#05727d] outline-none">

        <select name="active"
                class="rounded-lg border border-slate-200 bg-white px-3 py-2
                       focus:ring-4 focus:ring-[#05727d]/15 focus:border-[#05727d] outline-none">
          <option value="">Semua Status</option>
          <option value="1" {{ request('active')==='1'?'selected':'' }}>Aktif</option>
          <option value="0" {{ request('active')==='0'?'selected':'' }}>Nonaktif</option>
        </select>

        <div class="flex items-end justify-end gap-2">
          <button class="px-4 py-2 rounded-lg bg-[#05727d] text-white font-semibold">Filter</button>
          <a href="{{ route('sop.templates.index') }}"
             class="px-4 py-2 rounded-lg bg-white border border-slate-200 font-semibold">Reset</a>
        </div>
      </div>
    </form>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
    @forelse($templates as $t)
      <div class="bg-white rounded-2xl border border-[#05727d]/20 shadow-sm p-4">
        <div class="flex items-start justify-between">
          <div>
            <div class="font-semibold text-slate-900">{{ $t->name }}</div>
            <div class="text-[11px] text-slate-500">
              {{ $t->code ?? '-' }} • {{ $t->department ?? '-' }}
            </div>
          </div>
          <span class="text-[10px] px-2 py-0.5 rounded-full border
            {{ $t->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                            : 'bg-rose-50 text-rose-700 border-rose-200' }}">
            {{ $t->is_active ? 'AKTIF' : 'NONAKTIF' }}
          </span>
        </div>

        <div class="mt-3 flex gap-2">
          <a href="{{ route('sop.templates.edit',$t) }}"
             class="px-3 py-1.5 rounded-lg bg-white border border-[#05727d]/30 text-[#05727d] text-[11px] font-semibold hover:bg-[#05727d]/5">
            Edit
          </a>
          <form method="POST" action="{{ route('sop.templates.destroy',$t) }}"
                onsubmit="return confirm('Hapus template ini?')">
            @csrf @method('DELETE')
            <button class="px-3 py-1.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-[11px] font-semibold hover:bg-rose-100">
              Hapus
            </button>
          </form>
        </div>
      </div>
    @empty
      <div class="col-span-full text-center text-slate-400 py-12 bg-white rounded-2xl border border-[#05727d]/20">
        Belum ada template.
      </div>
    @endforelse
  </div>

  <div class="px-2 py-2">
    {{ $templates->links() }}
  </div>

</div>
@endsection
