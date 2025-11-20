<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>SOP+CheckFlow</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite([]) {{-- kalau belum pakai vite, abaikan aja --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 min-h-screen">
    <div class="min-h-screen flex">

        {{-- SIDEBAR --}}
        <aside class="w-64 bg-slate-900 text-slate-100 flex flex-col">
            <div class="px-6 py-4 border-b border-slate-800">
                <div class="text-lg font-bold">SOP+CheckFlow</div>
                <div class="text-xs text-slate-400">SOP selalu versi terbaru</div>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center px-3 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-slate-800' : 'hover:bg-slate-800/50' }}">
                    Dashboard
                </a>

                <div class="mt-4 text-[11px] uppercase text-slate-400 tracking-wider">SOP</div>

                {{-- List SOP --}}
                <a href="{{ route('sop.index') }}"
                    class="block px-3 py-2 rounded-lg {{ request()->routeIs('sop.index') ? 'bg-slate-800' : 'hover:bg-slate-800/50' }}">
                    List SOP
                </a>

                {{-- Approval SOP --}}
                <a href="{{ route('sop.approval.index') }}"
                    class="block px-3 py-2 rounded-lg {{ request()->routeIs('sop.approval.*') ? 'bg-slate-800' : 'hover:bg-slate-800/50' }}">
                    Approval SOP
                </a>


                <div class="mt-4 text-[11px] uppercase text-slate-400 tracking-wider">Check Sheet</div>
                <a href="{{ route('check_sheets.index') }}"
                    class="block px-3 py-2 rounded-lg {{ request()->routeIs('check_sheets.index') ? 'bg-slate-800' : 'hover:bg-slate-800/50' }}">
                    List Form
                </a>
                <a href="{{ route('check_sheets.submissions') }}"
                    class="block px-3 py-2 rounded-lg {{ request()->routeIs('check_sheets.submissions') ? 'bg-slate-800' : 'hover:bg-slate-800/50' }}">
                    Submissions
                </a>

                <div class="mt-4 text-[11px] uppercase text-slate-400 tracking-wider">QR</div>
                <a href="{{ route('qr_center.index') }}"
                    class="block px-3 py-2 rounded-lg {{ request()->routeIs('qr_center.*') ? 'bg-slate-800' : 'hover:bg-slate-800/50' }}">
                    QR Center
                </a>
            </nav>

            <div class="px-4 py-3 border-t border-slate-800 text-xs flex items-center justify-between">
                <span>{{ auth()->user()->name ?? '-' }} ({{ auth()->user()->role ?? '-' }})</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-rose-400 hover:text-rose-300">Logout</button>
                </form>
            </div>
        </aside>

        {{-- MAIN --}}
        <main class="flex-1">
            <header class="h-14 bg-white border-b border-slate-200 flex items-center justify-between px-6">
                <div>
                    <h1 class="text-lg font-semibold">@yield('title', 'Dashboard')</h1>
                </div>
            </header>

            <div class="p-6 space-y-4">
                @if (session('success'))
                    <div class="rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="rounded-md bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>

</html>
