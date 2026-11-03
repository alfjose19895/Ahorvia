<x-layouts.guest title="Restablecer contraseña — Ahorvia">
    <div class="mb-6">
        <h2 class="text-xl font-bold tracking-tight text-[#0F172A] dark:text-white">
            Definir nueva contraseña
        </h2>
        <p class="mt-1 text-sm text-[#64748B] dark:text-slate-400">
            Elige una contraseña robusta para proteger tu cuenta de Ahorvia.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-[#EF4444] dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                Correo electrónico
            </label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email', $request->email) }}"
                required
                autocomplete="username"
                class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] shadow-2xs focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            >
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                Nueva contraseña
            </label>
            <input
                id="password"
                name="password"
                type="password"
                required
                autofocus
                autocomplete="new-password"
                class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] shadow-2xs focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            >
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                Confirmar nueva contraseña
            </label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] shadow-2xs focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            >
        </div>

        <button
            type="submit"
            class="inline-flex w-full items-center justify-center rounded-xl bg-[#10B981] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#064E3B] focus:ring-2 focus:ring-[#10B981] focus:outline-none"
        >
            Guardar contraseña y continuar
        </button>
    </form>
</x-layouts.guest>

