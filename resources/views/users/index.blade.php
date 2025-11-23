@extends('layouts.app')
@section('title', 'Akses User')

@section('content')
@php
  // ===== SAFE FALLBACKS =====
  $roles = $roles ?? ['admin','produksi','qa','logistik','operator'];

  $users = $users
    ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);

  $q      = request('q');
  $roleF  = request('role');
  $status = request('status');
@endphp

<div class="max-w-7xl mx-auto space-y-4">

  {{-- ================= HEADER CARD ================= --}}
  <div class="bg-white rounded-2xl border border-[#05727d]/20 shadow-sm p-5">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div class="flex items-start gap-3">
        <div class="h-10 w-10 rounded-2xl bg-[#05727d]/10 text-[#05727d] grid place-items-center shrink-0">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20h6v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2h6"/>
            <circle cx="9" cy="7" r="4"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M23 7a4 4 0 01-7.75 1.25"/>
          </svg>
        </div>
        <div>
          <h2 class="text-base font-semibold text-slate-900">Manajemen Akses User</h2>
          <p class="text-xs text-slate-500">
            Menampilkan {{ $users->count() }} dari {{ $users->total() }} user
          </p>
        </div>
      </div>

      {{-- routes kamu: users.create --}}
      @if(Route::has('users.create'))
        <a href="{{ route('users.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                  bg-[#05727d] hover:bg-[#04616a] text-white text-xs font-semibold
                  shadow-sm transition">
          <span class="text-lg leading-none">+</span>
          Tambah User
        </a>
      @endif
    </div>

    {{-- ================= FILTER / SEARCH ================= --}}
    @if(Route::has('users.index'))
    <form method="GET" action="{{ route('users.index') }}"
          class="mt-4 bg-[#05727d]/5 border border-[#05727d]/20 rounded-xl p-3">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-xs">

        <div>
          <label class="block mb-1 text-slate-600">Kata Kunci</label>
          <input type="text" name="q" value="{{ $q }}"
                 placeholder="Cari nama / email..."
                 class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                        focus:ring-4 focus:ring-[#05727d]/15 focus:border-[#05727d] outline-none">
        </div>

        <div>
          <label class="block mb-1 text-slate-600">Role</label>
          <select name="role"
                  class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                         focus:ring-4 focus:ring-[#05727d]/15 focus:border-[#05727d] outline-none">
            <option value="">Semua Role</option>
            @foreach($roles as $r)
              <option value="{{ $r }}" {{ $roleF==$r ? 'selected':'' }}>
                {{ strtoupper($r) }}
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block mb-1 text-slate-600">Status</label>
          <select name="status"
                  class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                         focus:ring-4 focus:ring-[#05727d]/15 focus:border-[#05727d] outline-none">
            <option value="">Semua</option>
            <option value="active" {{ $status=='active'?'selected':'' }}>Aktif</option>
            <option value="inactive" {{ $status=='inactive'?'selected':'' }}>Nonaktif</option>
          </select>
        </div>

        <div class="md:col-span-2 flex items-end justify-end gap-2">
          <button
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg
                   bg-[#05727d] hover:bg-[#04616a] text-white font-semibold text-xs transition">
            Terapkan
          </button>
          <a href="{{ route('users.index') }}"
             class="inline-flex items-center justify-center px-4 py-2 rounded-lg
                    bg-white border border-slate-200 hover:bg-slate-50 text-slate-700
                    font-semibold text-xs transition">
            Reset
          </a>
        </div>

      </div>
    </form>
    @endif
  </div>


  {{-- ================= TABLE ================= --}}
  <div class="bg-white rounded-2xl border border-[#05727d]/20 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-xs bg-white">
        <thead class="bg-[#05727d]/5 text-[#05727d] text-[11px] uppercase tracking-wider sticky top-0 z-10">
          <tr>
            <th class="px-4 py-3 text-left whitespace-nowrap">Nama</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Email</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Role</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Status</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Dibuat</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-[#05727d]/10">
          @forelse($users as $u)
            @php
              // role chip colors versi brand
              $roleMap = [
                'admin'    => 'bg-[#05727d] text-white border-[#05727d]',
                'produksi' => 'bg-[#05727d]/10 text-[#05727d] border-[#05727d]/25',
                'qa'       => 'bg-amber-50 text-amber-700 border-amber-200',
                'logistik' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'operator' => 'bg-slate-50 text-slate-700 border-slate-200',
              ];
              $roleCls = $roleMap[$u->role ?? ''] ?? 'bg-slate-50 text-slate-700 border-slate-200';

              // routes kamu pakai toggleActive => asumsi boolean is_active
              $isActive = $u->is_active ?? $u->active ?? null;
            @endphp

            <tr class="hover:bg-[#05727d]/[0.04] transition">
              {{-- NAMA --}}
              <td class="px-4 py-3">
                <div class="font-semibold text-slate-900">{{ $u->name ?? '-' }}</div>
                @if(isset($u->username))
                  <div class="text-[11px] text-slate-400">{{ '@'.$u->username }}</div>
                @endif
              </td>

              {{-- EMAIL --}}
              <td class="px-4 py-3 text-slate-700 whitespace-nowrap">
                {{ $u->email ?? '-' }}
              </td>

              {{-- ROLE --}}
              <td class="px-4 py-3 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-semibold {{ $roleCls }}">
                  {{ strtoupper($u->role ?? '-') }}
                </span>
              </td>

              {{-- STATUS --}}
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

              {{-- CREATED --}}
              <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                {{ optional($u->created_at)->format('d M Y') ?? '-' }}
              </td>

              {{-- AKSI --}}
              <td class="px-4 py-3 whitespace-nowrap">
                <div class="flex flex-wrap items-center gap-2">

                  {{-- users.edit --}}
                  @if(Route::has('users.edit'))
                    <a href="{{ route('users.edit', $u) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg
                              bg-white border border-[#05727d]/30 text-[#05727d]
                              hover:bg-[#05727d]/5 font-semibold text-[11px] transition">
                      Edit
                    </a>
                  @endif

                  {{-- users.reset_password (POST) --}}
                  @if(Route::has('users.reset_password') && auth()->user()->id !== $u->id)
                    <form method="POST" action="{{ route('users.reset_password', $u) }}"
                          onsubmit="return confirm('Reset password user ini? Password akan diganti default.');">
                      @csrf
                      <button type="submit"
                        class="inline-flex items-center px-3 py-1.5 rounded-lg
                               bg-amber-50 border border-amber-200 text-amber-700
                               hover:bg-amber-100 font-semibold text-[11px] transition">
                        Reset Password
                      </button>
                    </form>
                  @endif

                  {{-- users.toggle_active (POST) --}}
                  @if(Route::has('users.toggle_active') && auth()->user()->id !== $u->id && !is_null($isActive))
                    <form method="POST" action="{{ route('users.toggle_active', $u) }}"
                          onsubmit="return confirm('Ubah status aktif/nonaktif user ini?');">
                      @csrf
                      <button type="submit"
                        class="inline-flex items-center px-3 py-1.5 rounded-lg border
                               font-semibold text-[11px] transition
                               {{ $isActive
                                  ? 'bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200'
                                  : 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100' }}">
                        {{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }}
                      </button>
                    </form>
                  @endif

                  {{-- users.destroy --}}
                  @if(Route::has('users.destroy') && auth()->user()->id !== $u->id)
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
              <td colspan="6" class="px-4 py-12 text-center">
                <div class="mx-auto w-14 h-14 rounded-2xl bg-[#05727d]/10 grid place-items-center text-[#05727d] mb-3">
                  <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20h6v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2h6"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M23 7a4 4 0 01-7.75 1.25"/>
                  </svg>
                </div>
                <div class="text-sm font-semibold text-slate-800 mb-1">Belum ada user</div>
                <div class="text-xs text-slate-400">Silakan tambah user baru.</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- PAGINATION --}}
    <div class="px-4 py-4 bg-white border-t border-[#05727d]/15">
      {{ $users->appends(request()->query())->links() }}
    </div>
  </div>

</div>
@endsection
