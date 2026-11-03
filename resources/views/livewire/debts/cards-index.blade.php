<div class="space-y-6">
    {{-- Notificación flash --}}
    @if (session('status'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-[#064E3B] dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300">
            <svg class="h-4 w-4 shrink-0 text-[#10B981]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-[#10B981]/10 px-3 py-1 text-xs font-semibold text-[#064E3B] dark:bg-[#10B981]/15 dark:text-emerald-300">
                <span>Líneas Rotativas y Cupos</span>
                <span>·</span>
                <span>Prevención de Doble Conteo</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-[#0F172A] dark:text-white">
                Tarjetas de Crédito
            </h1>
            <p class="text-sm text-[#64748B] dark:text-slate-400">
                Gestiona tus líneas de crédito, fechas de corte y pagos sin duplicar gastos contables.
            </p>
        </div>

        <button
            type="button"
            wire:click="openCreateModal"
            class="inline-flex items-center gap-1.5 rounded-xl bg-[#10B981] px-4 py-2.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#064E3B]"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>Nueva Tarjeta</span>
        </button>
    </div>

    {{-- Tarjetas KPI --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">Cupo Total Aprobado</span>
            <p class="mt-2 text-2xl font-bold tracking-tight text-[#0F172A] dark:text-white">
                {{ \App\Support\MoneyFormatter::format($totalCreditLimit, $currencyCode) }}
            </p>
            <span class="mt-1 block text-xs text-[#64748B] dark:text-slate-400">
                Límite de crédito en todas las tarjetas
            </span>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">Saldo Utilizado (Deuda Actual)</span>
            <p class="mt-2 text-2xl font-bold tracking-tight text-[#EF4444] dark:text-red-400">
                {{ \App\Support\MoneyFormatter::format($totalUsedBalance, $currencyCode) }}
            </p>
            <span class="mt-1 block text-xs text-[#64748B] dark:text-slate-400">
                Pasivo contable acumulado
            </span>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">Cupo Disponible Global</span>
            <p class="mt-2 text-2xl font-bold tracking-tight text-[#10B981]">
                {{ \App\Support\MoneyFormatter::format($totalAvailableCredit, $currencyCode) }}
            </p>
            <span class="mt-1 block text-xs text-[#64748B] dark:text-slate-400">
                Margen de crédito libre
            </span>
        </div>
    </div>

    {{-- Grid de Tarjetas (Visualización Premium Fintech) --}}
    @if (count($cardsData) > 0)
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($cardsData as $item)
                @php
                    /** @var \App\Models\Debt $card */
                    $card = $item['card'];
                    $used = $item['used_balance'];
                    $available = $item['available_credit'];
                    $limit = $item['limit'];
                    $usagePct = $item['usage_percent'];
                @endphp
                <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-linear-to-br from-slate-900 via-slate-800 to-slate-950 p-6 text-white shadow-lg dark:border-slate-800">
                    {{-- Marca de agua decorativa --}}
                    <div class="pointer-events-none absolute -right-6 -bottom-6 h-36 w-36 rounded-full bg-emerald-500/10 blur-2xl"></div>

                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-400">
                                {{ $card->creditor_name ?: 'Ahorvia Card' }}
                            </span>
                            <h3 class="mt-1 text-lg font-bold tracking-tight text-white">
                                {{ $card->name }}
                            </h3>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 backdrop-blur-md">
                            <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                            </svg>
                        </div>
                    </div>

                    <div class="mt-6 flex items-baseline justify-between">
                        <div>
                            <span class="text-[11px] text-slate-400">Saldo por pagar</span>
                            <p class="text-xl font-bold text-red-400">
                                {{ \App\Support\MoneyFormatter::format($used, $card->currency_code) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-slate-400">Cupo disponible</span>
                            <p class="text-base font-semibold text-emerald-300">
                                {{ \App\Support\MoneyFormatter::format($available, $card->currency_code) }}
                            </p>
                        </div>
                    </div>

                    {{-- Barra de utilización del cupo --}}
                    <div class="mt-4">
                        <div class="flex justify-between text-[10px] text-slate-400">
                            <span>Uso: {{ $usagePct }}%</span>
                            <span>Límite: {{ \App\Support\MoneyFormatter::format($limit, $card->currency_code) }}</span>
                        </div>
                        <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-white/20">
                            <div
                                class="h-full rounded-full transition-all duration-300 {{ $usagePct >= 90 ? 'bg-red-500' : ($usagePct >= 70 ? 'bg-amber-400' : 'bg-emerald-400') }}"
                                style="width: {{ min(100, $usagePct) }}%"
                            ></div>
                        </div>
                    </div>

                    {{-- Fechas de corte y pago --}}
                    <div class="mt-4 flex items-center justify-between border-t border-white/10 pt-3 text-[11px] text-slate-300">
                        <div>
                            <span>Corte: </span>
                            <span class="font-semibold text-white">Día {{ $card->cutoff_day ?: '—' }}</span>
                        </div>
                        <div>
                            <span>Límite pago: </span>
                            <span class="font-semibold text-white">Día {{ $card->payment_due_day ?: '—' }}</span>
                        </div>
                    </div>

                    {{-- Botón Pagar Tarjeta --}}
                    <div class="mt-4">
                        <button
                            type="button"
                            wire:click="openPayModal({{ $card->id }})"
                            class="w-full rounded-xl bg-white/15 py-2 text-center text-xs font-semibold text-white backdrop-blur-md transition hover:bg-emerald-500 hover:text-white"
                        >
                            Pagar Tarjeta
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-800 dark:bg-slate-900">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-[#64748B] dark:bg-slate-800 dark:text-slate-400">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-bold text-[#0F172A] dark:text-white">
                No tienes tarjetas de crédito registradas
            </h3>
            <p class="mx-auto mt-2 max-w-md text-xs leading-relaxed text-[#64748B] dark:text-slate-400">
                Añade tus tarjetas para monitorear tu cupo disponible, fechas de corte y registrar pagos sin distorsionar tus reportes de gasto.
            </p>
            <div class="mt-6">
                <button
                    type="button"
                    wire:click="openCreateModal"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#10B981] px-4 py-2.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#064E3B]"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Registrar Mi Primera Tarjeta</span>
                </button>
            </div>
        </section>
    @endif

    {{-- Modal: Crear Tarjeta --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-[#0F172A] dark:text-white">Nueva Tarjeta de Crédito</h3>
                        <p class="text-xs text-[#64748B] dark:text-slate-400">Se creará su cuenta contable de pasivo asociada</p>
                    </div>
                    <button type="button" wire:click="closeCreateModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveCard" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Nombre de la tarjeta *</label>
                        <input type="text" wire:model="name" placeholder="Ej. Visa Signature, Mastercard Oro..." class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                        @error('name') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Banco emisor</label>
                            <input type="text" wire:model="bankName" placeholder="Ej. BBVA, Santander, Chase..." class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            @error('bankName') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Cupo total aprobado *</label>
                            <div class="relative mt-1">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs text-slate-400">$</span>
                                <input type="text" wire:model="creditLimit" placeholder="3000.00" class="w-full rounded-xl border border-slate-200 pl-7 pr-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            </div>
                            @error('creditLimit') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Saldo actual adeudado (Opcional)</label>
                            <div class="relative mt-1">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs text-slate-400">$</span>
                                <input type="text" wire:model="openingBalance" placeholder="0.00" class="w-full rounded-xl border border-slate-200 pl-7 pr-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            </div>
                            @error('openingBalance') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Tasa anual (%)</label>
                            <div class="relative mt-1">
                                <input type="text" wire:model="interestRateAnnual" placeholder="28.5" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-slate-400">%</span>
                            </div>
                            @error('interestRateAnnual') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Día de corte mensual (1-31)</label>
                            <input type="number" min="1" max="31" wire:model="cutoffDay" placeholder="10" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            @error('cutoffDay') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Día límite de pago mensual (1-31)</label>
                            <input type="number" min="1" max="31" wire:model="paymentDueDay" placeholder="25" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            @error('paymentDueDay') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="closeCreateModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-[#64748B] hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-xl bg-[#10B981] px-5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-[#064E3B]">
                            Guardar Tarjeta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal: Pagar Tarjeta --}}
    @if ($showPayModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-[#0F172A] dark:text-white">
                            Pagar Tarjeta — {{ $selectedCard?->name }}
                        </h3>
                        <p class="text-xs text-[#64748B] dark:text-slate-400">
                            Transfiere fondos desde una cuenta de activo para reducir el pasivo sin duplicar gastos
                        </p>
                    </div>
                    <button type="button" wire:click="closePayModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="savePayment" class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Pagar desde cuenta *</label>
                            <select wire:model="sourceAccountId" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                <option value="">Selecciona cuenta...</option>
                                @foreach ($liquidAccounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                @endforeach
                            </select>
                            @error('sourceAccountId') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Fecha del pago *</label>
                            <input type="date" wire:model="paymentDate" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            @error('paymentDate') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2.5">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Abono Capital *</label>
                            <input type="text" wire:model="principalAmount" placeholder="0.00" class="mt-1 w-full rounded-xl border border-slate-200 px-2.5 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white font-medium" />
                            <span class="text-[10px] text-slate-400">Reduce deuda</span>
                            @error('principalAmount') <span class="block text-[10px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Interés financiado</label>
                            <input type="text" wire:model="interestAmount" placeholder="0.00" class="mt-1 w-full rounded-xl border border-slate-200 px-2.5 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white font-medium" />
                            <span class="text-[10px] text-slate-400">Gasto financiero</span>
                            @error('interestAmount') <span class="block text-[10px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Comisiones/Manejo</label>
                            <input type="text" wire:model="feeAmount" placeholder="0.00" class="mt-1 w-full rounded-xl border border-slate-200 px-2.5 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white font-medium" />
                            <span class="text-[10px] text-slate-400">Gasto bancario</span>
                            @error('feeAmount') <span class="block text-[10px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Referencia / Comprobante</label>
                        <input type="text" wire:model="reference" placeholder="Ej. PAGO-TARJETA-MAY" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="closePayModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-[#64748B] hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-xl bg-[#10B981] px-5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-[#064E3B]">
                            Confirmar Pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

