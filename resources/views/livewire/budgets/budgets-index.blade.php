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

    {{-- Encabezado y Navegación de Período --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-[#10B981]/10 px-3 py-1 text-xs font-semibold text-[#064E3B] dark:bg-[#10B981]/15 dark:text-emerald-300">
                <span>Control Mensual de Gastos</span>
                <span>·</span>
                <span>Umbrales 80%, 90% y 100%</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-[#0F172A] dark:text-white">
                Presupuestos por Categoría
            </h1>
            <p class="text-sm text-[#64748B] dark:text-slate-400">
                Monitorea tus límites mensuales frente al gasto contable real para proteger tu capacidad de ahorro.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {{-- Selector de Mes --}}
            <div class="inline-flex items-center rounded-xl border border-slate-200 bg-white p-1 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
                <button
                    type="button"
                    wire:click="previousMonth"
                    class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white"
                    title="Mes anterior"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </button>
                <button
                    type="button"
                    wire:click="currentMonth"
                    class="px-3 py-1 text-xs font-semibold text-[#0F172A] hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    {{ $monthName }}
                </button>
                <button
                    type="button"
                    wire:click="nextMonth"
                    class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white"
                    title="Mes siguiente"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </div>

            <button
                type="button"
                wire:click="openManageModal"
                class="inline-flex items-center gap-1.5 rounded-xl bg-[#10B981] px-4 py-2 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#064E3B]"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                <span>{{ $budget !== null ? 'Ajustar Presupuesto' : 'Configurar Presupuesto' }}</span>
            </button>
        </div>
    </div>

    @if ($execution !== null)
        {{-- Tarjetas KPI de Ejecución --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
                <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">Total Presupuestado</span>
                <p class="mt-2 text-2xl font-bold tracking-tight text-[#0F172A] dark:text-white">
                    {{ \App\Support\MoneyFormatter::format($execution->totalLimit, $execution->currencyCode) }}
                </p>
                <span class="mt-1 block text-xs text-[#64748B] dark:text-slate-400">
                    Límite global aprobado
                </span>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
                <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">Total Ejecutado</span>
                <p class="mt-2 text-2xl font-bold tracking-tight text-[#EF4444] dark:text-red-400">
                    {{ \App\Support\MoneyFormatter::format($execution->totalSpent, $execution->currencyCode) }}
                </p>
                <span class="mt-1 block text-xs text-[#64748B] dark:text-slate-400">
                    Gasto contable registrado
                </span>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
                <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">Saldo Disponible</span>
                <p class="mt-2 text-2xl font-bold tracking-tight {{ \App\Support\DecimalMath::compare($execution->remainingAmount, '0') >= 0 ? 'text-[#10B981]' : 'text-[#EF4444]' }}">
                    {{ \App\Support\MoneyFormatter::format($execution->remainingAmount, $execution->currencyCode) }}
                </p>
                <span class="mt-1 block text-xs text-[#64748B] dark:text-slate-400">
                    Margen restante del mes
                </span>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
                <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">% Consumo Global</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-2xl font-bold tracking-tight {{ $execution->consumptionPercent >= 100 ? 'text-[#EF4444]' : ($execution->consumptionPercent >= 80 ? 'text-amber-500' : 'text-[#10B981]') }}">
                        {{ $execution->consumptionPercent }}%
                    </p>
                    @if ($execution->highestThresholdReached >= 100)
                        <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-700 dark:bg-red-950/60 dark:text-red-400">Excedido</span>
                    @elseif ($execution->highestThresholdReached >= 90)
                        <span class="rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-bold text-orange-700 dark:bg-orange-950/60 dark:text-orange-400">Alerta 90%</span>
                    @elseif ($execution->highestThresholdReached >= 80)
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-950/60 dark:text-amber-400">Alerta 80%</span>
                    @else
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">En orden</span>
                    @endif
                </div>
                {{-- Barra progreso global --}}
                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div
                        class="h-full rounded-full transition-all duration-300 {{ $execution->consumptionPercent >= 100 ? 'bg-[#EF4444]' : ($execution->consumptionPercent >= 90 ? 'bg-orange-500' : ($execution->consumptionPercent >= 80 ? 'bg-amber-400' : 'bg-[#10B981]')) }}"
                        style="width: {{ min(100, $execution->consumptionPercent) }}%"
                    ></div>
                </div>
            </div>
        </div>

        {{-- Detalle por Categoría --}}
        <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                    Ejecución por Categorías
                </h2>
                <span class="text-xs text-[#64748B] dark:text-slate-400">
                    {{ count($execution->lines) }} categorías asignadas
                </span>
            </div>

            <div class="mt-4 space-y-4">
                @foreach ($execution->lines as $line)
                    @php
                        $pct = $line['consumption_percent'];
                        $isExceeded = $pct >= 100;
                        $isCritical = $pct >= 90 && $pct < 100;
                        $isWarning = $pct >= 80 && $pct < 90;
                    @endphp
                    <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4 transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950/40 dark:hover:bg-slate-800/40">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="h-2.5 w-2.5 rounded-full {{ $isExceeded ? 'bg-[#EF4444]' : ($isCritical ? 'bg-orange-500' : ($isWarning ? 'bg-amber-400' : 'bg-[#10B981]')) }}"></span>
                                <h3 class="text-sm font-semibold text-[#0F172A] dark:text-white">
                                    {{ $line['category_name'] }}
                                </h3>
                                @if ($line['alert_threshold'] >= 100)
                                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-700 dark:bg-red-950/60 dark:text-red-400">Límite excedido</span>
                                @elseif ($line['alert_threshold'] >= 90)
                                    <span class="rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-bold text-orange-700 dark:bg-orange-950/60 dark:text-orange-400">Consumo al {{ $pct }}%</span>
                                @elseif ($line['alert_threshold'] >= 80)
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-950/60 dark:text-amber-400">Consumo al {{ $pct }}%</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-4 text-xs">
                                <div>
                                    <span class="text-[#64748B] dark:text-slate-400">Gastado: </span>
                                    <span class="font-semibold text-[#0F172A] dark:text-white">
                                        {{ \App\Support\MoneyFormatter::format($line['spent_amount'], $execution->currencyCode) }}
                                    </span>
                                </div>
                                <span class="text-slate-300 dark:text-slate-700">/</span>
                                <div>
                                    <span class="text-[#64748B] dark:text-slate-400">Límite: </span>
                                    <span class="font-semibold text-[#0F172A] dark:text-white">
                                        {{ \App\Support\MoneyFormatter::format($line['limit_amount'], $execution->currencyCode) }}
                                    </span>
                                </div>
                                <span class="text-slate-300 dark:text-slate-700">·</span>
                                <div class="font-semibold {{ \App\Support\DecimalMath::compare($line['remaining_amount'], '0') >= 0 ? 'text-[#10B981]' : 'text-[#EF4444]' }}">
                                    {{ \App\Support\DecimalMath::compare($line['remaining_amount'], '0') >= 0 ? 'Disponible: ' : 'Exceso: ' }}
                                    {{ \App\Support\MoneyFormatter::format($line['remaining_amount'], $execution->currencyCode) }}
                                </div>
                            </div>
                        </div>

                        {{-- Barra de Progreso --}}
                        <div class="mt-3 flex items-center gap-3">
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200/80 dark:bg-slate-800">
                                <div
                                    class="h-full rounded-full transition-all duration-300 {{ $isExceeded ? 'bg-[#EF4444]' : ($isCritical ? 'bg-orange-500' : ($isWarning ? 'bg-amber-400' : 'bg-[#10B981]')) }}"
                                    style="width: {{ min(100, $pct) }}%"
                                ></div>
                            </div>
                            <span class="shrink-0 text-xs font-bold {{ $isExceeded ? 'text-[#EF4444]' : ($isCritical ? 'text-orange-500' : ($isWarning ? 'text-amber-500' : 'text-[#10B981]')) }}">
                                {{ $pct }}%
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @else
        {{-- Estado Vacío: Sin Presupuesto Configurado para este Mes --}}
        <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-800 dark:bg-slate-900">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#10B981]/10 text-[#10B981]">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-bold text-[#0F172A] dark:text-white">
                No hay presupuesto configurado para {{ $monthName }}
            </h3>
            <p class="mx-auto mt-2 max-w-md text-xs leading-relaxed text-[#64748B] dark:text-slate-400">
                Establece límites de consumo para tus categorías de gasto para recibir alertas tempranas antes de sobrepasar tus previsiones y proteger tu plan de ahorro.
            </p>
            <div class="mt-6">
                <button
                    type="button"
                    wire:click="openManageModal"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#10B981] px-4 py-2.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#064E3B]"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Configurar Presupuesto de {{ $monthName }}</span>
                </button>
            </div>
        </section>
    @endif

    {{-- Modal de Configuración de Presupuesto --}}
    @if ($showManageModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div class="relative w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-[#0F172A] dark:text-white">
                            Configurar Presupuesto — {{ $monthName }}
                        </h3>
                        <p class="text-xs text-[#64748B] dark:text-slate-400">
                            Asigna el límite máximo de gasto previsto para cada categoría
                        </p>
                    </div>
                    <button type="button" wire:click="closeManageModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveBudget" class="mt-4 space-y-4">
                    @error('limits')
                        <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-medium text-red-600 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="space-y-3">
                        @forelse ($expenseCategories as $cat)
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/60 p-3 dark:border-slate-800 dark:bg-slate-950/50">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full" style="background-color: {{ $cat->color }}"></span>
                                    <span class="text-xs font-semibold text-[#0F172A] dark:text-white">
                                        {{ $cat->name }}
                                    </span>
                                </div>
                                <div class="w-36">
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-xs text-slate-400">$</span>
                                        <input
                                            type="text"
                                            wire:model="limits.{{ $cat->id }}"
                                            placeholder="0.00"
                                            class="w-full rounded-lg border border-slate-200 bg-white pl-6 pr-2.5 py-1.5 text-xs text-right font-medium text-[#0F172A] focus:border-[#10B981] focus:ring-1 focus:ring-[#10B981] dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                                        />
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-[#64748B] dark:text-slate-400 text-center py-4">
                                No tienes categorías de gasto activas.
                            </p>
                        @endforelse
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">
                            Notas u observaciones (opcional)
                        </label>
                        <textarea
                            wire:model="budgetNotes"
                            rows="2"
                            placeholder="Ej. Mes con gastos extraordinarios por vacaciones escolares..."
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            wire:click="closeManageModal"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-[#64748B] hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="rounded-xl bg-[#10B981] px-5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-[#064E3B]"
                        >
                            Guardar Presupuesto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

