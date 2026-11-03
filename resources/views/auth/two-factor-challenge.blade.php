<x-layouts.guest title="Verificación 2FA — Ahorvia">
    <div x-data="{ recovery: false }">
        <div class="mb-6">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#10B981]/10 px-3 py-1 text-xs font-semibold text-[#064E3B] dark:text-emerald-300">
                Seguridad de Dos Factores (2FA)
            </span>
            <h2 class="mt-3 text-xl font-bold tracking-tight text-[#0F172A] dark:text-white">
                Verificación de identidad
            </h2>
            <p x-show="!recovery" class="mt-1 text-sm text-[#64748B] dark:text-slate-400">
                Ingresa el código temporal de 6 dígitos generado por tu aplicación autenticadora.
            </p>
            <p x-show="recovery" x-cloak class="mt-1 text-sm text-[#64748B] dark:text-slate-400">
                Ingresa uno de tus códigos de recuperación de emergencia para acceder a Ahorvia.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-[#EF4444] dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-4">
            @csrf

            <div x-show="!recovery">
                <label for="code" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                    Código de autenticación (6 dígitos)
                </label>
                <input
                    id="code"
                    name="code"
                    type="text"
                    inputmode="numeric"
                    autofocus
                    x-ref="code"
                    autocomplete="one-time-code"
                    placeholder="000000"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-center font-mono text-lg tracking-[0.3em] text-[#0F172A] shadow-2xs focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                >
            </div>

            <div x-show="recovery" x-cloak>
                <label for="recovery_code" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                    Código de recuperación
                </label>
                <input
                    id="recovery_code"
                    name="recovery_code"
                    type="text"
                    x-ref="recovery_code"
                    autocomplete="one-time-code"
                    placeholder="xxxxx-xxxxx"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 font-mono text-sm text-[#0F172A] shadow-2xs focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                >
            </div>

            <button
                type="submit"
                class="inline-flex w-full items-center justify-center rounded-xl bg-[#10B981] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#064E3B] focus:ring-2 focus:ring-[#10B981] focus:outline-none"
            >
                Verificar y acceder
            </button>

            <div class="pt-2 text-center">
                <button
                    type="button"
                    class="text-xs font-semibold text-[#10B981] hover:text-[#064E3B] dark:hover:text-emerald-300"
                    x-on:click="
                        recovery = !recovery;
                        $nextTick(() => {
                            if (recovery) {
                                $refs.code.value = '';
                                $refs.recovery_code.focus();
                            } else {
                                $refs.recovery_code.value = '';
                                $refs.code.focus();
                            }
                        });
                    "
                >
                    <span x-show="!recovery">Usar un código de recuperación</span>
                    <span x-show="recovery" x-cloak>Usar un código de aplicación autenticadora</span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.guest>

