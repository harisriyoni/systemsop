<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'PT. DIPSOL INDONESIA')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Vite optional --}}
    @vite([])

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .sidebar-scroll::-webkit-scrollbar { width: 6px; }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #a8ced2; /* brand-200 */
            border-radius: 999px;
        }
    </style>

    @stack('head')
</head>

@php
    // aman untuk halaman selain dashboard
    $needAction = $needAction ?? [];

    // SOP badge (tetap)
    $badgeSop =
        ($needAction['sop_pending_produksi'] ?? 0) +
        ($needAction['sop_pending_qa'] ?? 0) +
        ($needAction['sop_pending_logistik'] ?? 0);

    // ✅ FIX badge CS: 1 key aja biar gak 0 terus
    $badgeCs = $needAction['cs_pending'] ?? 0;

    $user = auth()->user();
@endphp

<body class="bg-slate-50 min-h-screen text-slate-800">
<div x-data="{ mobileOpen:false, collapsed:false }" class="min-h-screen flex">

    <!-- OVERLAY MOBILE -->
    <div
        x-show="mobileOpen"
        x-transition.opacity
        class="fixed inset-0 bg-black/40 z-40 md:hidden"
        @click="mobileOpen=false"
        style="display:none;"></div>

    {{-- SIDEBAR --}}
    <aside
        class="fixed md:static inset-y-0 left-0 z-50 md:z-auto
               bg-white border-r border-slate-200 shadow-sm
               flex flex-col transition-all duration-200"
        :class="[
            mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
            collapsed ? 'w-20' : 'w-64'
        ]">

        <!-- Brand -->
        <div class="px-4 py-4 border-b border-slate-200 bg-gradient-to-r from-[#05727d] to-[#04616a] text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-xl bg-white/15 grid place-items-center shadow">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div x-show="!collapsed" x-transition>
                        <div class="text-sm font-semibold leading-tight">PT. DIPSOL INDONESIA</div>
                        <div class="text-[11px] opacity-90">SOP + CheckFlow</div>
                    </div>
                </div>

                <!-- Collapse button (desktop) -->
                <button
                    class="hidden md:inline-flex p-2 rounded-lg hover:bg-white/10"
                    @click="collapsed=!collapsed"
                    :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'">
                    <svg x-show="!collapsed" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    <svg x-show="collapsed" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Menu -->
        <nav class="flex-1 px-2 py-3 space-y-1 text-sm overflow-y-auto sidebar-scroll">

            {{-- DASHBOARD --}}
            <a href="{{ route('dashboard') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                {{ request()->routeIs('dashboard')
                    ? 'bg-[#05727d] text-white shadow-sm'
                    : 'text-slate-700 hover:bg-[#e6f1f2] hover:text-[#045058]' }}">
                <div class="w-9 h-9 rounded-lg grid place-items-center
                    {{ request()->routeIs('dashboard') ? 'bg-white/15' : 'bg-slate-100 group-hover:bg-[#cde3e5]' }}">
                    <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 12l9-9 9 9v8a1 1 0 01-1 1h-5v-7H9v7H4a1 1 0 01-1-1z" />
                    </svg>
                </div>
                <span x-show="!collapsed" x-transition class="font-medium">Dashboard</span>
            </a>


            {{-- SOP GROUP --}}
            <div class="mt-4 px-3 text-[11px] uppercase text-slate-400 tracking-wider flex items-center gap-2" x-show="!collapsed">
                <span>SOP</span>
                <span class="h-px flex-1 bg-slate-200"></span>
            </div>

            {{-- SOP Management --}}
            <a href="{{ route('sop.index') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                {{ request()->routeIs('sop.index','sop.show','sop.edit')
                    ? 'bg-[#05727d] text-white shadow-sm'
                    : 'text-slate-700 hover:bg-[#e6f1f2] hover:text-[#045058]' }}">
                <div class="w-9 h-9 rounded-lg grid place-items-center
                    {{ request()->routeIs('sop.*') ? 'bg-white/15' : 'bg-slate-100 group-hover:bg-[#cde3e5]' }}">
                    <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                    </svg>
                </div>
                <span x-show="!collapsed" x-transition class="font-medium">SOP Management</span>
            </a>

            {{-- Create SOP --}}
            @if($user && $user->isRole(['admin','produksi']))
            <a href="{{ route('sop.create') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                {{ request()->routeIs('sop.create')
                    ? 'bg-[#05727d] text-white shadow-sm'
                    : 'text-slate-700 hover:bg-[#e6f1f2] hover:text-[#045058]' }}">
                <div class="w-9 h-9 rounded-lg grid place-items-center
                    {{ request()->routeIs('sop.create') ? 'bg-white/15' : 'bg-slate-100 group-hover:bg-[#cde3e5]' }}">
                    <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <span x-show="!collapsed" x-transition>Create SOP</span>
            </a>
            @endif

            {{-- Approval SOP --}}
            @if($user && $user->isRole(['admin','produksi','qa','logistik']))
            <a href="{{ route('sop.approval.index') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                {{ request()->routeIs('sop.approval.*')
                    ? 'bg-[#05727d] text-white shadow-sm'
                    : 'text-slate-700 hover:bg-[#e6f1f2] hover:text-[#045058]' }}">
                <div class="w-9 h-9 rounded-lg grid place-items-center
                    {{ request()->routeIs('sop.approval.*') ? 'bg-white/15' : 'bg-slate-100 group-hover:bg-[#cde3e5]' }}">
                    <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>

                <span x-show="!collapsed" x-transition class="flex-1">Approval SOP</span>

                {{-- badge --}}
                <span x-show="!collapsed" x-transition
                      class="text-[11px] px-2 py-0.5 rounded-full
                      {{ $badgeSop>0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $badgeSop }}
                </span>
            </a>
            @endif


            {{-- CHECK SHEET GROUP --}}
            <div class="mt-4 px-3 text-[11px] uppercase text-slate-400 tracking-wider flex items-center gap-2" x-show="!collapsed">
                <span>Check Sheet</span>
                <span class="h-px flex-1 bg-slate-200"></span>
            </div>

            {{-- List Form --}}
            <a href="{{ route('check_sheets.index') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                {{ request()->routeIs('check_sheets.index','check_sheets.edit')
                    ? 'bg-[#05727d] text-white shadow-sm'
                    : 'text-slate-700 hover:bg-[#e6f1f2] hover:text-[#045058]' }}">
                <div class="w-9 h-9 rounded-lg grid place-items-center
                    {{ request()->routeIs('check_sheets.*') ? 'bg-white/15' : 'bg-slate-100 group-hover:bg-[#cde3e5]' }}">
                    <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6M9 9h6M9 13h6M9 17h6M5 5h.01M5 9h.01M5 13h.01M5 17h.01" />
                    </svg>
                </div>
                <span x-show="!collapsed" x-transition class="font-medium">List Form</span>
            </a>

            {{-- Create Form --}}
            @if($user && $user->isRole(['admin','produksi','qa','logistik']))
            <a href="{{ route('check_sheets.create') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                {{ request()->routeIs('check_sheets.create')
                    ? 'bg-[#05727d] text-white shadow-sm'
                    : 'text-slate-700 hover:bg-[#e6f1f2] hover:text-[#045058]' }}">
                <div class="w-9 h-9 rounded-lg grid place-items-center
                    {{ request()->routeIs('check_sheets.create') ? 'bg-white/15' : 'bg-slate-100 group-hover:bg-[#cde3e5]' }}">
                    <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <span x-show="!collapsed" x-transition>Create Form</span>
            </a>
            @endif

            {{-- Submissions (aktif kalau TANPA status filter) --}}
            @if($user && $user->isRole(['admin','produksi','qa','logistik']))
            <a href="{{ route('check_sheets.submissions') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                {{ request()->routeIs('check_sheets.submissions*') && !request('status')
                    ? 'bg-[#05727d] text-white shadow-sm'
                    : 'text-slate-700 hover:bg-[#e6f1f2] hover:text-[#045058]' }}">
                <div class="w-9 h-9 rounded-lg grid place-items-center
                    {{ request()->routeIs('check_sheets.submissions*') && !request('status')
                        ? 'bg-white/15'
                        : 'bg-slate-100 group-hover:bg-[#cde3e5]' }}">
                    <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v6H4zM4 14h16v6H4z" />
                    </svg>
                </div>
                <span x-show="!collapsed" x-transition>Submissions</span>
            </a>
            @endif

            {{-- Approval Check Sheet (aktif kalau status=submitted) --}}
            @if($user && $user->isRole(['admin','qa','logistik']))
            <a href="{{ route('check_sheets.submissions', ['status'=>'submitted']) }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                {{ request()->routeIs('check_sheets.submissions*') && request('status')=='submitted'
                    ? 'bg-[#05727d] text-white shadow-sm'
                    : 'text-slate-700 hover:bg-[#e6f1f2] hover:text-[#045058]' }}">
                <div class="w-9 h-9 rounded-lg grid place-items-center
                    {{ request()->routeIs('check_sheets.submissions*') && request('status')=='submitted'
                        ? 'bg-white/15'
                        : 'bg-slate-100 group-hover:bg-[#cde3e5]' }}">
                    <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>

                <span x-show="!collapsed" x-transition class="flex-1">Approval Check Sheet</span>

                <span x-show="!collapsed" x-transition
                      class="text-[11px] px-2 py-0.5 rounded-full
                      {{ $badgeCs>0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $badgeCs }}
                </span>
            </a>
            @endif


            {{-- REPORT GROUP --}}
            @if($user && $user->isRole(['admin','produksi','qa','logistik']))
            <div class="mt-4 px-3 text-[11px] uppercase text-slate-400 tracking-wider flex items-center gap-2" x-show="!collapsed">
                <span>Report</span>
                <span class="h-px flex-1 bg-slate-200"></span>
            </div>

            <a href="{{ route('reports.index') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                {{ request()->routeIs('reports.*')
                    ? 'bg-[#05727d] text-white shadow-sm'
                    : 'text-slate-700 hover:bg-[#e6f1f2] hover:text-[#045058]' }}">
                <div class="w-9 h-9 rounded-lg grid place-items-center
                    {{ request()->routeIs('reports.*') ? 'bg-white/15' : 'bg-slate-100 group-hover:bg-[#cde3e5]' }}">
                    <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 15l3-3 3 2 5-5" />
                    </svg>
                </div>
                <span x-show="!collapsed" x-transition>Report & Analytics</span>
            </a>
            @endif


            {{-- USER ACCESS GROUP --}}
            @if($user && $user->isRole(['admin']))
            <div class="mt-4 px-3 text-[11px] uppercase text-slate-400 tracking-wider flex items-center gap-2" x-show="!collapsed">
                <span>Akses User</span>
                <span class="h-px flex-1 bg-slate-200"></span>
            </div>

            <a href="{{ route('users.index') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                {{ request()->routeIs('users.*')
                    ? 'bg-[#05727d] text-white shadow-sm'
                    : 'text-slate-700 hover:bg-[#e6f1f2] hover:text-[#045058]' }}">
                <div class="w-9 h-9 rounded-lg grid place-items-center
                    {{ request()->routeIs('users.*') ? 'bg-white/15' : 'bg-slate-100 group-hover:bg-[#cde3e5]' }}">
                    <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M23 21v-2a4 4 0 00-3-3.87" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a4 4 0 010 7.75" />
                    </svg>
                </div>
                <span x-show="!collapsed" x-transition>Manajemen User</span>
            </a>
            @endif


            {{-- QR GROUP --}}
            @if($user && $user->isRole(['admin','produksi','qa','logistik']))
            <div class="mt-4 px-3 text-[11px] uppercase text-slate-400 tracking-wider flex items-center gap-2" x-show="!collapsed">
                <span>QR</span>
                <span class="h-px flex-1 bg-slate-200"></span>
            </div>

            <a href="{{ route('qr_center.index') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                {{ request()->routeIs('qr_center.*')
                    ? 'bg-[#05727d] text-white shadow-sm'
                    : 'text-slate-700 hover:bg-[#e6f1f2] hover:text-[#045058]' }}">
                <div class="w-9 h-9 rounded-lg grid place-items-center
                    {{ request()->routeIs('qr_center.*') ? 'bg-white/15' : 'bg-slate-100 group-hover:bg-[#cde3e5]' }}">
                    <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h3v3h-3zM17 17h3v3h-3z" />
                    </svg>
                </div>
                <span x-show="!collapsed" x-transition>QR Center</span>
            </a>
            @endif

        </nav>
    </aside>

    {{-- MAIN --}}
    <main class="flex-1 min-w-0">

        <!-- TOPBAR -->
        <header class="h-14 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-6 sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <!-- Hamburger (mobile) -->
                <button
                    class="md:hidden p-2 rounded-lg hover:bg-slate-100 text-[#05727d]"
                    @click="mobileOpen=true"
                    aria-label="Open sidebar">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <h1 class="text-lg font-semibold text-slate-900">
                    @yield('title', 'Dashboard')
                </h1>
            </div>

            {{-- RIGHT AREA: notif + user --}}
            @php
                $photo =
                    $user->photo_url
                    ?? $user->avatar_url
                    ?? ($user->profile_photo_path ?? null);

                if ($photo && !str_starts_with($photo, 'http')) {
                    $photo = \Illuminate\Support\Facades\Storage::disk('public')->url($photo);
                }

                $notifCount = $notifCount ?? 0;
            @endphp

            <div class="flex items-center gap-3">
                {{-- Notif bell --}}
                <button
                    class="relative p-2 rounded-lg hover:bg-slate-100 text-slate-600"
                    title="Notifikasi">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a3 3 0 006 0" />
                    </svg>

                    {{-- Badge count --}}
                    @if($notifCount > 0)
                        <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1
                            rounded-full bg-rose-500 text-white text-[10px]
                            grid place-items-center font-semibold">
                            {{ $notifCount }}
                        </span>
                    @endif
                </button>

                {{-- User dropdown --}}
                <div x-data="{ open:false }" class="relative">
                    <button
                        @click="open=!open"
                        @click.outside="open=false"
                        class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100">
                        {{-- Avatar --}}
                        @if($photo)
                            <img src="{{ $photo }}" alt="avatar"
                                 class="h-9 w-9 rounded-full object-cover ring-2 ring-[#cde3e5]">
                        @else
                            <div class="h-9 w-9 rounded-full bg-[#cde3e5] text-[#045058] grid place-items-center font-bold">
                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                            </div>
                        @endif

                        {{-- Name + role --}}
                        <div class="hidden md:block text-left leading-tight">
                            <div class="text-sm font-semibold text-slate-900 max-w-[140px] truncate">
                                {{ $user->name ?? '-' }}
                            </div>
                            <div class="text-[11px] text-slate-500 uppercase tracking-wide">
                                {{ $user->role ?? '-' }}
                            </div>
                        </div>

                        <svg class="hidden md:block w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                        </svg>
                    </button>

                    {{-- Dropdown menu --}}
                    <div
                        x-show="open"
                        x-transition
                        style="display:none"
                        class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden z-50">
                        <div class="px-4 py-3 border-b border-slate-100">
                            <div class="text-sm font-semibold text-slate-900 truncate">{{ $user->name }}</div>
                            <div class="text-xs text-slate-500 truncate">{{ $user->email }}</div>
                        </div>

                        <a href="#" class="block px-4 py-2 text-sm hover:bg-slate-50">
                            Profile (soon)
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 text-rose-600">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- CONTENT -->
        <div class="p-5 md:p-6 space-y-4">
            @if (session('success'))
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

</div>

@stack('scripts')
</body>
</html>
