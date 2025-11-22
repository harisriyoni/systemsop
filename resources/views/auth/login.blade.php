<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Login - SOP+CheckFlow</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font modern -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    html, body { font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
    :root{
      --primary:#05727d;
      --primary-dark:#045058;
      --primary-soft:#e6f1f2;
      --border:#cde3e5;
    }
  </style>

  <!-- Alpine buat show/hide password -->
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-white via-[var(--primary-soft)] to-white">
  <div class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-6xl">

      <!-- CARD WRAPPER -->
      <div class="bg-white border border-[var(--border)] rounded-3xl
                  shadow-[0_18px_60px_-25px_rgba(5,114,125,0.45)]
                  overflow-hidden grid grid-cols-1 lg:grid-cols-5">

        <!-- HERO IMAGE -->
        <div class="relative lg:col-span-3 order-1 lg:order-2 min-h-[220px] sm:min-h-[280px] lg:min-h-full">
          <img
            src="{{ asset('assets/images/sop.jpg') }}"
            alt="Chemical Manufacturing"
            class="absolute inset-0 h-full w-full object-cover"
          />
          <!-- overlay brand -->
          <div class="absolute inset-0 bg-gradient-to-t from-[#022f33]/85 via-[#05727d]/35 to-transparent"></div>

          <!-- CAPTION -->
          <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-8 text-white">
            <div class="text-xs sm:text-sm text-white/85 font-medium">PT. DIPSOL INDONESIA</div>
            <div class="text-xl sm:text-2xl lg:text-3xl font-semibold leading-tight mt-1">
              SOP & Check Sheet Digital
            </div>
            <div class="text-xs sm:text-sm text-white/90 mt-2 max-w-xl">
              Lebih cepat, aman, dan terpantau untuk operasional chemical manufacturing.
            </div>

            <div class="mt-3 sm:mt-4 flex flex-wrap gap-2 text-[10px] sm:text-[11px]">
              <span class="px-2.5 py-1 rounded-full bg-white/10 border border-white/20">Approval Multi-Dept</span>
              <span class="px-2.5 py-1 rounded-full bg-white/10 border border-white/20">QR Operator Access</span>
              <span class="px-2.5 py-1 rounded-full bg-white/10 border border-white/20">Audit Trail</span>
            </div>
          </div>
        </div>

        <!-- FORM SIDE -->
        <div class="lg:col-span-2 order-2 lg:order-1 p-6 sm:p-9">
          <!-- Header / CTA -->
          <div class="flex items-center gap-3 mb-6">
            <div class="h-11 w-11 rounded-xl bg-[var(--primary)] text-white grid place-items-center shadow-md shrink-0">
              {{-- kalau mau pakai logo, tinggal ganti svg ke img --}}
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <div>
              <h1 class="text-lg sm:text-xl font-semibold text-slate-900 leading-tight">
                SELAMAT DATANG DI SISTEM SOP
              </h1>
              <p class="text-xs sm:text-sm text-slate-500">
                Chemical Manufacturing SOP & Check Sheet Center
              </p>
            </div>
          </div>

          <!-- Error -->
          @if ($errors->any())
            <div class="mb-4 text-sm rounded-xl bg-[var(--primary-soft)] border border-[var(--border)] text-[#04454b] px-3 py-2">
              {{ $errors->first() }}
            </div>
          @endif

          <!-- Form -->
          <form method="POST" action="{{ route('login.post') }}" class="space-y-4" x-data="{ showPw:false }">
            @csrf

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
              <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="admin@demo.test"
                class="w-full rounded-xl bg-white border border-slate-200 text-slate-900 text-sm px-4 py-3
                       placeholder:text-slate-400
                       focus:outline-none focus:ring-4 focus:ring-[var(--primary-soft)]
                       focus:border-[var(--primary)] transition"
                required
              >
            </div>

            <!-- Password -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
              <div class="relative">
                <input
                  :type="showPw ? 'text' : 'password'"
                  name="password"
                  placeholder="••••••••"
                  class="w-full rounded-xl bg-white border border-slate-200 text-slate-900 text-sm px-4 py-3 pr-12
                         placeholder:text-slate-400
                         focus:outline-none focus:ring-4 focus:ring-[var(--primary-soft)]
                         focus:border-[var(--primary)] transition"
                  required
                >
                <button type="button"
                        class="absolute right-2 top-1/2 -translate-y-1/2 h-9 w-9 grid place-items-center rounded-lg
                               text-slate-500 hover:text-[var(--primary-dark)] hover:bg-[var(--primary-soft)] transition"
                        @click="showPw=!showPw"
                        :aria-label="showPw ? 'Hide password' : 'Show password'">
                  <svg x-show="!showPw" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  <svg x-show="showPw" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.042-3.368M9.88 9.88a3 3 0 104.243 4.243"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Button -->
            <button
              type="submit"
              class="w-full inline-flex justify-center items-center gap-2 rounded-xl
                     bg-[var(--primary)] hover:bg-[var(--primary-dark)] active:bg-[#033b40]
                     text-sm font-semibold text-white px-4 py-3
                     shadow-md shadow-[rgba(5,114,125,0.35)] transition"
            >
              Login & Mulai
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </form>

          <!-- DEMO ACCOUNTS -->
          <div class="mt-5 bg-[var(--primary-soft)]/70 border border-[var(--border)] rounded-2xl p-4">
            <div class="text-xs font-semibold text-slate-800 mb-2 flex items-center gap-2">
              <span class="h-2 w-2 rounded-full bg-[var(--primary)]"></span>
              Akun Demo (Password: <span class="font-bold">password</span>)
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
              @php
                $demoUsers = [
                  ['role'=>'Admin',     'email'=>'admin@demo.test'],
                  ['role'=>'Produksi',  'email'=>'produksi@demo.test'],
                  ['role'=>'QA',        'email'=>'qa@demo.test'],
                  ['role'=>'Logistik',  'email'=>'logistik@demo.test'],
                  ['role'=>'Operator',  'email'=>'operator@demo.test'],
                ];
              @endphp

              @foreach($demoUsers as $d)
                <div class="bg-white border border-[var(--border)] rounded-xl px-3 py-2 flex items-center justify-between">
                  <div class="min-w-0">
                    <div class="font-semibold text-slate-800">{{ $d['role'] }}</div>
                    <div class="text-[11px] text-slate-500 truncate">{{ $d['email'] }}</div>
                  </div>
                  <span class="text-[11px] px-2 py-1 rounded-full bg-[var(--primary)] text-white font-semibold shrink-0">
                    password
                  </span>
                </div>
              @endforeach
            </div>
          </div>

          <p class="mt-6 text-center text-xs text-slate-400">
            © {{ date('Y') }} SOP+CheckFlow — Chemical Manufacturing
          </p>
        </div>

      </div>

      <p class="text-center text-xs text-slate-400 mt-4">
        Pastikan email & password benar.
      </p>
    </div>
  </div>
</body>
</html>
