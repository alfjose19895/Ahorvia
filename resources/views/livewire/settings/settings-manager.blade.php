<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#0F172A] dark:text-white">
            Configuración y Seguridad
        </h1>
        <p class="mt-1 text-sm text-[#64748B] dark:text-slate-400">
            Administra tu perfil personal, moneda contable preferida, contraseña y autenticación de dos factores (2FA).
        </p>
    </div>

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
                    Moneda principal
                </label>
                <select
                    id="profile-currency"
                    wire:model="currency_code"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                >
                    @foreach ($currencies as $code => $meta)
                        <option value="{{ $code }}">{{ $code }} — {{ $meta['name'] }} ({{ $meta['symbol'] }})</option>
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
                    @foreach ($commonTimezones as $tz => $label)
                        <option value="{{ $tz }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('timezone')
                    <p class="mt-1 text-xs text-[#EF4444]">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2 flex justify-end pt-2">
                <button
                    type="submit"
                    class="inline-flex items-center rounded-xl bg-[#10B981] px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-[#064E3B]"
                >
                    Guardar cambios de perfil
                </button>
            </div>
        </form>
    </section>

    {{-- Sección 2: Cambio de Contraseña --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-100 pb-4 dark:border-slate-800">
            <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                Cambio de contraseña
            </h2>
            <p class="text-xs text-[#64748B] dark:text-slate-400">
                Utiliza una contraseña única de al menos 8 caracteres para resguardar tu información financiera.
            </p>
        </div>

        @if ($passwordStatus)
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-[#064E3B] dark:border-emerald-900/60 dark:bg-emerald-950/50 dark:text-emerald-300">
                {{ $passwordStatus }}
            </div>
        @endif

        <form wire:submit="updatePassword" class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="current-password" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                    Contraseña actual
                </label>
                <input
                    id="current-password"
                    type="password"
                    wire:model="current_password"
                    autocomplete="current-password"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                >
                @error('current_password')
                    <p class="mt-1 text-xs text-[#EF4444]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="new-password" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                    Nueva contraseña
                </label>
                <input
                    id="new-password"
                    type="password"
                    wire:model="password"
                    autocomplete="new-password"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                >
                @error('password')
                    <p class="mt-1 text-xs text-[#EF4444]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="confirm-new-password" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                    Confirmar contraseña
                </label>
                <input
                    id="confirm-new-password"
                    type="password"
                    wire:model="password_confirmation"
                    autocomplete="new-password"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                >
            </div>

            <div class="sm:col-span-3 flex justify-end pt-2">
                <button
                    type="submit"
                    class="inline-flex items-center rounded-xl bg-[#0F172A] px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-[#064E3B] dark:bg-slate-800 dark:hover:bg-[#10B981]"
                >
                    Actualizar contraseña
                </button>
            </div>
        </form>
    </section>

    {{-- Sección 3: Autenticación de Dos Factores (2FA TOTP) --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-2 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                    Autenticación de Dos Factores (2FA)
                </h2>
                <p class="text-xs text-[#64748B] dark:text-slate-400">
                    Añade una segunda capa de verificación mediante Google Authenticator, Authy o 1Password.
                </p>
            </div>

            @if ($user->hasConfirmedTwoFactor())
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-[#064E3B] dark:bg-emerald-950/60 dark:text-emerald-300">
                    <span class="h-2 w-2 rounded-full bg-[#10B981]"></span>
                    2FA Activo y Confirmado
                </span>
            @elseif ($user->two_factor_secret !== null)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                    Pendiente de confirmación
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-[#64748B] dark:bg-slate-800 dark:text-slate-400">
                    Inactivo
                </span>
            @endif
        </div>

        @if ($twoFactorStatus)
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-[#064E3B] dark:border-emerald-900/60 dark:bg-emerald-950/50 dark:text-emerald-300">
                {{ $twoFactorStatus }}
            </div>
        @endif

        {{-- Paso de escaneo QR y confirmación cuando se acaba de iniciar activación --}}
        @if ($showingQrCode && $user->two_factor_secret !== null)
            <div class="mt-5 grid grid-cols-1 gap-6 rounded-xl border border-slate-200 bg-slate-50/70 p-5 md:grid-cols-2 dark:border-slate-800 dark:bg-slate-950/50">
                <div class="flex flex-col items-center justify-center rounded-xl bg-white p-4 shadow-2xs">
                    {!! $user->twoFactorQrCodeSvg() !!}
                    <p class="mt-3 text-center font-mono text-xs text-slate-700 break-all">
                        Clave manual: <strong>{{ decrypt($user->two_factor_secret) }}</strong>
                    </p>
                </div>

                <div class="flex flex-col justify-center space-y-4">
                    <p class="text-xs leading-relaxed text-[#64748B] dark:text-slate-300">
                        1. Escanea el código QR con tu aplicación autenticadora.<br>
                        2. Ingresa el código temporal de 6 dígitos que muestra tu aplicación para confirmar la activación.
                    </p>

                    <form wire:submit="confirmTwoFactor" class="space-y-3">
                        <div>
                            <label for="totp-confirm-code" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                                Código de verificación (6 dígitos)
                            </label>
                            <input
                                id="totp-confirm-code"
                                type="text"
                                inputmode="numeric"
                                wire:model="twoFactorCode"
                                placeholder="000000"
                                class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 font-mono text-sm tracking-widest text-[#0F172A] focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            >
                            @error('twoFactorCode')
                                <p class="mt-1 text-xs text-[#EF4444]">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="inline-flex items-center rounded-xl bg-[#10B981] px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-[#064E3B]"
                        >
                            Confirmar código 2FA
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Códigos de recuperación --}}
        @if ($showingRecoveryCodes && $user->two_factor_recovery_codes !== null)
            <div class="mt-5 rounded-xl border border-amber-200/80 bg-amber-50/60 p-5 dark:border-amber-900/50 dark:bg-amber-950/20">
                <p class="text-xs font-bold text-amber-900 dark:text-amber-300">
                    Códigos de recuperación de emergencia
                </p>
                <p class="mt-1 text-xs text-amber-800/90 dark:text-amber-200/80">
                    Guarda estos códigos en un gestor seguro. Cada código puede utilizarse una sola vez si pierdes acceso a tu dispositivo móvil.
                </p>
                <div class="mt-3 grid grid-cols-2 gap-2 rounded-xl bg-white p-4 font-mono text-xs text-[#0F172A] sm:grid-cols-4 dark:bg-slate-950 dark:text-slate-200">
                    @foreach ($user->recoveryCodes() as $code)
                        <div class="rounded bg-slate-50 px-2 py-1 text-center dark:bg-slate-900">{{ $code }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Controles protegidos por confirmación de contraseña actual --}}
        <div class="mt-5 space-y-4">
            <div class="max-w-sm">
                <label for="two-factor-password" class="block text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-300">
                    Confirma tu contraseña actual para gestionar 2FA
                </label>
                <input
                    id="two-factor-password"
                    type="password"
                    wire:model="twoFactorPassword"
                    placeholder="Contraseña actual"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#10B981] focus:ring-2 focus:ring-[#10B981]/20 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                >
                @error('twoFactorPassword')
                    <p class="mt-1 text-xs text-[#EF4444]">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if ($user->two_factor_secret === null)
                    <button
                        type="button"
                        wire:click="enableTwoFactor"
                        class="inline-flex items-center rounded-xl bg-[#10B981] px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-[#064E3B]"
                    >
                        Habilitar autenticación 2FA
                    </button>
                @else
                    @if ($user->hasConfirmedTwoFactor())
                        <button
                            type="button"
                            wire:click="showRecoveryCodes"
                            class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-[#0F172A] transition hover:border-[#10B981] dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            Ver códigos de recuperación
                        </button>

                        <button
                            type="button"
                            wire:click="regenerateRecoveryCodes"
                            class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-[#0F172A] transition hover:border-[#10B981] dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            Regenerar códigos
                        </button>
                    @endif

                    <button
                        type="button"
                        wire:click="disableTwoFactor"
                        class="inline-flex items-center rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-xs font-semibold text-[#EF4444] transition hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
                    >
                        Desactivar 2FA
                    </button>
                @endif
            </div>
        </div>
    </section>
</div>

