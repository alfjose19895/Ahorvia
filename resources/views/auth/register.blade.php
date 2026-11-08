<x-layouts.guest title="Crear cuenta — Ahorvia">
    <div class="mb-6">
        <h2 class="text-xl font-bold tracking-tight text-[#0F172A] dark:text-white">
            Comienza con Ahorvia
        </h2>
        <p class="mt-1 text-sm text-[#64748B] dark:text-slate-400">
            Tu dinero bajo control. Tus metas más cerca.
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

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                Nombre completo
            </label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                placeholder="Tu nombre y apellido"
                class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] placeholder-slate-400 shadow-2xs transition focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            >
        </div>

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
                autocomplete="username"
                placeholder="nombre@dominio.com"
                class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] placeholder-slate-400 shadow-2xs transition focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            >
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                    Contraseña
                </label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Mínimo 8 caracteres"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] placeholder-slate-400 shadow-2xs transition focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                >
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                    Confirmar
                </label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Repite tu contraseña"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] placeholder-slate-400 shadow-2xs transition focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                >
            </div>
        </div>

        <div>
            <label for="currency_code" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                Moneda principal
            </label>
            <select
                id="currency_code"
                name="currency_code"
                class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] shadow-2xs transition focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            >
                @foreach (config('ahorvia.finance.supported_currencies', ['USD' => ['name' => 'Dólar estadounidense']]) as $code => $currencyData)
                    @php
                        $name = is_array($currencyData) ? ($currencyData['name'] ?? $code) : $currencyData;
                    @endphp
                    <option value="{{ $code }}" @selected(old('currency_code', 'USD') === $code)>
                        {{ $code }} — {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button
            type="submit"
            class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#10B981] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-[#064E3B] focus:ring-2 focus:ring-[#10B981] focus:ring-offset-2 focus:outline-none dark:focus:ring-offset-slate-900"
        >
            <span>Crear mi cuenta en Ahorvia</span>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </button>
    </form>

    <div class="mt-6 border-t border-slate-100 pt-4 text-center text-xs text-[#64748B] dark:border-slate-800 dark:text-slate-400">
        ¿Ya tienes una cuenta registrada?
        <a href="{{ route('login') }}" class="font-semibold text-[#10B981] hover:text-[#064E3B] dark:hover:text-emerald-300">
            Inicia sesión aquí
        </a>
    </div>
</x-layouts.guest>
