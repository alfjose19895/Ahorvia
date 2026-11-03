<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#10B981">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Ahorvia">

    <title>{{ $title ?? 'Acceso Seguro — Ahorvia' }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">

    <script>
        (function () {
            const saved = localStorage.getItem('ahorvia_theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-[#F8FAFC] font-sans text-[#0F172A] antialiased selection:bg-[#10B981]/20 selection:text-[#064E3B] dark:bg-[#0F172A] dark:text-slate-100">
    <div class="flex min-h-screen flex-col lg:flex-row">
        {{-- Panel lateral de marca (Desktop) --}}
        <aside class="relative hidden lg:flex lg:w-5/12 xl:w-1/2 flex-col justify-between overflow-hidden bg-gradient-to-br from-[#0F172A] via-[#064E3B] to-[#0F172A] p-12 text-white">
            <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-[#10B981]/15 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-[#10B981]/10 blur-3xl"></div>

            <div class="relative z-10">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-tr from-[#064E3B] to-[#10B981] ring-1 ring-white/20 shadow-lg">
                        <svg viewBox="0 0 64 64" fill="none" class="h-6 w-6" aria-hidden="true">
                            <path d="M15 45L27.5 23.5L35.5 34L47 18" stroke="#FFFFFF" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M39 18H47V26" stroke="#FFFFFF" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="47" cy="45" r="4.5" fill="#A7F3D0" />
                        </svg>
                    </span>
                    <span class="text-2xl font-bold tracking-tight text-white">Ahorvia</span>
                </a>
            </div>

            <div class="relative z-10 my-auto max-w-md space-y-6">
                <span class="inline-flex items-center gap-2 rounded-full bg-[#10B981]/15 px-3.5 py-1 text-xs font-semibold tracking-wide text-emerald-300 ring-1 ring-[#10B981]/30">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#10B981]"></span>
                    Sistema Inteligente de Finanzas Personales
                </span>

                <h1 class="text-3xl xl:text-4xl font-bold leading-tight tracking-tight text-white">
                    Tu dinero bajo control.<br>
                    <span class="text-[#10B981]">Tus metas más cerca.</span>
                </h1>

                <p class="text-sm leading-relaxed text-slate-300">
                    Gestiona cuentas, ingresos, gastos, presupuestos, tarjetas y deudas con precisión contable, impulsado por un planificador inteligente de ahorro a seis meses.
                </p>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div class="rounded-xl bg-white/5 p-3.5 ring-1 ring-white/10">
                        <p class="text-xs font-semibold text-emerald-300">Planificador 6M</p>
                        <p class="mt-1 text-xs text-slate-300">Proyección fija y ajustable de capacidad real de ahorro.</p>
                    </div>
                    <div class="rounded-xl bg-white/5 p-3.5 ring-1 ring-white/10">
                        <p class="text-xs font-semibold text-emerald-300">Seguridad Fintech</p>
                        <p class="mt-1 text-xs text-slate-300">Protección 2FA TOTP, sesiones cifradas y partida doble.</p>
                    </div>
                </div>
            </div>

            <div class="relative z-10 flex items-center justify-between text-xs text-slate-400">
                <span>&copy; {{ now()->year }} Ahorvia — Finanzas Personales</span>
                <span>Arquitectura Contable DECIMAL(19,4)</span>
            </div>
        </aside>

        {{-- Contenedor de formulario --}}
        <main class="flex flex-1 flex-col justify-between p-6 sm:p-10">
            <div class="flex items-center justify-between">
                <div class="lg:hidden">
                    <x-ahorvia-logo size="md" :show-slogan="true" />
                </div>
                <div class="ml-auto">
                    <button
                        type="button"
                        onclick="window.ahorviaTheme && window.ahorviaTheme.toggle()"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-[#64748B] shadow-xs transition hover:border-[#10B981]/50 hover:text-[#0F172A] dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:text-white"
                        aria-label="Cambiar tema claro u oscuro"
                    >
                        <svg class="h-4 w-4 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                        </svg>
                        <svg class="hidden h-4 w-4 dark:block text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                        <span>Tema</span>
                    </button>
                </div>
            </div>

            <div class="mx-auto my-8 w-full max-w-md">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-7 sm:p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900/90">
                    {{ $slot }}
                </div>
            </div>

            <footer class="text-center text-xs text-[#64748B] dark:text-slate-500">
                Ahorvia — Tu dinero bajo control. Tus metas más cerca.
            </footer>
        </main>
    </div>
</body>
</html>

