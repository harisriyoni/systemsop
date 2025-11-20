<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Login - SOP+CheckFlow</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 min-h-screen flex items-center justify-center">
  <div class="w-full max-w-md bg-slate-900/80 border border-slate-700 rounded-2xl p-6 shadow-xl">
    <h1 class="text-xl font-semibold text-white mb-1">Masuk ke SOP+CheckFlow</h1>
    <p class="text-xs text-slate-400 mb-6">Gunakan akun demo yang sudah dibuat.</p>

    @if ($errors->any())
      <div class="mb-4 text-sm rounded-md bg-rose-900/40 border border-rose-700 text-rose-100 px-3 py-2">
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-xs text-slate-300 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email') }}"
               class="w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
      </div>
      <div>
        <label class="block text-xs text-slate-300 mb-1">Password</label>
        <input type="password" name="password"
               class="w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
      </div>
      <div class="flex items-center justify-between text-xs text-slate-400">
        <div>
          Demo: <code class="bg-slate-800 px-1 rounded">admin@demo.test</code> / <code>password</code>
        </div>
      </div>
      <button type="submit"
              class="w-full mt-2 inline-flex justify-center rounded-lg bg-emerald-500 hover:bg-emerald-600 text-sm font-medium text-white px-3 py-2">
        Login
      </button>
    </form>
  </div>
</body>
</html>
