@php
    /** @var \App\Models\User|null $authUser */
    $authUser = auth()->user();

    $sidebarItems = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'active' => request()->routeIs('dashboard'),
            'badge' => null,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />',
        ],
        [
            'label' => 'Cuentas',
            'route' => 'accounts.index',
            'active' => request()->routeIs('accounts.*'),
            'badge' => null,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" />',
        ],
        [
            'label' => 'Movimientos',
            'route' => 'transactions.index',
            'active' => request()->routeIs('transactions.*'),
            'badge' => null,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />',
        ],
        [
            'label' => 'Presupuestos',
            'route' => 'budgets.index',
            'active' => request()->routeIs('budgets.*'),
            'badge' => null,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />',
        ],
        [
            'label' => 'Deudas',
            'route' => 'debts.index',
            'active' => request()->routeIs('debts.*'),
            'badge' => null,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
        ],
        [
            'label' => 'Tarjetas',
            'route' => 'cards.index',
            'active' => request()->routeIs('cards.*'),
            'badge' => null,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />',
        ],
        [
            'label' => 'Ahorro',
            'route' => 'savings.index',
            'active' => request()->routeIs('savings.*'),
            'badge' => '6M',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />',
        ],
        [
            'label' => 'Reportes',
            'route' => 'reports.index',
            'active' => request()->routeIs('reports.*'),
            'badge' => null,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />',
        ],
        [
            'label' => 'Notificaciones',
            'route' => 'notifications.index',
            'active' => request()->routeIs('notifications.*'),
            'badge' => null,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />',
        ],
        [
            'label' => 'Configuración',
            'route' => 'settings.index',
            'active' => request()->routeIs('settings.*'),
            'badge' => null,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />',
        ],
    ];
@endphp
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

    <title>{{ $title ?? 'Ahorvia — Finanzas Personales' }}</title>

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
    @livewireStyles
</head>
<body
    x-data="{ sidebarOpen: false, quickAddOpen: false }"
    class="min-h-full bg-[#F8FAFC] font-sans text-[#0F172A] antialiased selection:bg-[#10B981]/20 selection:text-[#064E3B] dark:bg-[#0F172A] dark:text-slate-100"
>
    {{-- Drawer lateral móvil --}}
    <div
        x-show="sidebarOpen"
        x-cloak
        class="relative z-50 lg:hidden"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            x-on:click="sidebarOpen = false"
            class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs"
        ></div>

        <div class="fixed inset-0 flex">
            <div
                x-show="sidebarOpen"
                x-transition:enter="transition ease-out duration-200 transform"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-150 transform"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="relative mr-14 flex w-full max-w-xs flex-1 flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800"
            >
                <div class="flex h-16 items-center justify-between px-5 border-b border-slate-100 dark:border-slate-800">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <x-ahorvia-logo size="sm" />
                    </a>
                    <button
                        type="button"
                        x-on:click="sidebarOpen = false"
                        class="rounded-lg p-1.5 text-[#64748B] hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                        aria-label="Cerrar menú"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Menú móvil">
                    @foreach ($sidebarItems as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            class="group flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-medium transition {{ $item['active']
                                ? 'bg-[#10B981]/12 text-[#064E3B] font-semibold dark:bg-[#10B981]/15 dark:text-emerald-300'
                                : 'text-[#64748B] hover:bg-slate-100 hover:text-[#0F172A] dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-white' }}"
                        >
                            <span class="flex items-center gap-3">
                                <svg class="h-5 w-5 shrink-0 {{ $item['active'] ? 'text-[#10B981]' : 'text-slate-400 group-hover:text-[#10B981]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    {!! $item['icon'] !!}
                                </svg>
                                <span>{{ $item['label'] }}</span>
                            </span>
                            @if ($item['badge'])
                                <span class="rounded-full bg-[#10B981]/15 px-2 py-0.5 text-[10px] font-bold text-[#064E3B] dark:text-emerald-300">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                @if ($authUser)
                    <div class="border-t border-slate-200 p-4 dark:border-slate-800">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-[#64748B] transition hover:border-red-200 hover:bg-red-50 hover:text-[#EF4444] dark:border-slate-700 dark:text-slate-300 dark:hover:border-red-900 dark:hover:bg-red-950/40 dark:hover:text-red-300"
                            >
                                <span>Cerrar sesión segura</span>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar fijo Desktop --}}
    <aside class="hidden lg:fixed lg:inset-y-0 lg:z-40 lg:flex lg:w-64 lg:flex-col border-r border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="flex h-16 shrink-0 items-center justify-between px-6 border-b border-slate-100 dark:border-slate-800/80">
            <a href="{{ route('dashboard') }}" class="flex items-center">
                <x-ahorvia-logo size="sm" />
            </a>
            <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-[#064E3B] dark:bg-emerald-950/60 dark:text-emerald-300">
                PWA
            </span>
        </div>

        <div class="px-5 pt-3 pb-1">
            <p class="text-[11px] font-medium text-[#64748B] dark:text-slate-400">
                Tu dinero bajo control. Tus metas más cerca.
            </p>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-3" aria-label="Navegación principal">
            @foreach ($sidebarItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="group flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-medium transition {{ $item['active']
                        ? 'bg-[#10B981]/12 text-[#064E3B] font-semibold dark:bg-[#10B981]/15 dark:text-emerald-300'
                        : 'text-[#64748B] hover:bg-slate-100/80 hover:text-[#0F172A] dark:text-slate-400 dark:hover:bg-slate-800/60 dark:hover:text-white' }}"
                >
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5 shrink-0 {{ $item['active'] ? 'text-[#10B981]' : 'text-slate-400 group-hover:text-[#10B981]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            {!! $item['icon'] !!}
                        </svg>
                        <span>{{ $item['label'] }}</span>
                    </span>
                    @if ($item['badge'])
                        <span class="rounded-full bg-[#10B981]/15 px-2 py-0.5 text-[10px] font-bold text-[#064E3B] dark:text-emerald-300">
                            {{ $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>

        @if ($authUser)
            <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#064E3B] text-xs font-bold text-white">
                        {{ $authUser->initials() }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-semibold text-[#0F172A] dark:text-white">
                            {{ $authUser->name }}
                        </p>
                        <p class="truncate text-[11px] text-[#64748B] dark:text-slate-400">
                            @if ($authUser->hasConfirmedTwoFactor())
                                <span class="text-[#10B981] font-semibold">● 2FA Activo</span>
                            @else
                                <span>2FA disponible</span>
                            @endif
                            · {{ $authUser->currency_code }}
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mt-2.5">
                    @csrf
                    <button
                        type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-[#64748B] transition hover:border-red-200 hover:bg-red-50 hover:text-[#EF4444] dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-red-900/70 dark:hover:bg-red-950/40 dark:hover:text-red-300"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </div>
        @endif
    </aside>

    {{-- Área principal --}}
    <div class="lg:pl-64 flex min-h-screen flex-col">
        {{-- Topbar --}}
        <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center justify-between gap-4 border-b border-slate-200/80 bg-white/90 px-4 backdrop-blur-md sm:px-6 lg:px-8 dark:border-slate-800 dark:bg-slate-900/90">
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    x-on:click="sidebarOpen = true"
                    class="inline-flex items-center justify-center rounded-xl p-2 text-[#64748B] hover:bg-slate-100 hover:text-[#0F172A] lg:hidden dark:text-slate-300 dark:hover:bg-slate-800"
                    aria-label="Abrir menú de navegación"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <a href="{{ route('dashboard') }}" class="lg:hidden">
                    <x-ahorvia-logo size="sm" />
                </a>

                <div class="hidden lg:block">
                    <p class="text-xs font-medium text-[#64748B] dark:text-slate-400">
                        Ahorvia — Finanzas Personales
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <button
                    type="button"
                    x-on:click="quickAddOpen = true"
                    class="hidden sm:inline-flex items-center gap-1.5 rounded-xl bg-[#10B981] px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-[#064E3B]"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Añadir movimiento</span>
                </button>

                <a
                    href="{{ route('notifications.index') }}"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-[#64748B] transition hover:border-[#10B981]/50 hover:text-[#0F172A] dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:text-white"
                    aria-label="Notificaciones"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </a>

                <button
                    type="button"
                    onclick="window.ahorviaTheme && window.ahorviaTheme.toggle()"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-[#64748B] transition hover:border-[#10B981]/50 hover:text-[#0F172A] dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:text-white"
                    aria-label="Cambiar modo claro u oscuro"
                >
                    <svg class="h-4 w-4 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                    <svg class="hidden h-4 w-4 text-amber-400 dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                </button>
            </div>
        </header>

        {{-- Contenido dinámico --}}
        <main class="flex-1 px-4 pt-6 pb-24 sm:px-6 lg:px-8 lg:pb-10">
            <div class="mx-auto max-w-7xl">
                {{ $slot }}
            </div>
        </main>
    </div>

    {{-- Barra inferior móvil (Inicio, Movimientos, Añadir, Metas, Reportes) --}}
    <nav
        aria-label="Navegación móvil inferior"
        class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200/90 bg-white/95 pb-safe backdrop-blur-md lg:hidden dark:border-slate-800 dark:bg-slate-900/95"
    >
        <div class="mx-auto grid max-w-md grid-cols-5 items-center px-2 py-1.5">
            <a
                href="{{ route('dashboard') }}"
                class="flex flex-col items-center justify-center gap-0.5 py-1 text-[11px] font-medium {{ request()->routeIs('dashboard') ? 'text-[#10B981] font-semibold' : 'text-[#64748B] dark:text-slate-400' }}"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                </svg>
                <span>Inicio</span>
            </a>

            <a
                href="{{ route('transactions.index') }}"
                class="flex flex-col items-center justify-center gap-0.5 py-1 text-[11px] font-medium {{ request()->routeIs('transactions.*') ? 'text-[#10B981] font-semibold' : 'text-[#64748B] dark:text-slate-400' }}"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
                <span>Movimientos</span>
            </a>

            <div class="flex flex-col items-center justify-center">
                <button
                    type="button"
                    x-on:click="quickAddOpen = true"
                    class="-mt-5 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-tr from-[#064E3B] to-[#10B981] text-white shadow-lg shadow-emerald-600/30 ring-4 ring-[#F8FAFC] transition active:scale-95 dark:ring-[#0F172A]"
                    aria-label="Añadir ingreso, gasto o transferencia"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </button>
                <span class="mt-0.5 text-[10px] font-semibold text-[#064E3B] dark:text-emerald-300">Añadir</span>
            </div>

            <a
                href="{{ route('savings.index') }}"
                class="flex flex-col items-center justify-center gap-0.5 py-1 text-[11px] font-medium {{ request()->routeIs('savings.*') ? 'text-[#10B981] font-semibold' : 'text-[#64748B] dark:text-slate-400' }}"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                </svg>
                <span>Metas</span>
            </a>

            <a
                href="{{ route('reports.index') }}"
                class="flex flex-col items-center justify-center gap-0.5 py-1 text-[11px] font-medium {{ request()->routeIs('reports.*') ? 'text-[#10B981] font-semibold' : 'text-[#64748B] dark:text-slate-400' }}"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
                <span>Reportes</span>
            </a>
        </div>
    </nav>

    {{-- Modal / Sheet de Acción Rápida (Botón Añadir: Ingreso, Gasto, Transferencia) --}}
    <div
        x-show="quickAddOpen"
        x-cloak
        x-on:keydown.escape.window="quickAddOpen = false"
        class="fixed inset-0 z-50 flex items-end justify-center sm:items-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="quick-add-title"
    >
        <div
            x-show="quickAddOpen"
            x-transition.opacity
            x-on:click="quickAddOpen = false"
            class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs"
        ></div>

        <div
            x-show="quickAddOpen"
            x-transition
            class="relative z-10 w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h3 id="quick-add-title" class="text-base font-bold text-[#0F172A] dark:text-white">
                        Registrar movimiento
                    </h3>
                    <p class="text-xs text-[#64748B] dark:text-slate-400">
                        Selecciona el tipo de operación financiera
                    </p>
                </div>
                <button
                    type="button"
                    x-on:click="quickAddOpen = false"
                    class="rounded-lg p-1.5 text-[#64748B] hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                    aria-label="Cerrar acciones rápidas"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-2.5">
                <a
                    href="{{ route('transactions.index', ['tipo' => 'income']) }}"
                    class="flex items-center gap-3.5 rounded-xl border border-slate-200 p-3.5 transition hover:border-[#10B981] hover:bg-emerald-50/50 dark:border-slate-800 dark:hover:border-[#10B981]/60 dark:hover:bg-emerald-950/30"
                >
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#10B981]/15 text-[#10B981]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0-6.75 6.75M12 4.5l6.75 6.75" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-[#0F172A] dark:text-white">Registrar ingreso</p>
                        <p class="text-xs text-[#64748B] dark:text-slate-400">Sueldo, honorarios, rendimientos o cobros</p>
                    </div>
                </a>

                <a
                    href="{{ route('transactions.index', ['tipo' => 'expense']) }}"
                    class="flex items-center gap-3.5 rounded-xl border border-slate-200 p-3.5 transition hover:border-[#EF4444]/60 hover:bg-red-50/50 dark:border-slate-800 dark:hover:border-[#EF4444]/60 dark:hover:bg-red-950/30"
                >
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#EF4444]/12 text-[#EF4444]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0 6.75-6.75M12 19.5l-6.75-6.75" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-[#0F172A] dark:text-white">Registrar gasto</p>
                        <p class="text-xs text-[#64748B] dark:text-slate-400">Consumo en efectivo, cuenta bancaria o tarjeta</p>
                    </div>
                </a>

                <a
                    href="{{ route('transactions.index', ['tipo' => 'transfer']) }}"
                    class="flex items-center gap-3.5 rounded-xl border border-slate-200 p-3.5 transition hover:border-slate-400 hover:bg-slate-50 dark:border-slate-800 dark:hover:border-slate-600 dark:hover:bg-slate-800/60"
                >
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-[#0F172A] dark:bg-slate-800 dark:text-slate-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-[#0F172A] dark:text-white">Transferencia entre cuentas</p>
                        <p class="text-xs text-[#64748B] dark:text-slate-400">Traspaso interno o pago a tarjeta sin duplicar gasto</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>

