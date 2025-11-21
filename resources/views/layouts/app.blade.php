<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>SOP+CheckFlow</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite([]) {{-- kalau belum pakai vite, abaikan aja --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
      /* biar scrollbar sidebar halus */
      .sidebar-scroll::-webkit-scrollbar{ width:6px;}
      .sidebar-scroll::-webkit-scrollbar-thumb{ background:#dbeafe; border-radius:999px;}
    </style>
</head>

<body class="bg-blue-50/40 min-h-screen">
<div x-data="{ mobileOpen:false, collapsed:false }" class="min-h-screen flex">

    <!-- OVERLAY MOBILE -->
    <div
      x-show="mobileOpen"
      x-transition.opacity
      class="fixed inset-0 bg-black/40 z-40 md:hidden"
      @click="mobileOpen=false"
      style="display:none;"
    ></div>

    {{-- SIDEBAR --}}
    <aside
      class="fixed md:static inset-y-0 left-0 z-50 md:z-auto
             bg-white border-r border-blue-100 shadow-sm
             flex flex-col transition-all duration-200"
      :class="[
        mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
        collapsed ? 'w-20' : 'w-64'
      ]"
    >

        <!-- Brand -->
        <div class="px-5 py-4 border-b border-blue-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl bg-blue-600 text-white grid place-items-center shadow">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div x-show="!collapsed" x-transition>
                    <div class="text-base font-bold text-slate-900 leading-tight">PT. DIPSOL INDONESIA</div>
                    <div class="text-[11px] text-slate-500">SOP + CheckFlow</div>
                </div>
            </div>

            <!-- Collapse button (desktop) -->
            <button
              class="hidden md:inline-flex p-2 rounded-lg hover:bg-blue-50 text-blue-600"
              @click="collapsed=!collapsed"
              :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            >
              <svg x-show="!collapsed" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
              </svg>
              <svg x-show="collapsed" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
        </div>

        <!-- Menu -->
        <nav class="flex-1 px-3 py-4 space-y-1 text-sm overflow-y-auto sidebar-scroll">

            {{-- DASHBOARD --}}
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                      {{ request()->routeIs('dashboard')
                          ? 'bg-blue-600 text-white shadow-sm'
                          : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">
                <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9v8a1 1 0 01-1 1h-5v-7H9v7H4a1 1 0 01-1-1z"/>
                </svg>
                <span x-show="!collapsed" x-transition>Dashboard</span>
            </a>

            {{-- SOP GROUP --}}
            <div class="mt-4 px-3 text-[11px] uppercase text-slate-400 tracking-wider" x-show="!collapsed">
              SOP
            </div>

            <a href="{{ route('sop.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                      {{ request()->routeIs('sop.index')
                          ? 'bg-blue-600 text-white shadow-sm'
                          : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">
                <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
                </svg>
                <span x-show="!collapsed" x-transition>SOP Management</span>
            </a>

            <a href="{{ route('sop.approval.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                      {{ request()->routeIs('sop.approval.*')
                          ? 'bg-blue-600 text-white shadow-sm'
                          : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">
                <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span x-show="!collapsed" x-transition>Approval SOP</span>
            </a>

            {{-- CHECK SHEET GROUP --}}
            <div class="mt-4 px-3 text-[11px] uppercase text-slate-400 tracking-wider" x-show="!collapsed">
              Check Sheet
            </div>

            <a href="{{ route('check_sheets.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                      {{ request()->routeIs('check_sheets.index')
                          ? 'bg-blue-600 text-white shadow-sm'
                          : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">
                <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6M9 9h6M9 13h6M9 17h6M5 5h.01M5 9h.01M5 13h.01M5 17h.01"/>
                </svg>
                <span x-show="!collapsed" x-transition>List Form</span>
            </a>

            <a href="{{ route('check_sheets.submissions') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                      {{ request()->routeIs('check_sheets.submissions')
                          ? 'bg-blue-600 text-white shadow-sm'
                          : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">
                <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v6H4zM4 14h16v6H4z"/>
                </svg>
                <span x-show="!collapsed" x-transition>Submissions</span>
            </a>

            {{-- REPORT GROUP --}}
            @if(auth()->user()->isRole(['admin','produksi','qa','logistik']))
              <div class="mt-4 px-3 text-[11px] uppercase text-slate-400 tracking-wider" x-show="!collapsed">
                Report
              </div>

              <a href="{{ route('reports.index') }}"
                 class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                        {{ request()->routeIs('reports.*')
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">
                  <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18"/>
                      <path stroke-linecap="round" stroke-linejoin="round" d="M7 15l3-3 3 2 5-5"/>
                  </svg>
                  <span x-show="!collapsed" x-transition>Report & Analytics</span>
              </a>
            @endif

            {{-- USER ACCESS GROUP --}}
            @if(auth()->user()->isRole(['admin']))
              <div class="mt-4 px-3 text-[11px] uppercase text-slate-400 tracking-wider" x-show="!collapsed">
                Akses User
              </div>

              <a href="{{ route('users.index') }}"
                 class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                        {{ request()->routeIs('users.*')
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">
                  <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2"/>
                      <circle cx="9" cy="7" r="4"/>
                      <path stroke-linecap="round" stroke-linejoin="round" d="M23 21v-2a4 4 0 00-3-3.87"/>
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a4 4 0 010 7.75"/>
                  </svg>
                  <span x-show="!collapsed" x-transition>Manajemen User</span>
              </a>
            @endif

            {{-- QR GROUP --}}
            <div class="mt-4 px-3 text-[11px] uppercase text-slate-400 tracking-wider" x-show="!collapsed">
              QR
            </div>

            <a href="{{ route('qr_center.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                      {{ request()->routeIs('qr_center.*')
                          ? 'bg-blue-600 text-white shadow-sm'
                          : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">
                <svg class="w-5 h-5 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h3v3h-3zM17 17h3v3h-3z"/>
                </svg>
                <span x-show="!collapsed" x-transition>QR Center</span>
            </a>

        </nav>

        <!-- User / Logout -->
        <div class="px-4 py-3 border-t border-blue-100 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2 min-w-0">
                <div class="h-7 w-7 rounded-full bg-blue-100 text-blue-700 grid place-items-center font-semibold">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U',0,1)) }}
                </div>
                <div class="min-w-0" x-show="!collapsed" x-transition>
                    <div class="truncate text-slate-700 font-medium">
                        {{ auth()->user()->name ?? '-' }}
                    </div>
                    <div class="text-[11px] text-slate-400 truncate">
                        {{ auth()->user()->role ?? '-' }}
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" x-show="!collapsed" x-transition>
                @csrf
                <button class="text-blue-600 hover:text-blue-700 font-semibold">Logout</button>
            </form>
        </div>
    </aside>

    {{-- MAIN --}}
    <main class="flex-1 min-w-0">
        <!-- TOPBAR -->
        <header class="h-14 bg-white border-b border-blue-100 flex items-center justify-between px-4 md:px-6 sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <!-- Hamburger (mobile) -->
                <button
                  class="md:hidden p-2 rounded-lg hover:bg-blue-50 text-blue-600"
                  @click="mobileOpen=true"
                  aria-label="Open sidebar"
                >
                  <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                  </svg>
                </button>

                <h1 class="text-lg font-semibold text-slate-900">
                    @yield('title', 'Dashboard')
                </h1>
            </div>

            <div class="text-sm text-slate-500 hidden md:block">
                SOP & Checklist Center
            </div>
        </header>

        <div class="p-6 space-y-4">
            @if (session('success'))
                <div class="rounded-xl bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 text-sm">
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
</body>
</html>
