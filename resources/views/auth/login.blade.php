<x-layouts.guest title="Iniciar sesión — Ahorvia">
    <div class="mb-6">
        <h2 class="text-xl font-bold tracking-tight text-[#0F172A] dark:text-white">
            Bienvenido a Ahorvia
        </h2>
        <p class="mt-1 text-sm text-[#64748B] dark:text-slate-400">
            Ingresa tus credenciales para acceder a tu panel financiero seguro.
        </p>
    </div>

    @if (session('status'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-[#064E3B] dark:border-emerald-900/60 dark:bg-emerald-950/50 dark:text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-[#EF4444] dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                Correo electrónico
            </label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="nombre@dominio.com"
                class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] placeholder-slate-400 shadow-2xs transition focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            >
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                    Contraseña
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-[#10B981] hover:text-[#064E3B] dark:hover:text-emerald-300">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>
            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] placeholder-slate-400 shadow-2xs transition focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            >
        </div>

        <div class="flex items-center justify-between pt-1">
            <label for="remember" class="inline-flex cursor-pointer items-center gap-2 text-xs font-medium text-[#64748B] dark:text-slate-400">
                <input
                    id="remember"
                    name="remember"
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-[#10B981] focus:ring-[#10B981] dark:border-slate-700 dark:bg-slate-900"
                >
                <span>Mantener sesión segura</span>
            </label>
        </div>

        <button
            type="submit"
            class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#10B981] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-[#064E3B] focus:ring-2 focus:ring-[#10B981] focus:ring-offset-2 focus:outline-none dark:focus:ring-offset-slate-900"
        >
            <span>Acceder a Ahorvia</span>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </button>
    </form>

    @if (Route::has('register'))
        <div class="mt-6 border-t border-slate-100 pt-4 text-center text-xs text-[#64748B] dark:border-slate-800 dark:text-slate-400">
            ¿No tienes una cuenta aún?
            <a href="{{ route('register') }}" class="font-semibold text-[#10B981] hover:text-[#064E3B] dark:hover:text-emerald-300">
                Regístrate en Ahorvia
            </a>
        </div>
    @endif
</x-layouts.guest>

