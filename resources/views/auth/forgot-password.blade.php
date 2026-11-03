<x-layouts.guest title="Recuperar contraseña — Ahorvia">
    <div class="mb-6">
        <h2 class="text-xl font-bold tracking-tight text-[#0F172A] dark:text-white">
            Recuperar contraseña
        </h2>
        <p class="mt-1 text-sm text-[#64748B] dark:text-slate-400">
            Ingresa tu correo electrónico registrado y te enviaremos un enlace seguro para restablecer tu acceso.
        </p>
    </div>

    @if (session('status'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-[#064E3B] dark:border-emerald-900/60 dark:bg-emerald-950/50 dark:text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-[#EF4444] dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
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
                placeholder="nombre@dominio.com"
                class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] placeholder-slate-400 shadow-2xs transition focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            >
        </div>

        <button
            type="submit"
            class="inline-flex w-full items-center justify-center rounded-xl bg-[#10B981] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#064E3B] focus:ring-2 focus:ring-[#10B981] focus:outline-none"
        >
            Enviar enlace de recuperación
        </button>
    </form>

    <div class="mt-6 text-center">
        <a href="{{ route('login') }}" class="text-xs font-semibold text-[#10B981] hover:text-[#064E3B] dark:hover:text-emerald-300">
            &larr; Volver al inicio de sesión
        </a>
    </div>
</x-layouts.guest>

