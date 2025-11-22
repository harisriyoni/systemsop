@extends('layouts.app')
@section('title', 'Daftar Form Check Sheet')

@section('content')
@php
  $user = auth()->user();
  $canManage = $user->isRole(['admin','produksi','qa','logistik']);
@endphp

<div
  x-data="{
    openCreate: {{ $errors->any() ? 'true' : 'false' }},
    openCreateModal(){
      this.openCreate=true;
      this.$nextTick(()=> this.$refs.titleInput?.focus());
    }
  }"
  class="space-y-4"
>

  {{-- =======================
     HEADER CARD
  ======================= --}}
  <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h2 class="text-base font-semibold text-slate-900">Daftar Form Check Sheet</h2>
        <p class="text-xs text-slate-500">
          Menampilkan {{ $forms->count() }} dari {{ $forms->total() }} form
        </p>
      </div>

      @if ($canManage && Route::has('check_sheets.store'))
        <button
          type="button"
          @click="openCreateModal()"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition"
        >
          <span class="text-lg leading-none">+</span>
          Tambah Form
        </button>
      @endif
    </div>

    {{-- =======================
       FILTER / SEARCH
    ======================= --}}
    @if(Route::has('check_sheets.index'))
      <form method="GET" action="{{ route('check_sheets.index') }}"
            class="mt-4 bg-blue-50/60 border border-blue-100 rounded-xl p-3">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-xs">

          <div>
            <label class="block mb-1 text-slate-600">Kata Kunci</label>
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Cari judul form..."
                   class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                          focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none">
          </div>

          <div>
            <label class="block mb-1 text-slate-600">Departemen</label>
            <input type="text" name="department" value="{{ request('department') }}"
                   placeholder="Produksi / QA / Logistik..."
                   class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                          focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none">
          </div>

          <div>
            <label class="block mb-1 text-slate-600">Produk</label>
            <input type="text" name="product" value="{{ request('product') }}"
                   placeholder="Opsional"
                   class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                          focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none">
          </div>

          <div>
            <label class="block mb-1 text-slate-600">Lini Produksi</label>
            <input type="text" name="line" value="{{ request('line') }}"
                   placeholder="Opsional"
                   class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                          focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none">
          </div>

          <div>
            <label class="block mb-1 text-slate-600">Status</label>
            <select name="status"
                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2
                           focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none">
              <option value="">Semua Status</option>
              <option value="draft"  {{ request('status')=='draft' ? 'selected' : '' }}>Draf</option>
              <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Aktif</option>
              <option value="inactive" {{ request('status')=='inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
          </div>

          <div class="md:col-span-5 flex items-end gap-2 justify-end">
            <button
              class="inline-flex items-center justify-center px-4 py-2 rounded-lg
                     bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition">
              Terapkan
            </button>
            <a href="{{ route('check_sheets.index') }}"
               class="inline-flex items-center justify-center px-4 py-2 rounded-lg
                      bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
              Reset
            </a>
          </div>

        </div>
      </form>
    @endif
  </div>


  {{-- =======================
     TABLE FORM
  ======================= --}}
  <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-xs bg-white">
        <thead class="bg-blue-50 text-blue-700 text-[11px] uppercase tracking-wider sticky top-0 z-10">
          <tr>
            <th class="px-4 py-3 text-left">Judul Form</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Departemen</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Produk</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Lini</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Status</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Updated</th>
            <th class="px-4 py-3 text-left whitespace-nowrap">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-blue-50">
          @forelse ($forms as $form)
            @php
              $statusMap = [
                'draft'    => ['label' => 'Draf',    'class' => 'bg-slate-50 text-slate-700 border-slate-200'],
                'active'   => ['label' => 'Aktif',   'class' => 'bg-blue-600 text-white border-blue-600'],
                'inactive' => ['label' => 'Nonaktif','class' => 'bg-slate-100 text-slate-500 border-slate-200'],
              ];
              $st = $statusMap[$form->status] ?? ['label'=>$form->status, 'class'=>'bg-slate-50 text-slate-700 border-slate-200'];
            @endphp

            <tr class="hover:bg-blue-50/40 transition">
              <td class="px-4 py-3">
                <div class="font-medium text-slate-900">{{ $form->title }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                  Dibuat: {{ optional($form->created_at)->format('d M Y') }}
                </div>
              </td>

              <td class="px-4 py-3 whitespace-nowrap">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100 text-[11px] font-semibold">
                  {{ $form->department }}
                </span>
              </td>

              <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                {{ $form->product ?: '-' }}
              </td>

              <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                {{ $form->line ?: '-' }}
              </td>

              <td class="px-4 py-3 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-semibold {{ $st['class'] }}">
                  {{ $st['label'] }}
                </span>
              </td>

              <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                {{ optional($form->updated_at)->format('d M Y H:i') }}
              </td>

              <td class="px-4 py-3 whitespace-nowrap">
                <div class="flex flex-wrap items-center gap-2">

                  {{-- Lihat detail --}}
                  @if(Route::has('check_sheets.show'))
                    <a href="{{ route('check_sheets.show', $form) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg
                              bg-white border border-blue-200 text-blue-700
                              hover:bg-blue-50 font-semibold text-[11px] transition">
                      Lihat
                    </a>
                  @endif

                  {{-- Edit --}}
                  @if($canManage && Route::has('check_sheets.edit'))
                    <a href="{{ route('check_sheets.edit', $form) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg
                              bg-white border border-slate-200 text-slate-700
                              hover:bg-slate-50 font-semibold text-[11px] transition">
                      Edit
                    </a>
                  @endif

                  {{-- Builder (kalau ada) --}}
                  @if($canManage && Route::has('check_sheets.builder'))
                    <a href="{{ route('check_sheets.builder', $form) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg
                              bg-blue-50 border border-blue-100 text-blue-700
                              hover:bg-blue-100 font-semibold text-[11px] transition">
                      Builder
                    </a>
                  @endif

                  {{-- Isi Form (hanya aktif) --}}
                  @if($form->status === 'active' && Route::has('check_sheets.fill'))
                    <a href="{{ route('check_sheets.fill', $form) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg
                              bg-white border border-blue-200 text-blue-700
                              hover:bg-blue-50 font-semibold text-[11px] transition">
                      Isi Form
                    </a>
                  @else
                    <span class="text-[11px] text-slate-400 italic">Belum aktif</span>
                  @endif

                  {{-- Activate / Deactivate --}}
                  @if($canManage)

                    @if($form->status !== 'active' && Route::has('check_sheets.activate'))
                      <form method="POST" action="{{ route('check_sheets.activate', $form) }}">
                        @csrf
                        <button
                          class="inline-flex items-center px-3 py-1.5 rounded-lg
                                 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[11px] transition"
                          onclick="return confirm('Aktifkan form ini?');"
                        >
                          Aktifkan
                        </button>
                      </form>
                    @endif

                    @if($form->status === 'active' && Route::has('check_sheets.deactivate'))
                      <form method="POST" action="{{ route('check_sheets.deactivate', $form) }}">
                        @csrf
                        <button
                          class="inline-flex items-center px-3 py-1.5 rounded-lg
                                 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200
                                 font-semibold text-[11px] transition"
                          onclick="return confirm('Nonaktifkan form ini?');"
                        >
                          Nonaktifkan
                        </button>
                      </form>
                    @endif

                  @endif

                  {{-- Delete --}}
                  @if($user->isRole(['admin']) && Route::has('check_sheets.destroy'))
                    <form method="POST" action="{{ route('check_sheets.destroy', $form) }}">
                      @csrf @method('DELETE')
                      <button
                        class="inline-flex items-center px-3 py-1.5 rounded-lg
                               bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200
                               font-semibold text-[11px] transition"
                        onclick="return confirm('Yakin hapus form ini?');"
                      >
                        Hapus
                      </button>
                    </form>
                  @endif

                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-12 text-center">
                <div class="mx-auto w-14 h-14 rounded-2xl bg-blue-50 grid place-items-center text-blue-700 mb-3">
                  <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6M9 9h6M9 13h6M9 17h6M5 5h.01M5 9h.01M5 13h.01M5 17h.01"/>
                  </svg>
                </div>
                <div class="text-sm font-semibold text-slate-800 mb-1">Belum ada form check sheet</div>
                <div class="text-xs text-slate-400">Silakan buat form baru atau ubah filter pencarian.</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- PAGINATION --}}
    <div class="px-4 py-3 border-t border-blue-100">
      {{ $forms->appends(request()->query())->links() }}
    </div>
  </div>


  {{-- ================= MODAL CREATE FORM ================= --}}
  <div
    x-show="openCreate"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center px-4"
    style="display:none;"
  >
    <div class="absolute inset-0 bg-black/40" @click="openCreate=false"></div>

    <div class="relative w-full max-w-2xl bg-white rounded-2xl border border-blue-100 shadow-2xl overflow-hidden">
      <div class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-4 text-white">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-white/15 grid place-items-center">
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6M9 9h6M9 13h6M9 17h6M5 5h.01M5 9h.01M5 13h.01M5 17h.01"/>
              </svg>
            </div>
            <div>
              <h3 class="text-base font-semibold leading-tight">Tambah Form Check Sheet</h3>
              <p class="text-xs text-blue-100">Buat form baru untuk operator isi harian.</p>
            </div>
          </div>
          <button @click="openCreate=false" class="p-2 rounded-lg hover:bg-white/10">✕</button>
        </div>
      </div>

      <div class="p-6 space-y-4">

        {{-- Error global --}}
        @if ($errors->any())
          <div class="text-xs rounded-lg bg-blue-50 border border-blue-200 text-blue-800 px-3 py-2">
            <div class="font-semibold mb-1">Periksa kembali input:</div>
            <ul class="list-disc pl-4 space-y-0.5">
              @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form id="checkSheetCreateForm" method="POST" action="{{ route('check_sheets.store') }}" class="space-y-4">
          @csrf

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Judul --}}
            <div class="md:col-span-2">
              <label class="block text-xs text-slate-600 mb-1">Judul Form <span class="text-rose-500">*</span></label>
              <input x-ref="titleInput" type="text" name="title" value="{{ old('title') }}"
                     class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                            {{ $errors->has('title') ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500' : 'border-slate-200 focus:ring-blue-100 focus:border-blue-500' }}"
                     placeholder="Contoh: Check Sheet Harian OHT" required>
              @error('title') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>

            {{-- Departemen --}}
            <div>
              <label class="block text-xs text-slate-600 mb-1">Departemen <span class="text-rose-500">*</span></label>
              <input type="text" name="department" value="{{ old('department') }}"
                     class="w-full rounded-lg border px-3 py-2 text-sm outline-none
                            {{ $errors->has('department') ? 'border-rose-300 focus:ring-rose-100 focus:border-rose-500' : 'border-slate-200 focus:ring-blue-100 focus:border-blue-500' }}"
                     placeholder="QA / Logistik / Produksi" required>
              @error('department') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>

            {{-- Produk --}}
            <div>
              <label class="block text-xs text-slate-600 mb-1">Produk (Opsional)</label>
              <input type="text" name="product" value="{{ old('product') }}"
                     class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                            focus:ring-blue-100 focus:border-blue-500"
                     placeholder="Nickel Matte / Packing ...">
              @error('product') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>

            {{-- Lini --}}
            <div>
              <label class="block text-xs text-slate-600 mb-1">Lini Produksi (Opsional)</label>
              <input type="text" name="line" value="{{ old('line') }}"
                     class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                            focus:ring-blue-100 focus:border-blue-500"
                     placeholder="Line A / Line B">
              @error('line') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>

            {{-- Status awal (opsional) --}}
            <div class="md:col-span-2">
              <label class="block text-xs text-slate-600 mb-1">Status Awal (Opsional)</label>
              <select name="status"
                      class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none
                             focus:ring-blue-100 focus:border-blue-500">
                <option value="">Default sistem</option>
                <option value="draft" {{ old('status')=='draft' ? 'selected' : '' }}>Draf</option>
                <option value="active" {{ old('status')=='active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ old('status')=='inactive' ? 'selected' : '' }}>Nonaktif</option>
              </select>
              <div class="text-[11px] text-slate-400 mt-1">
                Jika controller belum mendukung, field ini akan diabaikan otomatis.
              </div>
            </div>

            {{-- Deskripsi --}}
            <div class="md:col-span-2">
              <label class="block text-xs text-slate-600 mb-1">Deskripsi / Instruksi Singkat</label>
              <textarea name="description" rows="4"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none
                               focus:ring-blue-100 focus:border-blue-500"
                        placeholder="Instruksi singkat untuk operator saat mengisi form...">{{ old('description') }}</textarea>
              @error('description') <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>
          </div>

        </form>
      </div>

      {{-- FOOTER ACTION --}}
      <div class="sticky bottom-0 bg-white/95 backdrop-blur border-t border-blue-100 px-6 py-3">
        <div class="flex items-center justify-end gap-2">
          <button type="button"
                  @click="openCreate=false"
                  class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
            Batal
          </button>

          {{-- Simpan Draft (opsional, aman kalau controller belum pakai) --}}
          <button type="submit"
                  form="checkSheetCreateForm"
                  name="save_draft" value="1"
                  class="px-4 py-2 rounded-lg bg-white border border-blue-200 text-blue-700 text-xs font-semibold hover:bg-blue-50">
            Simpan Draft
          </button>

          <button type="submit"
                  form="checkSheetCreateForm"
                  class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm">
            Simpan Form
          </button>
        </div>
      </div>

    </div>
  </div>
  {{-- ================= END MODAL ================= --}}

</div>
@endsection
