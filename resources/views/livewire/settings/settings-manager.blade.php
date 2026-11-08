<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#0F172A] dark:text-white">
            Configuración y Seguridad
        </h1>
        <p class="mt-1 text-sm text-[#64748B] dark:text-slate-400">
            Administra tu perfil, seguridad 2FA y los catálogos maestros de opciones (categorías, bancos e instituciones) de Ahorvia.
        </p>
    </div>

    {{-- Selector de Pestañas Maestras --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-2 dark:border-slate-800">
        <button
            type="button"
            wire:click="setTab('profile')"
            class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold transition {{ $activeTab === 'profile' ? 'bg-[#0F172A] text-white shadow-2xs dark:bg-emerald-500 dark:text-slate-950' : 'text-[#64748B] hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800' }}"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            <span>Perfil y Seguridad</span>
        </button>

        <button
            type="button"
            wire:click="setTab('categories')"
            class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold transition {{ $activeTab === 'categories' ? 'bg-[#0F172A] text-white shadow-2xs dark:bg-emerald-500 dark:text-slate-950' : 'text-[#64748B] hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800' }}"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.386a11.196 11.196 0 0 0 4.14-4.14c.486-.827.313-1.908-.386-2.607L10.159 3.659A2.25 2.25 0 0 0 9.568 3Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
            </svg>
            <span>Categorías Maestras</span>
            <span class="rounded-full bg-slate-200/60 px-2 py-0.5 text-[10px] text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                {{ $categories->count() }}
            </span>
        </button>

        <button
            type="button"
            wire:click="setTab('institutions')"
            class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold transition {{ $activeTab === 'institutions' ? 'bg-[#0F172A] text-white shadow-2xs dark:bg-emerald-500 dark:text-slate-950' : 'text-[#64748B] hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800' }}"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5m-15 10.5V10.5m18 10.5H3" />
            </svg>
            <span>Bancos e Instituciones</span>
            <span class="rounded-full bg-slate-200/60 px-2 py-0.5 text-[10px] text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                {{ $institutions->count() }}
            </span>
        </button>
    </div>

    {{-- PESTAÑA 1: PERFIL Y SEGURIDAD --}}
    @if ($activeTab === 'profile')
        {{-- Sección 1: Perfil y Preferencias Financieras --}}
        <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 pb-4 dark:border-slate-800">
                <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                    Perfil de usuario y preferencias
                </h2>
                <p class="text-xs text-[#64748B] dark:text-slate-400">
                    Información del titular de la cuenta y formato monetario principal.
                </p>
            </div>

            @if ($profileStatus)
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-[#064E3B] dark:border-emerald-900/60 dark:bg-emerald-950/50 dark:text-emerald-300">
                    {{ $profileStatus }}
                </div>
            @endif

            <form wire:submit="updateProfile" class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="profile-name" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                        Nombre completo
                    </label>
                    <input
                        id="profile-name"
                        type="text"
                        wire:model="name"
                        required
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    >
                    @error('name')
                        <p class="mt-1 text-xs text-[#EF4444]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="profile-email" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                        Correo electrónico
                    </label>
                    <input
                        id="profile-email"
                        type="email"
                        wire:model="email"
                        required
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    >
                    @error('email')
                        <p class="mt-1 text-xs text-[#EF4444]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="profile-currency" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                        Moneda contable principal
                    </label>
                    <select
                        id="profile-currency"
                        wire:model="currency_code"
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    >
                        @foreach ($currencies as $code => $curr)
                            <option value="{{ $code }}">
                                {{ $code }} — {{ is_array($curr) ? $curr['name'] : $curr }} ({{ is_array($curr) ? $curr['symbol'] : '$' }})
                            </option>
                        @endforeach
                    </select>
                    @error('currency_code')
                        <p class="mt-1 text-xs text-[#EF4444]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="profile-timezone" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                        Zona horaria
                    </label>
                    <select
                        id="profile-timezone"
                        wire:model="timezone"
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    >
                        @foreach ($commonTimezones as $tzCode => $tzLabel)
                            <option value="{{ $tzCode }}">{{ $tzLabel }}</option>
                        @endforeach
                    </select>
                    @error('timezone')
                        <p class="mt-1 text-xs text-[#EF4444]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2 flex justify-end pt-2">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-xl bg-[#10B981] px-5 py-2.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#064E3B]"
                    >
                        Guardar cambios de perfil
                    </button>
                </div>
            </form>
        </section>

        {{-- Sección 2: Actualización de Contraseña --}}
        <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 pb-4 dark:border-slate-800">
                <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                    Seguridad de la contraseña
                </h2>
                <p class="text-xs text-[#64748B] dark:text-slate-400">
                    Asegúrate de utilizar una clave robusta para proteger tus datos financieros.
                </p>
            </div>

            @if ($passwordStatus)
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-[#064E3B] dark:border-emerald-900/60 dark:bg-emerald-950/50 dark:text-emerald-300">
                    {{ $passwordStatus }}
                </div>
            @endif

            <form wire:submit="updatePassword" class="mt-5 max-w-xl space-y-4">
                <div>
                    <label for="current_password" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                        Contraseña actual
                    </label>
                    <input
                        id="current_password"
                        type="password"
                        wire:model="current_password"
                        required
                        autocomplete="current-password"
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    >
                    @error('current_password')
                        <p class="mt-1 text-xs text-[#EF4444]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                            Nueva contraseña
                        </label>
                        <input
                            id="password"
                            type="password"
                            wire:model="password"
                            required
                            autocomplete="new-password"
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        >
                        @error('password')
                            <p class="mt-1 text-xs text-[#EF4444]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                            Confirmar nueva clave
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            wire:model="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        >
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-xl bg-[#0F172A] px-5 py-2.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700"
                    >
                        Actualizar contraseña
                    </button>
                </div>
            </form>
        </section>

        {{-- Sección 3: Autenticación de Dos Factores (2FA) --}}
        <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 pb-4 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                            Autenticación de dos factores (2FA)
                        </h2>
                        <p class="text-xs text-[#64748B] dark:text-slate-400">
                            Agrega una capa adicional de seguridad a tu cuenta mediante Google Authenticator o apps TOTP compatibles.
                        </p>
                    </div>
                    <div>
                        @if ($user->hasConfirmedTwoFactor())
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-[#064E3B] dark:bg-emerald-950/60 dark:text-emerald-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#10B981]"></span>
                                2FA Activo
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                Inactivo
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            @if ($twoFactorStatus)
                <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-xs font-semibold text-blue-900 dark:border-blue-900/60 dark:bg-blue-950/50 dark:text-blue-300">
                    {{ $twoFactorStatus }}
                </div>
            @endif

            <div class="mt-5 space-y-4">
                @if ($showingQrCode)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5 dark:border-slate-800 dark:bg-slate-950/50">
                        <p class="text-xs font-semibold text-[#0F172A] dark:text-white">
                            1. Escanea este código QR con tu app de autenticación (Google Authenticator, Authy, 1Password):
                        </p>
                        <div class="mt-4 flex justify-center rounded-xl bg-white p-4 shadow-2xs dark:bg-white inline-block">
                            {!! $user->twoFactorQrCodeSvg() !!}
                        </div>
                    </div>
                @endif

                @if ($showingConfirmation)
                    <form wire:submit="confirmTwoFactor" class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-5 dark:border-emerald-900/60 dark:bg-emerald-950/20">
                        <p class="text-xs font-semibold text-[#064E3B] dark:text-emerald-300">
                            2. Ingresa el código numérico de 6 dígitos que muestra tu app:
                        </p>
                        <div class="mt-3 flex items-center gap-3">
                            <input
                                type="text"
                                wire:model="twoFactorCode"
                                maxlength="6"
                                placeholder="123456"
                                class="w-36 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-center text-lg font-mono tracking-widest text-[#0F172A] focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            >
                            <button
                                type="submit"
                                class="rounded-xl bg-[#10B981] px-4 py-2.5 text-xs font-semibold text-white shadow-2xs hover:bg-[#064E3B]"
                            >
                                Confirmar y Activar
                            </button>
                        </div>
                        @error('twoFactorCode')
                            <p class="mt-2 text-xs text-[#EF4444]">{{ $message }}</p>
                        @enderror
                    </form>
                @endif

                @if ($showingRecoveryCodes)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-5 dark:border-amber-900/60 dark:bg-amber-950/20">
                        <h4 class="text-xs font-bold text-amber-900 dark:text-amber-300">
                            Códigos de Recuperación de Emergencia
                        </h4>
                        <p class="mt-1 text-xs text-amber-800 dark:text-amber-400">
                            Guarda estos códigos en un lugar seguro. Si pierdes el acceso a tu autenticador, cada código puede usarse una única vez.
                        </p>
                        <div class="mt-3 grid grid-cols-2 gap-2 font-mono text-xs text-slate-800 dark:text-slate-200">
                            @foreach ($user->recoveryCodes() as $code)
                                <div class="rounded-lg bg-white px-3 py-1.5 shadow-2xs dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                                    {{ $code }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    @if (! $user->hasConfirmedTwoFactor())
                        <div class="flex items-center gap-2">
                            <input
                                type="password"
                                wire:model="twoFactorPassword"
                                placeholder="Tu contraseña para habilitar"
                                class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-[#0F172A] dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            >
                            <button
                                type="button"
                                wire:click="enableTwoFactor"
                                class="inline-flex items-center rounded-xl bg-[#10B981] px-4 py-2 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#064E3B]"
                            >
                                Iniciar configuración 2FA
                            </button>
                        </div>
                        @error('twoFactorPassword')
                            <p class="w-full text-xs text-[#EF4444]">{{ $message }}</p>
                        @enderror
                    @else
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                wire:click="showRecoveryCodes"
                                class="rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                Ver códigos de respaldo
                            </button>
                            <button
                                type="button"
                                wire:click="regenerateRecoveryCodes"
                                class="rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                Regenerar códigos
                            </button>
                            <button
                                type="button"
                                wire:click="disableTwoFactor"
                                class="rounded-xl border border-red-200 bg-red-50/60 px-3.5 py-2 text-xs font-semibold text-red-600 hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-400"
                            >
                                Desactivar 2FA
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- PESTAÑA 2: CATÁLOGO MAESTRO DE CATEGORÍAS --}}
    @if ($activeTab === 'categories')
        <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4 dark:border-slate-800">
                <div>
                    <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                        Catálogo Maestro de Categorías
                    </h2>
                    <p class="text-xs text-[#64748B] dark:text-slate-400">
                        Crea, personaliza o archiva las opciones de clasificación para tus gastos e ingresos.
                    </p>
                </div>
                <button
                    type="button"
                    wire:click="openCreateCategoryModal('expense')"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-[#10B981] px-4 py-2 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#064E3B]"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Nueva Categoría</span>
                </button>
            </div>

            @if ($categoryStatus)
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-[#064E3B] dark:border-emerald-900/60 dark:bg-emerald-950/50 dark:text-emerald-300">
                    {{ $categoryStatus }}
                </div>
            @endif

            {{-- Filtros de categorías --}}
            <div class="mt-4 flex items-center gap-2">
                <button
                    type="button"
                    wire:click="$set('categoryFilter', 'all')"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $categoryFilter === 'all' ? 'bg-[#0F172A] text-white dark:bg-slate-800' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800/60 dark:text-slate-400' }}"
                >
                    Todas ({{ $categories->count() }})
                </button>
                <button
                    type="button"
                    wire:click="$set('categoryFilter', 'expense')"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $categoryFilter === 'expense' ? 'bg-red-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800/60 dark:text-slate-400' }}"
                >
                    Gastos ({{ $categories->where('type', \App\Enums\CategoryType::Expense)->count() }})
                </button>
                <button
                    type="button"
                    wire:click="$set('categoryFilter', 'income')"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $categoryFilter === 'income' ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800/60 dark:text-slate-400' }}"
                >
                    Ingresos ({{ $categories->where('type', \App\Enums\CategoryType::Income)->count() }})
                </button>
            </div>

            {{-- Grid de categorías --}}
            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($categories as $cat)
                    <div class="flex items-center justify-between rounded-xl border border-slate-200/80 p-3.5 transition hover:shadow-xs dark:border-slate-800 dark:bg-slate-950/40 {{ ! $cat->is_active ? 'opacity-50' : '' }}">
                        <div class="flex items-center gap-3">
                            <span
                                class="h-4 w-4 shrink-0 rounded-full"
                                style="background-color: {{ $cat->color ?: '#10B981' }}"
                            ></span>
                            <div>
                                <h3 class="text-xs font-bold text-[#0F172A] dark:text-white">
                                    {{ $cat->name }}
                                </h3>
                                <div class="mt-0.5 flex items-center gap-2 text-[10px] text-slate-400">
                                    <span class="font-semibold {{ $cat->type->value === 'income' ? 'text-emerald-500' : 'text-red-400' }}">
                                        {{ $cat->type->value === 'income' ? 'Ingreso' : 'Gasto' }}
                                    </span>
                                    <span>•</span>
                                    <span>{{ $cat->journal_lines_count }} movs</span>
                                    @if (! $cat->is_active)
                                        <span class="rounded bg-slate-200 px-1 text-slate-600 dark:bg-slate-800 dark:text-slate-400">Archivada</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                wire:click="openEditCategoryModal({{ $cat->id }})"
                                class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                title="Editar categoría"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>

                            <button
                                type="button"
                                wire:click="toggleCategoryStatus({{ $cat->id }})"
                                class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                title="{{ $cat->is_active ? 'Desactivar / Archivar' : 'Activar' }}"
                            >
                                @if ($cat->is_active)
                                    <svg class="h-3.5 w-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                @else
                                    <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                @endif
                            </button>

                            <button
                                type="button"
                                wire:click="deleteCategory({{ $cat->id }})"
                                wire:confirm="¿Seguro que deseas eliminar/archivar esta categoría?"
                                class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-950/40"
                                title="Eliminar categoría"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center text-xs text-slate-400">
                        No hay categorías registradas en esta vista.
                    </div>
                @endforelse
            </div>
        </section>
    @endif

    {{-- PESTAÑA 3: CATÁLOGO MAESTRO DE BANCOS E INSTITUCIONES --}}
    @if ($activeTab === 'institutions')
        <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4 dark:border-slate-800">
                <div>
                    <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                        Catálogo Maestro de Bancos e Instituciones
                    </h2>
                    <p class="text-xs text-[#64748B] dark:text-slate-400">
                        Entidades financieras y emisores para seleccionar en Cuentas, Tarjetas y Préstamos.
                    </p>
                </div>
                <button
                    type="button"
                    wire:click="openCreateInstitutionModal"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-[#10B981] px-4 py-2 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#064E3B]"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Nuevo Banco / Entidad</span>
                </button>
            </div>

            @if ($institutionStatus)
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-[#064E3B] dark:border-emerald-900/60 dark:bg-emerald-950/50 dark:text-emerald-300">
                    {{ $institutionStatus }}
                </div>
            @endif

            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($institutions as $inst)
                    <div class="flex items-center justify-between rounded-xl border border-slate-200/80 p-3.5 transition hover:shadow-xs dark:border-slate-800 dark:bg-slate-950/40 {{ ! $inst->is_active ? 'opacity-50' : '' }}">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5m-15 10.5V10.5m18 10.5H3" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold text-[#0F172A] dark:text-white">
                                    {{ $inst->name }}
                                </h3>
                                <p class="text-[10px] text-slate-400">
                                    {{ $inst->is_active ? 'Activo en selectores' : 'Desactivado' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                wire:click="openEditInstitutionModal({{ $inst->id }})"
                                class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                title="Editar institución"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>

                            <button
                                type="button"
                                wire:click="toggleInstitutionStatus({{ $inst->id }})"
                                class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                title="{{ $inst->is_active ? 'Desactivar' : 'Activar' }}"
                            >
                                @if ($inst->is_active)
                                    <svg class="h-3.5 w-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                @else
                                    <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                @endif
                            </button>

                            <button
                                type="button"
                                wire:click="deleteInstitution({{ $inst->id }})"
                                wire:confirm="¿Seguro que deseas eliminar este banco del catálogo?"
                                class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-950/40"
                                title="Eliminar banco"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center text-xs text-slate-400">
                        No hay bancos o instituciones registradas.
                    </div>
                @endforelse
            </div>
        </section>
    @endif

    {{-- Modal: Crear / Editar Categoría --}}
    @if ($showCategoryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-[#0F172A] dark:text-white">
                        {{ $editingCategoryId ? 'Editar Categoría' : 'Nueva Categoría Maestra' }}
                    </h3>
                    <button type="button" wire:click="closeCategoryModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveCategory" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Nombre de la categoría *</label>
                        <input
                            type="text"
                            wire:model="categoryName"
                            placeholder="Ej. Mascotas, Streaming, Cursos..."
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            autofocus
                        />
                        @error('categoryName') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Tipo *</label>
                            <select
                                wire:model="categoryType"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            >
                                <option value="expense">Gasto</option>
                                <option value="income">Ingreso</option>
                            </select>
                            @error('categoryType') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Color distintivo *</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input
                                    type="color"
                                    wire:model="categoryColor"
                                    class="h-8 w-12 cursor-pointer rounded-lg border border-slate-200 bg-transparent p-0.5"
                                />
                                <span class="text-xs font-mono text-slate-500">{{ $categoryColor }}</span>
                            </div>
                            @error('categoryColor') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            wire:click="closeCategoryModal"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-[#64748B] hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="rounded-xl bg-[#10B981] px-5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-[#064E3B]"
                        >
                            {{ $editingCategoryId ? 'Actualizar' : 'Guardar Categoría' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal: Crear / Editar Institución / Banco --}}
    @if ($showInstitutionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-[#0F172A] dark:text-white">
                        {{ $editingInstitutionId ? 'Editar Banco / Entidad' : 'Nuevo Banco / Entidad' }}
                    </h3>
                    <button type="button" wire:click="closeInstitutionModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveInstitution" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Nombre del Banco / Cooperativa *</label>
                        <input
                            type="text"
                            wire:model="institutionName"
                            placeholder="Ej. Banco Guayaquil, Pichincha, Chase..."
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            autofocus
                        />
                        @error('institutionName') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            wire:click="closeInstitutionModal"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-[#64748B] hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="rounded-xl bg-[#10B981] px-5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-[#064E3B]"
                        >
                            {{ $editingInstitutionId ? 'Actualizar' : 'Guardar Banco' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
