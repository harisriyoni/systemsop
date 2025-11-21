@extends('layouts.app')
@section('title', 'Akses User')

@section('content')

<div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">

  {{-- HEADER --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
    <div>
      <h2 class="text-base font-semibold text-slate-900">Manajemen Akses User</h2>
      <p class="text-xs text-slate-500">
        Menampilkan {{ $users->count() }} dari {{ $users->total() }} user
      </p>
    </div>

    @if(auth()->user()->isRole(['admin']))
      <a href="{{ route('users.create') }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition">
        <span class="text-lg leading-none">+</span>
        Tambah User
      </a>
    @endif
  </div>

  {{-- FILTER / SEARCH --}}
  <form method="GET" action="{{ route('users.index') }}"
        class="bg-blue-50/60 border border-blue-100 rounded-xl p-3 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">

      <div>
        <label class="block mb-1 text-slate-600">Kata Kunci</label>
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Cari nama / email..."
               class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                      focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none">
      </div>

      <div>
        <label class="block mb-1 text-slate-600">Role</label>
        <select name="role"
                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                       focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none">
          <option value="">Semua Role</option>
          @foreach($roles as $r)
            <option value="{{ $r }}" {{ request('role')==$r ? 'selected':'' }}>
              {{ strtoupper($r) }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block mb-1 text-slate-600">Status</label>
        <select name="status"
                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                       focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none">
          <option value="">Semua</option>
          <option value="active" {{ request('status')=='active'?'selected':'' }}>Aktif</option>
          <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Nonaktif</option>
        </select>
      </div>

      <div class="flex items-end gap-2">
        <button
          class="inline-flex items-center justify-center w-full md:w-auto px-4 py-2 rounded-lg
                 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition">
          Terapkan
        </button>
        <a href="{{ route('users.index') }}"
           class="inline-flex items-center justify-center w-full md:w-auto px-4 py-2 rounded-lg
                  bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
          Reset
        </a>
      </div>

    </div>
  </form>

  {{-- TABLE --}}
  <div class="overflow-x-auto rounded-xl border border-blue-100">
    <table class="min-w-full text-xs bg-white">
      <thead class="bg-blue-50 text-blue-700 text-[11px] uppercase tracking-wider">
        <tr>
          <th class="px-4 py-3 text-left whitespace-nowrap">Nama</th>
          <th class="px-4 py-3 text-left whitespace-nowrap">Email</th>
          <th class="px-4 py-3 text-left whitespace-nowrap">Role</th>
          <th class="px-4 py-3 text-left whitespace-nowrap">Status</th>
          <th class="px-4 py-3 text-left whitespace-nowrap">Dibuat</th>
          <th class="px-4 py-3 text-left whitespace-nowrap">Aksi</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-blue-50">
        @forelse($users as $u)
          @php
            $roleMap = [
              'admin' => 'bg-blue-600 text-white border-blue-600',
              'produksi' => 'bg-sky-50 text-sky-700 border-sky-200',
              'qa' => 'bg-amber-50 text-amber-700 border-amber-200',
              'logistik' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
              'operator' => 'bg-slate-50 text-slate-700 border-slate-200',
            ];
            $roleCls = $roleMap[$u->role ?? ''] ?? 'bg-slate-50 text-slate-700 border-slate-200';

            $isActive = $u->is_active ?? $u->active ?? null; // aman kalau kolom belum ada
          @endphp

          <tr class="hover:bg-blue-50/40 transition">
            <td class="px-4 py-3">
              <div class="font-semibold text-slate-900">{{ $u->name ?? '-' }}</div>
            </td>

            <td class="px-4 py-3 text-slate-700 whitespace-nowrap">
              {{ $u->email ?? '-' }}
            </td>

            <td class="px-4 py-3 whitespace-nowrap">
              <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-semibold {{ $roleCls }}">
                {{ strtoupper($u->role ?? '-') }}
              </span>
            </td>

            <td class="px-4 py-3 whitespace-nowrap">
              @if(is_null($isActive))
                <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-semibold bg-slate-50 text-slate-600 border-slate-200">
                  -
                </span>
              @elseif($isActive)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-semibold bg-emerald-50 text-emerald-700 border-emerald-200">
                  Aktif
                </span>
              @else
                <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-semibold bg-rose-50 text-rose-700 border-rose-200">
                  Nonaktif
                </span>
              @endif
            </td>

            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
              {{ optional($u->created_at)->format('d M Y') ?? '-' }}
            </td>

            <td class="px-4 py-3 whitespace-nowrap">
              <div class="flex items-center gap-2">
                <a href="{{ route('users.edit', $u) }}"
                   class="inline-flex items-center px-3 py-1.5 rounded-lg
                          bg-white border border-blue-200 text-blue-700
                          hover:bg-blue-50 font-semibold text-[11px] transition">
                  Edit
                </a>

                @if(auth()->user()->id !== $u->id)
                  <form method="POST" action="{{ route('users.destroy', $u) }}"
                        onsubmit="return confirm('Yakin hapus user ini?')">
                    @csrf
                    @method('DELETE')
                    <button
                      class="inline-flex items-center px-3 py-1.5 rounded-lg
                             bg-rose-50 border border-rose-200 text-rose-700
                             hover:bg-rose-100 font-semibold text-[11px] transition">
                      Hapus
                    </button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-4 py-10 text-center">
              <div class="text-sm font-semibold text-slate-700 mb-1">Belum ada user</div>
              <div class="text-xs text-slate-400">Silakan tambah user baru.</div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINATION --}}
  <div class="mt-4">
    {{ $users->appends(request()->query())->links() }}
  </div>

</div>

@endsection
