<div class="space-y-6">
    {{-- Notificación de estado --}}
    @if (session('status'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-[#064E3B] dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300">
            <svg class="h-4 w-4 shrink-0 text-[#10B981]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-semibold text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
            <svg class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Encabezado del Módulo --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-[#10B981]/10 px-3 py-1 text-xs font-semibold text-[#064E3B] dark:bg-[#10B981]/15 dark:text-emerald-300">
                <span>Gestión de Cuentas y Liquidez</span>
                <span>·</span>
                <span>Moneda: {{ $currency }}</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-[#0F172A] dark:text-white">
                Cuentas Financieras
            </h1>
            <p class="text-sm text-[#64748B] dark:text-slate-400">
                Saldos contables por partida doble, liquidez libre y cupos de crédito.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button
                type="button"
                wire:click="openTransferModal"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-[#0F172A] shadow-2xs transition hover:border-[#10B981] dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
            >
                <svg class="h-4 w-4 text-[#10B981]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
                <span>Transferir entre cuentas</span>
            </button>

            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center gap-2 rounded-xl bg-[#064E3B] px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-[#10B981]"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Nueva cuenta</span>
            </button>
        </div>
    </div>

    {{-- Resumen de Métricas de Cuentas --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-400">
                Liquidez disponible
            </p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-[#10B981]">
                {{ $availableLiquidityFormatted }}
            </p>
            <p class="mt-1 text-xs text-[#64748B] dark:text-slate-400">
                Efectivo y cuentas bancarias libres de reservas
            </p>
        </article>

        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-400">
                Total de activos
            </p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-[#0F172A] dark:text-white">
                {{ $totalAssetsFormatted }}
            </p>
            <p class="mt-1 text-xs text-[#64748B] dark:text-slate-400">
                Todas las cuentas y reservas acumuladas
            </p>
        </article>

        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-400">
                Total de pasivos
            </p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-[#EF4444]">
                {{ $totalLiabilitiesFormatted }}
            </p>
            <p class="mt-1 text-xs text-[#64748B] dark:text-slate-400">
                Saldos en tarjetas de crédito y préstamos
            </p>
        </article>

        <article class="rounded-2xl border border-[#10B981]/30 bg-gradient-to-br from-[#0F172A] to-[#064E3B] p-5 text-white shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-200">
                Patrimonio neto
            </p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-white">
                {{ $netWorthFormatted }}
            </p>
            <p class="mt-1 text-xs text-emerald-100/80">
                Activos menos pasivos consolidados
            </p>
        </article>
    </section>

    {{-- Listado de Cuentas --}}
    @if (count($accountsData) === 0)
        <section class="rounded-2xl border border-dashed border-slate-200 bg-white p-12 text-center dark:border-slate-800 dark:bg-slate-900">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-[#10B981] dark:bg-emerald-950/40">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                </svg>
            </div>
            <h2 class="mt-4 text-base font-bold text-[#0F172A] dark:text-white">
                No tienes cuentas registradas todavía
            </h2>
            <p class="mx-auto mt-1 max-w-sm text-xs text-[#64748B] dark:text-slate-400">
                Registra tu cuenta de efectivo, cuenta de ahorros o tarjeta para comenzar a llevar tu contabilidad en partida doble.
            </p>
            <button
                type="button"
                wire:click="openCreateModal"
                class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#10B981] px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-[#064E3B]"
            >
                Crear mi primera cuenta
            </button>
        </section>
    @else
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($accountsData as $item)
                @php
                    $acc = $item['model'];
                @endphp
                <article class="flex flex-col justify-between rounded-2xl border {{ $acc->is_active ? 'border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900' : 'border-slate-200/50 bg-slate-50/60 opacity-60 dark:border-slate-800/50 dark:bg-slate-950/40' }} p-5 shadow-2xs">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-[#064E3B] dark:bg-slate-800 dark:text-emerald-300">
                                        {{ $acc->type->label() }}
                                    </span>
                                    @if (! $acc->is_active)
                                        <span class="rounded-md bg-slate-200 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                            Inactiva
                                        </span>
                                    @endif
                                </div>
                                <h3 class="mt-2 text-base font-bold text-[#0F172A] dark:text-white">
                                    {{ $acc->name }}
                                </h3>
                                @if ($acc->institution_name)
                                    <p class="text-xs text-[#64748B] dark:text-slate-400">
                                        {{ $acc->institution_name }} {{ $acc->account_number_last4 ? "· **** {$acc->account_number_last4}" : '' }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    wire:click="openTransferModal({{ $acc->id }})"
                                    class="rounded-lg p-1.5 text-[#64748B] transition hover:bg-slate-100 hover:text-[#10B981] dark:text-slate-400 dark:hover:bg-slate-800"
                                    title="Transferir desde esta cuenta"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    wire:click="openEditModal({{ $acc->id }})"
                                    class="rounded-lg p-1.5 text-[#64748B] transition hover:bg-slate-100 hover:text-[#0F172A] dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white"
                                    title="Editar cuenta"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    wire:click="deleteAccount({{ $acc->id }})"
                                    wire:confirm="¿Estás seguro de que deseas eliminar esta cuenta creada por error? Si solo tiene asiento de apertura, se revertirá contablemente."
                                    class="rounded-lg p-1.5 text-[#64748B] transition hover:bg-red-50 hover:text-red-600 dark:text-slate-400 dark:hover:bg-red-950/40 dark:hover:text-red-400"
                                    title="Eliminar cuenta creada por error"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Saldos --}}
                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80">
                            <p class="text-xs text-[#64748B] dark:text-slate-400">
                                Saldo contable
                            </p>
                            <p class="text-xl font-bold tabular-nums {{ $acc->type->isAsset() ? 'text-[#0F172A] dark:text-white' : 'text-[#EF4444]' }}">
                                {{ $item['formatted_balance'] }}
                            </p>

                            @if ($acc->type->isAsset())
                                <div class="mt-2 flex items-center justify-between text-xs text-[#64748B] dark:text-slate-400">
                                    <span>Disponible libre:</span>
                                    <span class="font-semibold text-[#10B981]">{{ $item['formatted_available'] }}</span>
                                </div>
                                @if (bccomp($item['reserved'], '0.0000', 4) > 0)
                                    <div class="mt-1 flex items-center justify-between text-[11px] text-[#64748B] dark:text-slate-400">
                                        <span>Reservado en metas:</span>
                                        <span class="font-medium text-amber-600 dark:text-amber-400">{{ $item['formatted_reserved'] }}</span>
                                    </div>
                                @endif
                            @elseif ($acc->credit_limit !== null)
                                <div class="mt-2 flex items-center justify-between text-xs text-[#64748B] dark:text-slate-400">
                                    <span>Cupo disponible:</span>
                                    <span class="font-semibold text-[#10B981]">{{ $item['formatted_available'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Footer de tarjeta --}}
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-[#64748B] dark:border-slate-800/80 dark:text-slate-400">
                        <span>{{ $acc->include_in_net_worth ? 'Incluida en patrimonio' : 'Excluida de patrimonio' }}</span>
                        <button
                            type="button"
                            wire:click="toggleStatus({{ $acc->id }})"
                            class="hover:underline {{ $acc->is_active ? 'text-red-500' : 'text-[#10B981]' }}"
                        >
                            {{ $acc->is_active ? 'Desactivar' : 'Activar' }}
                        </button>
                    </div>
                </article>
            @endforeach
        </section>
    @endif

    {{-- Modal: Crear Cuenta --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <h3 class="text-base font-bold text-[#0F172A] dark:text-white">
                        Nueva Cuenta Financiera
                    </h3>
                    <button type="button" wire:click="closeCreateModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveAccount" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Nombre de la cuenta *</label>
                        <input type="text" wire:model="name" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" placeholder="Ej. BCP Sueldo, Billetera Efectivo" />
                        @error('name') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Tipo de cuenta *</label>
                            <select wire:model="type" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                @foreach ($userCaseTypes as $caseType)
                                    <option value="{{ $caseType->value }}">{{ $caseType->label() }}</option>
                                @endforeach
                            </select>
                            @error('type') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Saldo inicial</label>
                            <input type="text" wire:model="opening_balance" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" placeholder="0.00" />
                            @error('opening_balance') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Institución bancaria</label>
                            <input type="text" list="institutions-list" wire:model="institution_name" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" placeholder="Ej. Banco Guayaquil, Pichincha, Chase..." />
                            @error('institution_name') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Últimos 4 dígitos</label>
                            <input type="text" wire:model="account_number_last4" maxlength="4" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" placeholder="1234" />
                            @error('account_number_last4') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if ($type === 'credit_card')
                        <div class="grid grid-cols-3 gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                            <div>
                                <label class="block text-[11px] font-semibold text-[#0F172A] dark:text-slate-200">Límite de crédito</label>
                                <input type="text" wire:model="credit_limit" class="mt-1 w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-900 dark:text-white" placeholder="3000.00" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-[#0F172A] dark:text-slate-200">Día de corte</label>
                                <input type="number" min="1" max="31" wire:model="billing_cutoff_day" class="mt-1 w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-900 dark:text-white" placeholder="15" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-[#0F172A] dark:text-slate-200">Día de pago</label>
                                <input type="number" min="1" max="31" wire:model="payment_due_day" class="mt-1 w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-900 dark:text-white" placeholder="5" />
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="include_in_net_worth" wire:model="include_in_net_worth" class="rounded text-[#10B981] focus:ring-[#10B981]" />
                        <label for="include_in_net_worth" class="text-xs text-[#64748B] dark:text-slate-400">
                            Incluir esta cuenta en el cálculo del patrimonio neto
                        </label>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="closeCreateModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-[#64748B] hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-xl bg-[#10B981] px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-[#064E3B]">
                            Guardar cuenta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal: Editar Cuenta --}}
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <h3 class="text-base font-bold text-[#0F172A] dark:text-white">
                        Editar Cuenta Financiera
                    </h3>
                    <button type="button" wire:click="closeEditModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="updateAccount" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Nombre de la cuenta *</label>
                        <input type="text" wire:model="editName" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                        @error('editName') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Institución bancaria</label>
                            <input type="text" list="institutions-list" wire:model="editInstitutionName" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Últimos 4 dígitos</label>
                            <input type="text" wire:model="editAccountNumberLast4" maxlength="4" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                        </div>
                    </div>

                    @if ($editCreditLimit !== null)
                        <div class="grid grid-cols-3 gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                            <div>
                                <label class="block text-[11px] font-semibold text-[#0F172A] dark:text-slate-200">Límite de crédito</label>
                                <input type="text" wire:model="editCreditLimit" class="mt-1 w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-900 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-[#0F172A] dark:text-slate-200">Día de corte</label>
                                <input type="number" min="1" max="31" wire:model="editBillingCutoffDay" class="mt-1 w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-900 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-[#0F172A] dark:text-slate-200">Día de pago</label>
                                <input type="number" min="1" max="31" wire:model="editPaymentDueDay" class="mt-1 w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-900 dark:text-white" />
                            </div>
                        </div>
                    @endif

                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="editIncludeInNetWorth" wire:model="editIncludeInNetWorth" class="rounded text-[#10B981] focus:ring-[#10B981]" />
                            <label for="editIncludeInNetWorth" class="text-xs text-[#64748B] dark:text-slate-400">Incluir en patrimonio neto</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="editIsActive" wire:model="editIsActive" class="rounded text-[#10B981] focus:ring-[#10B981]" />
                            <label for="editIsActive" class="text-xs text-[#64748B] dark:text-slate-400">Cuenta activa para operaciones</label>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        @if ($editingAccountId)
                            <button
                                type="button"
                                wire:click="deleteAccount({{ $editingAccountId }})"
                                wire:confirm="¿Estás seguro de que deseas eliminar esta cuenta creada por error? Si solo tiene asiento de apertura, se revertirá contablemente."
                                class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-800 dark:text-red-400"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                                <span>Eliminar cuenta</span>
                            </button>
                        @else
                            <div></div>
                        @endif

                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="closeEditModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-[#64748B] hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button type="submit" class="rounded-xl bg-[#064E3B] px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-[#10B981]">
                                Actualizar cuenta
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal: Transferencia Rápida --}}
    @if ($showTransferModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <h3 class="text-base font-bold text-[#0F172A] dark:text-white">
                        Transferencia entre Cuentas
                    </h3>
                    <button type="button" wire:click="closeTransferModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="executeTransfer" class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Cuenta de origen *</label>
                            <select wire:model="transferSourceId" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                <option value="">Selecciona origen</option>
                                @foreach ($activeTransferAccounts as $srcAcc)
                                    <option value="{{ $srcAcc->id }}">{{ $srcAcc->name }}</option>
                                @endforeach
                            </select>
                            @error('transferSourceId') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Cuenta de destino *</label>
                            <select wire:model="transferDestinationId" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                <option value="">Selecciona destino</option>
                                @foreach ($activeTransferAccounts as $destAcc)
                                    <option value="{{ $destAcc->id }}">{{ $destAcc->name }}</option>
                                @endforeach
                            </select>
                            @error('transferDestinationId') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Importe a transferir ({{ $currency }}) *</label>
                        <input type="text" wire:model="transferAmount" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" placeholder="0.00" />
                        @error('transferAmount') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Descripción *</label>
                        <input type="text" wire:model="transferDescription" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                        @error('transferDescription') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="closeTransferModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-[#64748B] hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-xl bg-[#10B981] px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-[#064E3B]">
                            Confirmar transferencia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <datalist id="institutions-list">
        @foreach ($institutions as $inst)
            <option value="{{ $inst }}"></option>
        @endforeach
    </datalist>
</div>

