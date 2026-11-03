<x-layouts.guest title="Confirmar contraseña — Ahorvia">
    <div class="mb-6">
        <h2 class="text-xl font-bold tracking-tight text-[#0F172A] dark:text-white">
            Confirmación de seguridad
        </h2>
        <p class="mt-1 text-sm text-[#64748B] dark:text-slate-400">
            Esta es un área protegida de Ahorvia. Confirma tu contraseña actual antes de continuar.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-[#EF4444] dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                Contraseña actual
            </label>
            <input
                id="password"
                name="password"
                type="password"
                required
                autofocus
                autocomplete="current-password"
                class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] shadow-2xs focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            >
        </div>

        <button
            type="submit"
            class="inline-flex w-full items-center justify-center rounded-xl bg-[#10B981] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#064E3B] focus:ring-2 focus:ring-[#10B981] focus:outline-none"
        >
            Confirmar contraseña
        </button>
    </form>
</x-layouts.guest>

