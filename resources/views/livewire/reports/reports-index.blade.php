<div class="space-y-6">
    {{-- Encabezado y Selector de Período --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-[#10B981]/10 px-3 py-1 text-xs font-semibold text-[#064E3B] dark:bg-[#10B981]/15 dark:text-emerald-300">
                <span>Análisis Multitemporal</span>
                <span>·</span>
                <span>Visualización con Chart.js</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-[#0F172A] dark:text-white">
                Reportes y Evolución Financiera
            </h1>
            <p class="text-sm text-[#64748B] dark:text-slate-400">
                Visualiza el comportamiento de tus ingresos, gastos, distribución por categoría y la evolución de tu patrimonio.
            </p>
        </div>

        {{-- Selector de Rango de Meses --}}
        <div class="inline-flex items-center rounded-xl border border-slate-200 bg-white p-1 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <button
                type="button"
                wire:click="setTimeframe(3)"
                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $report->periodMonths === 3 ? 'bg-[#10B981] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0F172A] dark:text-slate-400 dark:hover:text-white' }}"
            >
                Últimos 3 meses
            </button>
            <button
                type="button"
                wire:click="setTimeframe(6)"
                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $report->periodMonths === 6 ? 'bg-[#10B981] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0F172A] dark:text-slate-400 dark:hover:text-white' }}"
            >
                Últimos 6 meses
            </button>
            <button
                type="button"
                wire:click="setTimeframe(12)"
                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $report->periodMonths === 12 ? 'bg-[#10B981] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0F172A] dark:text-slate-400 dark:hover:text-white' }}"
            >
                Últimos 12 meses
            </button>
        </div>
    </div>

    {{-- Tarjetas KPI Consolidadas del Período --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">Ingresos Totales ({{ $report->periodMonths }}M)</span>
            <p class="mt-2 text-2xl font-bold tracking-tight text-[#10B981]">
                {{ \App\Support\MoneyFormatter::format($report->totalIncome, $report->currencyCode) }}
            </p>
            <span class="mt-1 block text-xs text-[#64748B] dark:text-slate-400">
                Suma acumulada del período
            </span>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">Gastos Totales ({{ $report->periodMonths }}M)</span>
            <p class="mt-2 text-2xl font-bold tracking-tight text-[#EF4444] dark:text-red-400">
                {{ \App\Support\MoneyFormatter::format($report->totalExpenses, $report->currencyCode) }}
            </p>
            <span class="mt-1 block text-xs text-[#64748B] dark:text-slate-400">
                Consumo total reconocido
            </span>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">Ahorro Neto Acumulado</span>
            <p class="mt-2 text-2xl font-bold tracking-tight {{ \App\Support\DecimalMath::compare($report->netSavings, '0') >= 0 ? 'text-[#064E3B] dark:text-emerald-400' : 'text-[#EF4444]' }}">
                {{ \App\Support\MoneyFormatter::format($report->netSavings, $report->currencyCode) }}
            </p>
            <span class="mt-1 block text-xs text-[#64748B] dark:text-slate-400">
                Ingresos menos gastos
            </span>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">Tasa Media de Ahorro</span>
            <div class="mt-2 flex items-baseline gap-2">
                <p class="text-2xl font-bold tracking-tight {{ $report->savingsRatePercent >= 20 ? 'text-[#10B981]' : ($report->savingsRatePercent > 0 ? 'text-amber-500' : 'text-[#EF4444]') }}">
                    {{ $report->savingsRatePercent }}%
                </p>
                <span class="text-xs text-[#64748B] dark:text-slate-400">
                    {{ $report->savingsRatePercent >= 20 ? 'Excelente margen' : ($report->savingsRatePercent > 0 ? 'Margen ajustado' : 'Sin ahorro') }}
                </span>
            </div>
            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div
                    class="h-full rounded-full {{ $report->savingsRatePercent >= 20 ? 'bg-[#10B981]' : ($report->savingsRatePercent > 0 ? 'bg-amber-400' : 'bg-[#EF4444]') }}"
                    style="width: {{ min(100, max(0, $report->savingsRatePercent)) }}%"
                ></div>
            </div>
        </div>
    </div>

    {{-- Gráficos Principales --}}
    @if ($report->hasData)
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Gráfico 1: Flujo de Caja Comparativo (Ingresos vs Gastos + Línea Flujo Neto) --}}
            <div class="lg:col-span-2 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                            Ingresos vs Gastos Mensuales
                        </h2>
                        <p class="text-xs text-[#64748B] dark:text-slate-400">
                            Evolución de flujo de efectivo en los últimos {{ $report->periodMonths }} meses
                        </p>
                    </div>
                </div>

                <div
                    wire:key="cash-flow-chart-{{ $report->periodMonths }}"
                    x-data="window.ahorviaCashFlowReportChart(
                        @js($report->monthlyLabels),
                        @js($report->monthlyIncomes),
                        @js($report->monthlyExpenses),
                        @js($report->monthlyNetSavings)
                    )"
                    class="mt-6 h-72 w-full"
                    wire:ignore
                >
                    <canvas x-ref="canvas" aria-label="Gráfico de ingresos versus gastos"></canvas>
                </div>
            </div>

            {{-- Gráfico 2: Distribución por Categoría (Doughnut) --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                    Distribución de Gastos
                </h2>
                <p class="text-xs text-[#64748B] dark:text-slate-400">
                    Proporción por categoría en el período
                </p>

                @if (count($report->categoryValues) > 0)
                    <div
                        wire:key="category-doughnut-{{ $report->periodMonths }}"
                        x-data="window.ahorviaCategoryDoughnutReportChart(
                            @js($report->categoryLabels),
                            @js($report->categoryValues),
                            @js($report->categoryColors)
                        )"
                        class="mt-4 h-60 w-full"
                        wire:ignore
                    >
                        <canvas x-ref="canvas" aria-label="Gráfico de distribución por categoría"></canvas>
                    </div>

                    <div class="mt-4 max-h-44 overflow-y-auto space-y-2 border-t border-slate-100 pt-3 dark:border-slate-800">
                        @foreach ($report->categoryTable as $item)
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                                    <span class="truncate font-medium text-[#0F172A] dark:text-slate-200">{{ $item['name'] }}</span>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-bold text-[#0F172A] dark:text-white">
                                        {{ \App\Support\MoneyFormatter::format($item['total'], $report->currencyCode) }}
                                    </span>
                                    <span class="ml-1 text-[11px] text-[#64748B] dark:text-slate-400">({{ $item['percent'] }}%)</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-8 flex h-48 flex-col items-center justify-center text-center">
                        <p class="text-xs text-[#64748B] dark:text-slate-400">Sin gastos clasificados en este período.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Gráfico 3: Evolución del Patrimonio Neto --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                        Evolución del Patrimonio Neto
                    </h2>
                    <p class="text-xs text-[#64748B] dark:text-slate-400">
                        Crecimiento del capital neto (Activos totales menos Pasivos totales) al cierre de cada mes
                    </p>
                </div>
            </div>

            <div
                wire:key="net-worth-chart-{{ $report->periodMonths }}"
                x-data="window.ahorviaNetWorthReportChart(
                    @js($report->monthlyLabels),
                    @js($report->monthlyNetWorth)
                )"
                class="mt-6 h-64 w-full"
                wire:ignore
            >
                <canvas x-ref="canvas" aria-label="Gráfico de evolución del patrimonio neto"></canvas>
            </div>
        </div>
    @else
        {{-- Estado Vacío --}}
        <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-800 dark:bg-slate-900">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#10B981]/10 text-[#10B981]">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-bold text-[#0F172A] dark:text-white">
                No hay movimientos registrados en este período
            </h3>
            <p class="mx-auto mt-2 max-w-md text-xs leading-relaxed text-[#64748B] dark:text-slate-400">
                A medida que registres ingresos, gastos y ahorros confirmados en Ahorvia, se graficarán automáticamente los comparativos mensuales y el crecimiento patrimonial.
            </p>
            <div class="mt-6">
                <a
                    href="{{ route('transactions.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#10B981] px-4 py-2.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#064E3B]"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Registrar Mi Primer Movimiento</span>
                </a>
            </div>
        </section>
    @endif
</div>

