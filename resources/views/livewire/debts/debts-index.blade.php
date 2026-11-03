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
                <span>Gestión de Pasivos y Amortización</span>
                <span>·</span>
                <span>Separación Capital / Interés</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-[#0F172A] dark:text-white">
                Deudas y Préstamos
            </h1>
            <p class="text-sm text-[#64748B] dark:text-slate-400">
                Control contable de préstamos personales, bancarios e hipotecarios con amortización auditable.
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
            <span>Nueva Deuda / Préstamo</span>
        </button>
    </div>

    {{-- Tarjetas KPI --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">Total Pasivo Pendiente</span>
            <p class="mt-2 text-2xl font-bold tracking-tight text-[#EF4444] dark:text-red-400">
                {{ \App\Support\MoneyFormatter::format($totalPendingPrincipal, $currencyCode) }}
            </p>
            <span class="mt-1 block text-xs text-[#64748B] dark:text-slate-400">
                Capital total adeudado
            </span>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">Total Amortizado a Capital</span>
            <p class="mt-2 text-2xl font-bold tracking-tight text-[#10B981]">
                {{ \App\Support\MoneyFormatter::format($totalPrincipalPaid, $currencyCode) }}
            </p>
            <span class="mt-1 block text-xs text-[#64748B] dark:text-slate-400">
                Reducción acumulada de deuda
            </span>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">Intereses y Comisiones Pagados</span>
            <p class="mt-2 text-2xl font-bold tracking-tight text-[#0F172A] dark:text-white">
                {{ \App\Support\MoneyFormatter::format($totalInterestFeesPaid, $currencyCode) }}
            </p>
            <span class="mt-1 block text-xs text-[#64748B] dark:text-slate-400">
                Costo financiero reconocido
            </span>
        </div>
    </div>

    {{-- Lista de Deudas --}}
    @if (count($debtsData) > 0)
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            @foreach ($debtsData as $item)
                @php
                    /** @var \App\Models\Debt $debt */
                    $debt = $item['debt'];
                    $pending = $item['pending_principal'];
                    $breakdown = $item['breakdown'];
                    $isClosed = \App\Support\DecimalMath::equals($pending, '0');
                @endphp
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs transition hover:border-[#10B981]/50 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $isClosed ? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' : 'bg-red-50 text-red-700 dark:bg-red-950/60 dark:text-red-400' }}">
                                    {{ $debt->type->label() }}
                                </span>
                                @if ($isClosed)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        Liquidada
                                    </span>
                                @endif
                            </div>
                            <h3 class="mt-2 text-base font-bold text-[#0F172A] dark:text-white">
                                {{ $debt->name }}
                            </h3>
                            @if ($debt->creditor_name)
                                <p class="text-xs text-[#64748B] dark:text-slate-400">
                                    Acreedor: <span class="font-medium text-[#0F172A] dark:text-slate-200">{{ $debt->creditor_name }}</span>
                                </p>
                            @endif
                        </div>

                        <div class="text-right">
                            <span class="text-xs text-[#64748B] dark:text-slate-400">Saldo pendiente</span>
                            <p class="text-xl font-bold {{ $isClosed ? 'text-slate-400' : 'text-[#EF4444] dark:text-red-400' }}">
                                {{ \App\Support\MoneyFormatter::format($pending, $debt->currency_code) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-3 text-xs dark:border-slate-800">
                        <div>
                            <span class="text-[#64748B] dark:text-slate-400">Monto Original:</span>
                            <p class="font-semibold text-[#0F172A] dark:text-white">
                                {{ \App\Support\MoneyFormatter::format((string) $debt->original_principal, $debt->currency_code) }}
                            </p>
                        </div>
                        <div>
                            <span class="text-[#64748B] dark:text-slate-400">Tasa Interés Anual:</span>
                            <p class="font-semibold text-[#0F172A] dark:text-white">
                                {{ $debt->interest_rate_annual ? number_format((float) $debt->interest_rate_annual, 2).'%' : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-[#64748B] dark:text-slate-400">Capital Amortizado:</span>
                            <p class="font-semibold text-[#10B981]">
                                {{ \App\Support\MoneyFormatter::format($breakdown['principal_paid'], $debt->currency_code) }}
                            </p>
                        </div>
                        <div>
                            <span class="text-[#64748B] dark:text-slate-400">Intereses Pagados:</span>
                            <p class="font-semibold text-[#0F172A] dark:text-white">
                                {{ \App\Support\MoneyFormatter::format($breakdown['interest_paid'], $debt->currency_code) }}
                            </p>
                        </div>
                        @if ($debt->due_date)
                            <div>
                                <span class="text-[#64748B] dark:text-slate-400">Fecha Vencimiento:</span>
                                <p class="font-semibold text-[#0F172A] dark:text-white">
                                    {{ \Carbon\CarbonImmutable::parse($debt->due_date)->format('d/m/Y') }}
                                </p>
                            </div>
                        @endif
                        @if ($debt->payment_due_day)
                            <div>
                                <span class="text-[#64748B] dark:text-slate-400">Día de Pago:</span>
                                <p class="font-semibold text-[#0F172A] dark:text-white">
                                    Día {{ $debt->payment_due_day }} de cada mes
                                </p>
                            </div>
                        @endif
                    </div>

                    @if (! $isClosed)
                        <div class="mt-4 flex items-center justify-end border-t border-slate-100 pt-3 dark:border-slate-800">
                            <button
                                type="button"
                                wire:click="openPaymentModal({{ $debt->id }})"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-[#0F172A] px-3.5 py-1.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#064E3B] dark:bg-slate-800 dark:hover:bg-[#10B981]"
                            >
                                <svg class="h-4 w-4 text-[#10B981]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v1.5M16.5 6h2.25a.75.75 0 0 0 .75-.75V4.5m0 0H16.5m0 0a3 3 0 0 0-3-3H10.5a3 3 0 0 0-3 3M16.5 6v12a3 3 0 0 1-3 3H10.5a3 3 0 0 1-3-3V6" />
                                </svg>
                                <span>Registrar Abono</span>
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-800 dark:bg-slate-900">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-[#64748B] dark:bg-slate-800 dark:text-slate-400">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-bold text-[#0F172A] dark:text-white">
                No tienes préstamos o deudas registradas
            </h3>
            <p class="mx-auto mt-2 max-w-md text-xs leading-relaxed text-[#64748B] dark:text-slate-400">
                Lleva un control milimétrico de lo que debes, las fechas de vencimiento y el desglose de capital frente a intereses.
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
                    <span>Registrar Mi Primera Deuda</span>
                </button>
            </div>
        </section>
    @endif

    {{-- Modal: Crear Deuda --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-[#0F172A] dark:text-white">Nueva Deuda o Préstamo</h3>
                        <p class="text-xs text-[#64748B] dark:text-slate-400">Se creará su cuenta contable de pasivo asociada</p>
                    </div>
                    <button type="button" wire:click="closeCreateModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveDebt" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Nombre de la deuda *</label>
                        <input type="text" wire:model="name" placeholder="Ej. Préstamo Auto, Deuda Personal Juan..." class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                        @error('name') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Tipo de deuda *</label>
                            <select wire:model="debtType" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                <option value="personal_loan">Préstamo personal</option>
                                <option value="bank_loan">Préstamo bancario</option>
                                <option value="mortgage">Crédito hipotecario</option>
                                <option value="informal_debt">Deuda informal / amigos</option>
                            </select>
                            @error('debtType') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Acreedor / Banco</label>
                            <input type="text" wire:model="creditorName" placeholder="Ej. Banco Santander..." class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            @error('creditorName') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Monto principal *</label>
                            <div class="relative mt-1">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs text-slate-400">$</span>
                                <input type="text" wire:model="originalPrincipal" placeholder="5000.00" class="w-full rounded-xl border border-slate-200 pl-7 pr-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            </div>
                            @error('originalPrincipal') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Tasa anual (%)</label>
                            <div class="relative mt-1">
                                <input type="text" wire:model="interestRateAnnual" placeholder="12.5" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-slate-400">%</span>
                            </div>
                            @error('interestRateAnnual') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Fecha de vencimiento</label>
                            <input type="date" wire:model="dueDate" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            @error('dueDate') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Día de pago mensual (1-31)</label>
                            <input type="number" min="1" max="31" wire:model="paymentDueDay" placeholder="15" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            @error('paymentDueDay') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">
                            ¿Dónde se desembolsó el dinero? (Opcional)
                        </label>
                        <select wire:model="disbursementAccountId" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                            <option value="">Ninguna (Apertura de saldo histórico)</option>
                            @foreach ($liquidAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} (Aumentará saldo de activo)</option>
                            @endforeach
                        </select>
                        <span class="mt-1 block text-[11px] text-[#64748B] dark:text-slate-400">
                            Si seleccionas una cuenta bancaria, se asentará el ingreso contable sin duplicar ganancias.
                        </span>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="closeCreateModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-[#64748B] hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-xl bg-[#10B981] px-5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-[#064E3B]">
                            Registrar Deuda
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal: Registrar Abono a Deuda --}}
    @if ($showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-[#0F172A] dark:text-white">
                            Registrar Abono — {{ $selectedDebt?->name }}
                        </h3>
                        <p class="text-xs text-[#64748B] dark:text-slate-400">
                            Desglosa el pago para que el capital reduzca la deuda y los intereses se computen como gasto
                        </p>
                    </div>
                    <button type="button" wire:click="closePaymentModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="savePayment" class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Cuenta de origen *</label>
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
                            <span class="text-[10px] text-slate-400">Reduce pasivo</span>
                            @error('principalAmount') <span class="block text-[10px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Interés</label>
                            <input type="text" wire:model="interestAmount" placeholder="0.00" class="mt-1 w-full rounded-xl border border-slate-200 px-2.5 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white font-medium" />
                            <span class="text-[10px] text-slate-400">Gasto financiero</span>
                            @error('interestAmount') <span class="block text-[10px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Comisiones</label>
                            <input type="text" wire:model="feeAmount" placeholder="0.00" class="mt-1 w-full rounded-xl border border-slate-200 px-2.5 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white font-medium" />
                            <span class="text-[10px] text-slate-400">Gasto comisiones</span>
                            @error('feeAmount') <span class="block text-[10px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Referencia / Núm. comprobante</label>
                        <input type="text" wire:model="reference" placeholder="Ej. TRANSF-90823" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="closePaymentModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-[#64748B] hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-xl bg-[#10B981] px-5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-[#064E3B]">
                            Confirmar Abono
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

