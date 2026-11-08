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

    @if (session('error'))
        <div class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-semibold text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
            <svg class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-[#10B981]/10 px-3 py-1 text-xs font-semibold text-[#064E3B] dark:bg-[#10B981]/15 dark:text-emerald-300">
                <span>Algoritmo de Metas a 6 Meses</span>
                <span>·</span>
                <span>Recálculo Dinámico Sin Alterar Historial</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-[#0F172A] dark:text-white">
                Planificador Inteligente de Ahorro a 6 Meses
            </h1>
            <p class="text-sm text-[#64748B] dark:text-slate-400">
                Calcula tu capacidad real de ahorro, proyecta cuotas mensuales sostenibles y ajusta objetivos automáticamente ante imprevistos.
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
            <span>Nueva Meta de Ahorro</span>
        </button>
    </div>

    {{-- Panel de Inteligencia Financiera: Capacidad Real de Ahorro Mensual --}}
    <section class="rounded-2xl border border-emerald-200/80 bg-linear-to-br from-emerald-500/5 via-white to-emerald-500/10 p-6 shadow-2xs dark:border-emerald-950/60 dark:from-slate-900 dark:via-slate-900 dark:to-emerald-950/30">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-emerald-100 pb-4 dark:border-emerald-900/40">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-[#10B981]">
                    Evaluación de Capacidad a 6 Meses
                </span>
                <h2 class="text-lg font-bold text-[#0F172A] dark:text-white">
                    Capacidad Real de Ahorro del Mes
                </h2>
            </div>
            <div>
                @if ($capacity->isSustainable)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300">
                        <span class="h-2 w-2 rounded-full bg-[#10B981]"></span>
                        Plan Financiero Sostenible
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/80 dark:text-amber-300">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        Capacidad en Tensión / Sobrecomprometida
                    </span>
                @endif
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 text-xs">
            <div class="rounded-xl bg-white/80 p-3 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800">
                <span class="text-[#64748B] dark:text-slate-400">Ingreso previsto:</span>
                <p class="mt-1 text-sm font-bold text-[#0F172A] dark:text-white">
                    {{ \App\Support\MoneyFormatter::format($capacity->expectedMonthlyIncome, $capacity->currencyCode) }}
                </p>
            </div>

            <div class="rounded-xl bg-white/80 p-3 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800">
                <span class="text-[#64748B] dark:text-slate-400">Gastos presupuestados:</span>
                <p class="mt-1 text-sm font-bold text-[#EF4444] dark:text-red-400">
                    {{ \App\Support\MoneyFormatter::format($capacity->budgetedExpenses, $capacity->currencyCode) }}
                </p>
            </div>

            <div class="rounded-xl bg-white/80 p-3 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800">
                <span class="text-[#64748B] dark:text-slate-400">Obligaciones externas:</span>
                <p class="mt-1 text-sm font-bold text-[#EF4444] dark:text-red-400">
                    {{ \App\Support\MoneyFormatter::format($capacity->unbudgetedObligations, $capacity->currencyCode) }}
                </p>
            </div>

            <div class="rounded-xl bg-white/80 p-3 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800">
                <span class="text-[#64748B] dark:text-slate-400">Capacidad bruta:</span>
                <p class="mt-1 text-sm font-bold text-[#0F172A] dark:text-white">
                    {{ \App\Support\MoneyFormatter::format($capacity->grossSavingsCapacity, $capacity->currencyCode) }}
                </p>
            </div>

            <div class="rounded-xl bg-white/80 p-3 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800">
                <span class="text-[#64748B] dark:text-slate-400">Demanda de metas:</span>
                <p class="mt-1 text-sm font-bold text-[#10B981]">
                    {{ \App\Support\MoneyFormatter::format($capacity->activeGoalsMonthlyDemand, $capacity->currencyCode) }}
                </p>
            </div>

            <div class="rounded-xl bg-white/80 p-3 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800">
                <span class="text-[#64748B] dark:text-slate-400">Margen remanente:</span>
                <p class="mt-1 text-sm font-bold {{ \App\Support\DecimalMath::compare($capacity->netUnallocatedCapacity, '0') >= 0 ? 'text-[#10B981]' : 'text-[#EF4444]' }}">
                    {{ \App\Support\MoneyFormatter::format($capacity->netUnallocatedCapacity, $capacity->currencyCode) }}
                </p>
            </div>
        </div>
    </section>

    {{-- Tarjetas KPI de Metas --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">Total Objetivo Acumulado</span>
            <p class="mt-2 text-2xl font-bold tracking-tight text-[#0F172A] dark:text-white">
                {{ \App\Support\MoneyFormatter::format($totalTarget, $currencyCode) }}
            </p>
            <span class="mt-1 block text-xs text-[#64748B] dark:text-slate-400">
                Suma de objetivos en metas activas
            </span>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">Ahorro Reconocido Contable</span>
            <p class="mt-2 text-2xl font-bold tracking-tight text-[#10B981]">
                {{ \App\Support\MoneyFormatter::format($totalRecognized, $currencyCode) }}
            </p>
            <span class="mt-1 block text-xs text-[#64748B] dark:text-slate-400">
                Fondos reales aportados o reservados
            </span>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">Ahorro Pendiente Global</span>
            <p class="mt-2 text-2xl font-bold tracking-tight text-[#EF4444] dark:text-red-400">
                {{ \App\Support\MoneyFormatter::format($totalPending, $currencyCode) }}
            </p>
            <span class="mt-1 block text-xs text-[#64748B] dark:text-slate-400">
                Brecha restante para alcanzar la meta
            </span>
        </div>
    </div>

    {{-- Listado de Metas de Ahorro --}}
    @if (count($evaluatedGoals) > 0)
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            @foreach ($evaluatedGoals as $plan)
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs transition hover:border-[#10B981]/50 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $plan->mode->label() }}
                                </span>
                                @if ($plan->isCompleted)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        Meta Cumplida
                                    </span>
                                @elseif ($plan->requiresAdjustment)
                                    <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                        Cuota recalculada (Ajuste requerido)
                                    </span>
                                @endif
                            </div>
                            <h3 class="mt-2 text-base font-bold text-[#0F172A] dark:text-white">
                                {{ $plan->goalName }}
                            </h3>
                            <p class="text-xs text-[#64748B] dark:text-slate-400">
                                Fecha objetivo: <span class="font-medium text-[#0F172A] dark:text-slate-200">{{ \Carbon\CarbonImmutable::parse($plan->targetDate)->format('d/m/Y') }}</span>
                                ({{ $plan->remainingMonths }} {{ $plan->remainingMonths === 1 ? 'mes restante' : 'meses restantes' }})
                            </p>
                        </div>

                        <div class="text-right">
                            <span class="text-xs text-[#64748B] dark:text-slate-400">Objetivo</span>
                            <p class="text-lg font-bold text-[#0F172A] dark:text-white">
                                {{ \App\Support\MoneyFormatter::format($plan->targetAmount, $plan->currencyCode) }}
                            </p>
                        </div>
                    </div>

                    {{-- Barra de Progreso --}}
                    <div class="mt-4">
                        <div class="flex justify-between text-xs font-semibold">
                            <span class="text-[#10B981]">
                                Reconocido: {{ \App\Support\MoneyFormatter::format($plan->recognizedSavings, $plan->currencyCode) }}
                            </span>
                            <span class="text-[#64748B] dark:text-slate-400">
                                {{ $plan->completionPercentage }}%
                            </span>
                        </div>
                        <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div
                                class="h-full rounded-full bg-[#10B981] transition-all duration-300"
                                style="width: {{ min(100, $plan->completionPercentage) }}%"
                            ></div>
                        </div>
                    </div>

                    {{-- Métricas de Cuota Inteligente (Fórmulas Oficiales) --}}
                    <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-3 text-xs dark:border-slate-800">
                        <div>
                            <span class="text-[#64748B] dark:text-slate-400">Ahorro pendiente:</span>
                            <p class="font-semibold text-[#EF4444] dark:text-red-400">
                                {{ \App\Support\MoneyFormatter::format($plan->pendingSavings, $plan->currencyCode) }}
                            </p>
                        </div>

                        <div>
                            <span class="text-[#64748B] dark:text-slate-400">Cuota mensual sugerida:</span>
                            <p class="text-sm font-bold text-[#10B981]">
                                {{ \App\Support\MoneyFormatter::format($plan->recommendedMonthlyQuota, $plan->currencyCode) }}
                            </p>
                            @if ($plan->requiresAdjustment)
                                <span class="text-[10px] text-amber-600 dark:text-amber-400">
                                    Original: {{ \App\Support\MoneyFormatter::format($plan->originalFixedMonthlyQuota, $plan->currencyCode) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                wire:click="openEditModal({{ $plan->goalId }})"
                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1.5 text-[11px] font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800"
                                title="Editar meta"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                                <span>Editar</span>
                            </button>

                            <button
                                type="button"
                                wire:click="deleteGoal({{ $plan->goalId }})"
                                wire:confirm="¿Estás seguro de que deseas eliminar esta meta de ahorro? Si no tiene aportes registrados se eliminará."
                                class="inline-flex items-center gap-1 rounded-lg border border-red-200 px-2.5 py-1.5 text-[11px] font-medium text-red-600 transition hover:bg-red-50 dark:border-red-950/60 dark:text-red-400 dark:hover:bg-red-950/40"
                                title="Eliminar meta de ahorro"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                                <span>Eliminar</span>
                            </button>
                        </div>

                        @if (! $plan->isCompleted)
                            <button
                                type="button"
                                wire:click="openContributionModal({{ $plan->goalId }})"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-[#10B981] px-4 py-1.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#064E3B]"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>Registrar Aporte</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-800 dark:bg-slate-900">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#10B981]/10 text-[#10B981]">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-bold text-[#0F172A] dark:text-white">
                No tienes metas de ahorro activas
            </h3>
            <p class="mx-auto mt-2 max-w-md text-xs leading-relaxed text-[#64748B] dark:text-slate-400">
                Configura un objetivo financiero a 6 meses (fondo de emergencia, viaje, compra o inversión) y deja que el planificador calcule tus aportes periódicos sostenibles.
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
                    <span>Crear Mi Primera Meta a 6 Meses</span>
                </button>
            </div>
        </section>
    @endif

    {{-- Modal: Crear Meta de Ahorro --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-[#0F172A] dark:text-white">Nueva Meta de Ahorro</h3>
                        <p class="text-xs text-[#64748B] dark:text-slate-400">Planificador inteligente con horizonte de cálculo a 6 meses</p>
                    </div>
                    <button type="button" wire:click="closeCreateModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveGoal" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Nombre de la meta *</label>
                        <input type="text" wire:model="name" placeholder="Ej. Fondo de Emergencia, Vacaciones, Curso..." class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                        @error('name') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Monto objetivo *</label>
                            <div class="relative mt-1">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs text-slate-400">$</span>
                                <input type="text" wire:model="targetAmount" placeholder="1200.00" class="w-full rounded-xl border border-slate-200 pl-7 pr-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            </div>
                            @error('targetAmount') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Ahorro inicial ya disponible</label>
                            <div class="relative mt-1">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs text-slate-400">$</span>
                                <input type="text" wire:model="initialAmount" placeholder="0.00" class="w-full rounded-xl border border-slate-200 pl-7 pr-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            </div>
                            @error('initialAmount') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Plazo (meses) *</label>
                            <input type="number" min="1" max="60" wire:model="durationMonths" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            @error('durationMonths') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Modo de cálculo *</label>
                            <select wire:model="mode" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                <option value="adjustable">Ajustable (Recalcula ante omisiones)</option>
                                <option value="fixed">Fijo (Cuota constante predefinida)</option>
                            </select>
                            @error('mode') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">
                            Cuenta bancaria vinculada para custodia (Opcional)
                        </label>
                        <select wire:model="linkedAccountId" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                            <option value="">Ninguna (Ahorro multi-cuenta o reserva virtual)</option>
                            @foreach ($liquidAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="closeCreateModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-[#64748B] hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-xl bg-[#10B981] px-5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-[#064E3B]">
                            Programar Meta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal: Registrar Aporte a Meta --}}
    @if ($showContributionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-[#0F172A] dark:text-white">
                            Aportar a Meta — {{ $selectedGoal?->name }}
                        </h3>
                        <p class="text-xs text-[#64748B] dark:text-slate-400">
                            Registra un aporte real respaldado por movimientos o reservas contables
                        </p>
                    </div>
                    <button type="button" wire:click="closeContributionModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveContribution" class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Tipo de aporte *</label>
                            <select wire:model.live="contributionType" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                <option value="transfer">Transferencia a cuenta de ahorro</option>
                                <option value="reserve">Reserva contable en cuenta existente</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Monto del aporte *</label>
                            <div class="relative mt-1">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs text-slate-400">$</span>
                                <input type="text" wire:model="contributionAmount" placeholder="100.00" class="w-full rounded-xl border border-slate-200 pl-7 pr-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            </div>
                            @error('contributionAmount') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">
                                {{ $contributionType === 'transfer' ? 'Cuenta origen (débito) *' : 'Cuenta de la reserva *' }}
                            </label>
                            <select wire:model="sourceAccountId" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                <option value="">Selecciona cuenta...</option>
                                @foreach ($liquidAccounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency_code }})</option>
                                @endforeach
                            </select>
                            @error('sourceAccountId') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        @if ($contributionType === 'transfer')
                            <div>
                                <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Cuenta destino (ahorro) *</label>
                                <select wire:model="destinationAccountId" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                    <option value="">Selecciona destino...</option>
                                    @foreach ($liquidAccounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency_code }})</option>
                                    @endforeach
                                </select>
                                @error('destinationAccountId') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <div>
                                <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Fecha del aporte *</label>
                                <input type="date" wire:model="contributionDate" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                                @error('contributionDate') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    @if ($contributionType === 'transfer')
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Fecha de transferencia *</label>
                            <input type="date" wire:model="contributionDate" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            @error('contributionDate') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="closeContributionModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-[#64748B] hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-xl bg-[#10B981] px-5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-[#064E3B]">
                            Confirmar Aporte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal: Editar Meta de Ahorro --}}
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-[#0F172A] dark:text-white">Editar Meta de Ahorro</h3>
                        <p class="text-xs text-[#64748B] dark:text-slate-400">Modifica objetivo, duración o estado</p>
                    </div>
                    <button type="button" wire:click="closeEditModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="updateGoal" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Nombre del objetivo *</label>
                        <input type="text" wire:model="editName" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                        @error('editName') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Monto objetivo total *</label>
                            <input type="text" wire:model="editTargetAmount" placeholder="1200.00" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            @error('editTargetAmount') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Plazo en meses (1-60) *</label>
                            <input type="number" min="1" max="60" wire:model="editDurationMonths" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            @error('editDurationMonths') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Modalidad de Cuota *</label>
                            <select wire:model="editMode" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                <option value="adjustable">Ajustable dinámicamente (Recomendado)</option>
                                <option value="fixed">Fija / Estricta</option>
                            </select>
                            @error('editMode') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Estado de la Meta *</label>
                            <select wire:model="editStatus" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                <option value="active">Activa</option>
                                <option value="completed">Completada</option>
                                <option value="paused">En pausa</option>
                                <option value="cancelled">Cancelada</option>
                            </select>
                            @error('editStatus') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Cuenta vinculada de custodia (Opcional)</label>
                        <select wire:model="editLinkedAccountId" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                            <option value="">Sin cuenta fija asignada</option>
                            @foreach ($liquidAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Notas / Propósito</label>
                        <input type="text" wire:model="editNotes" placeholder="Detalles de tu objetivo..." class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="closeEditModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-[#64748B] hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-xl bg-[#10B981] px-5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-[#064E3B]">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

