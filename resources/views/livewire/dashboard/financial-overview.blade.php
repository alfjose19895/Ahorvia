<div class="space-y-6">
    {{-- Encabezado del Dashboard --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-[#10B981]/10 px-3 py-1 text-xs font-semibold text-[#064E3B] dark:bg-[#10B981]/15 dark:text-emerald-300">
                <span>Periodo contable: {{ $metrics->periodLabel }}</span>
                <span>·</span>
                <span>Moneda base: {{ $metrics->currencyCode }}</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-[#0F172A] dark:text-white">
                Hola, {{ explode(' ', trim($user->name))[0] }}
            </h1>
            <p class="text-sm text-[#64748B] dark:text-slate-400">
                Tu dinero bajo control. Tus metas más cerca.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a
                href="{{ route('accounts.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-[#0F172A] shadow-2xs transition hover:border-[#10B981] dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
            >
                <span>Configurar cuentas</span>
            </a>
            <a
                href="{{ route('savings.index') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-[#064E3B] px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-[#10B981]"
            >
                <span>Planificador de ahorro 6M</span>
            </a>
        </div>
    </div>

    {{-- Banner guía de estado inicial cuando aún no existen cuentas registradas --}}
    @if (! $metrics->hasAccounts)
        <section class="rounded-2xl border border-emerald-200/80 bg-gradient-to-r from-emerald-50/90 via-white to-emerald-50/50 p-5 dark:border-emerald-900/50 dark:from-emerald-950/40 dark:via-slate-900 dark:to-slate-900">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="space-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#10B981]">
                        Estado Inicial Limpio · Partida Doble DECIMAL(19,4)
                    </span>
                    <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                        Tu panel financiero está listo para recibir tus primeras cuentas y metas
                    </h2>
                    <p class="text-xs leading-relaxed text-[#64748B] dark:text-slate-400">
                        Ahorvia calcula cada saldo desde movimientos contables reales sin datos ficticios. Activa la verificación en dos pasos (2FA) en Configuración para blindar tu cuenta desde el primer día.
                    </p>
                </div>

                @if (! $user->hasConfirmedTwoFactor())
                    <a
                        href="{{ route('settings.index') }}"
                        class="inline-flex shrink-0 items-center justify-center rounded-xl bg-[#10B981] px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-[#064E3B]"
                    >
                        Activar seguridad 2FA
                    </a>
                @endif
            </div>
        </section>
    @endif

    {{-- Bloque 1: Liquidez y Balance Patrimonial (4 KPIs principales) --}}
    <section aria-label="Resumen patrimonial" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-400">
                    Dinero disponible
                </p>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#10B981]/12 text-[#10B981]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-2xl font-bold tabular-nums text-[#0F172A] dark:text-white">
                {{ $metrics->format($metrics->availableCash) }}
            </p>
            <p class="mt-1 text-xs text-[#64748B] dark:text-slate-400">
                Liquidez inmediata libre de reservas
            </p>
        </article>

        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-400">
                    Total de activos
                </p>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 text-[#064E3B] dark:text-emerald-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-2xl font-bold tabular-nums text-[#0F172A] dark:text-white">
                {{ $metrics->format($metrics->totalAssets) }}
            </p>
            <p class="mt-1 text-xs text-[#64748B] dark:text-slate-400">
                Efectivo, cuentas bancarias y ahorros
            </p>
        </article>

        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-400">
                    Total de pasivos
                </p>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#EF4444]/10 text-[#EF4444]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-2xl font-bold tabular-nums text-[#0F172A] dark:text-white">
                {{ $metrics->format($metrics->totalLiabilities) }}
            </p>
            <p class="mt-1 text-xs text-[#64748B] dark:text-slate-400">
                Tarjetas de crédito, préstamos y deudas
            </p>
        </article>

        <article class="rounded-2xl border border-[#10B981]/30 bg-gradient-to-br from-[#0F172A] to-[#064E3B] p-5 text-white shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-200">
                    Patrimonio neto
                </p>
                <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-bold text-emerald-200">
                    Activos − Pasivos
                </span>
            </div>
            <p class="mt-3 text-2xl font-bold tabular-nums text-white">
                {{ $metrics->format($metrics->netWorth) }}
            </p>
            <p class="mt-1 text-xs text-emerald-100/80">
                Posición patrimonial consolidada
            </p>
        </article>
    </section>

    {{-- Bloque 2: Flujo Mensual y Ahorro (4 KPIs operativos) --}}
    <section aria-label="Flujo mensual y ahorro" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-400">
                Ingresos mensuales
            </p>
            <p class="mt-2 text-xl font-bold tabular-nums text-[#10B981]">
                {{ $metrics->format($metrics->monthlyIncome) }}
            </p>
            <p class="mt-1 text-xs text-[#64748B] dark:text-slate-400">
                Entradas confirmadas en {{ $metrics->periodLabel }}
            </p>
        </article>

        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-400">
                Gastos mensuales
            </p>
            <p class="mt-2 text-xl font-bold tabular-nums text-[#EF4444]">
                {{ $metrics->format($metrics->monthlyExpenses) }}
            </p>
            <p class="mt-1 text-xs text-[#64748B] dark:text-slate-400">
                Salidas y consumos sin doble conteo
            </p>
        </article>

        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-400">
                Flujo de efectivo
            </p>
            <p class="mt-2 text-xl font-bold tabular-nums text-[#0F172A] dark:text-white">
                {{ $metrics->format($metrics->monthlyCashFlow) }}
            </p>
            <p class="mt-1 text-xs text-[#64748B] dark:text-slate-400">
                Balance neto del mes actual
            </p>
        </article>

        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#64748B] dark:text-slate-400">
                Ahorro acumulado
            </p>
            <p class="mt-2 text-xl font-bold tabular-nums text-[#064E3B] dark:text-emerald-400">
                {{ $metrics->format($metrics->accumulatedSavings) }}
            </p>
            <p class="mt-1 text-xs text-[#64748B] dark:text-slate-400">
                Aportes reconocidos hacia objetivos
            </p>
        </article>
    </section>

    {{-- Bloque 3: Gráficos Interactivos (Evolución Financiera Mensual + Distribución de Gastos) --}}
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <article class="lg:col-span-2 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                        Evolución financiera mensual
                    </h2>
                    <p class="text-xs text-[#64748B] dark:text-slate-400">
                        Comparativa de ingresos, gastos y ahorro de los últimos 6 meses
                    </p>
                </div>
                @if (! $metrics->hasTransactions)
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-[#64748B] dark:bg-slate-800 dark:text-slate-300">
                        Sin movimientos registrados aún
                    </span>
                @endif
            </div>

            <div
                x-data="window.ahorviaEvolutionChart(@js($metrics->monthlyEvolution))"
                class="mt-4 h-64 w-full"
                wire:ignore
            >
                <canvas x-ref="canvas" aria-label="Gráfico de evolución financiera mensual"></canvas>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                Distribución de gastos
            </h2>
            <p class="text-xs text-[#64748B] dark:text-slate-400">
                Participación por categoría en {{ $metrics->periodLabel }}
            </p>

            @if (count($metrics->expenseDistribution) === 0)
                <div class="mt-6 flex h-52 flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/70 p-6 text-center dark:border-slate-800 dark:bg-slate-950/50">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-slate-200/70 text-[#64748B] dark:bg-slate-800 dark:text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                        </svg>
                    </span>
                    <p class="mt-3 text-sm font-semibold text-[#0F172A] dark:text-white">
                        Sin gastos en este mes
                    </p>
                    <p class="mt-1 text-xs text-[#64748B] dark:text-slate-400">
                        Al registrar tus primeros consumos verás aquí la proporción exacta por categoría.
                    </p>
                </div>
            @else
                <ul class="mt-5 space-y-3">
                    @foreach ($metrics->expenseDistribution as $item)
                        <li class="flex items-center justify-between rounded-xl border border-slate-100 px-3 py-2.5 text-xs dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                                <span class="font-medium text-[#0F172A] dark:text-slate-200">{{ $item['category'] }}</span>
                            </div>
                            <span class="font-bold tabular-nums text-[#EF4444]">
                                {{ $metrics->format(number_format($item['amount'], 4, '.', '')) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </article>
    </section>

    {{-- Bloque 4: Metas Activas y Cumplimiento + Próximos Vencimientos + Alertas Financieras --}}
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Metas activas y porcentaje de cumplimiento --}}
        <article class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                        Metas activas
                    </h2>
                    <p class="text-xs text-[#64748B] dark:text-slate-400">
                        Planificador inteligente a 6 meses
                    </p>
                </div>
                <span class="rounded-full bg-[#10B981]/12 px-2.5 py-1 text-xs font-bold tabular-nums text-[#064E3B] dark:text-emerald-300">
                    {{ $metrics->overallGoalsCompletionPercent }}% cumplimiento
                </span>
            </div>

            <div class="mt-4">
                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div
                        class="h-full rounded-full bg-[#10B981] transition-all duration-300"
                        style="width: {{ min(100, max(0, $metrics->overallGoalsCompletionPercent)) }}%"
                    ></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs text-[#64748B] dark:text-slate-400">
                    <span>{{ $metrics->activeGoalsCount }} metas activas</span>
                    <span>Objetivo inicial: 6 meses</span>
                </div>
            </div>

            @if (count($metrics->activeGoals) === 0)
                <div class="mt-4 rounded-xl border border-dashed border-slate-200 p-4 text-center dark:border-slate-800">
                    <p class="text-xs font-medium text-[#0F172A] dark:text-slate-200">
                        Aún no has definido metas de ahorro
                    </p>
                    <p class="mt-1 text-xs text-[#64748B] dark:text-slate-400">
                        Proyecta cuotas fijas o ajustables según tu capacidad financiera real.
                    </p>
                    <a
                        href="{{ route('savings.index') }}"
                        class="mt-3 inline-flex items-center text-xs font-semibold text-[#10B981] hover:text-[#064E3B] dark:hover:text-emerald-300"
                    >
                        Ir al planificador de ahorro &rarr;
                    </a>
                </div>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach ($metrics->activeGoals as $goal)
                        <li class="rounded-xl border border-slate-100 p-3 text-xs dark:border-slate-800">
                            <div class="flex items-center justify-between font-semibold text-[#0F172A] dark:text-white">
                                <span>{{ $goal['name'] }}</span>
                                <span class="tabular-nums text-[#10B981]">{{ $goal['progress'] }}%</span>
                            </div>
                            <p class="mt-1 tabular-nums text-[#64748B] dark:text-slate-400">
                                {{ $goal['current'] }} de {{ $goal['target'] }} · Meta: {{ $goal['target_date'] }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </article>

        {{-- Próximos vencimientos --}}
        <article class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                        Próximos vencimientos
                    </h2>
                    <p class="text-xs text-[#64748B] dark:text-slate-400">
                        Tarjetas, préstamos y obligaciones programadas
                    </p>
                </div>
            </div>

            @if (count($metrics->upcomingMaturities) === 0)
                <div class="mt-5 rounded-xl border border-dashed border-slate-200 p-5 text-center dark:border-slate-800">
                    <p class="text-xs font-semibold text-[#0F172A] dark:text-slate-200">
                        Sin vencimientos pendientes
                    </p>
                    <p class="mt-1 text-xs text-[#64748B] dark:text-slate-400">
                        Aquí aparecerán las fechas de corte y pago de tus tarjetas, préstamos y obligaciones recurrentes.
                    </p>
                </div>
            @else
                <ul class="mt-4 space-y-2.5">
                    @foreach ($metrics->upcomingMaturities as $maturity)
                        <li class="flex items-center justify-between rounded-xl border border-slate-100 p-3 text-xs dark:border-slate-800">
                            <div>
                                <p class="font-semibold text-[#0F172A] dark:text-white">{{ $maturity['title'] }}</p>
                                <p class="text-[#64748B] dark:text-slate-400">Vence: {{ $maturity['due_date'] }} ({{ $maturity['type'] }})</p>
                            </div>
                            <span class="font-bold tabular-nums text-[#0F172A] dark:text-slate-200">{{ $maturity['amount'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </article>

        {{-- Alertas financieras --}}
        <article class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#0F172A] dark:text-white">
                        Alertas financieras
                    </h2>
                    <p class="text-xs text-[#64748B] dark:text-slate-400">
                        Umbrales de presupuesto (80% / 90% / 100%) y ahorro
                    </p>
                </div>
            </div>

            @if (count($metrics->financialAlerts) === 0)
                <div class="mt-5 rounded-xl border border-emerald-200/60 bg-emerald-50/50 p-5 text-center dark:border-emerald-900/40 dark:bg-emerald-950/20">
                    <p class="text-xs font-semibold text-[#064E3B] dark:text-emerald-300">
                        Todo en orden
                    </p>
                    <p class="mt-1 text-xs text-[#64748B] dark:text-slate-400">
                        No tienes presupuestos excedidos ni aportes de ahorro atrasados en este momento.
                    </p>
                </div>
            @else
                <ul class="mt-4 space-y-2.5">
                    @foreach ($metrics->financialAlerts as $alert)
                        <li class="rounded-xl border {{ $alert['level'] === 'danger' ? 'border-red-200 bg-red-50/60 dark:border-red-900/50 dark:bg-red-950/20' : 'border-amber-200 bg-amber-50/60 dark:border-amber-900/50 dark:bg-amber-950/20' }} p-3 text-xs">
                            <p class="font-semibold {{ $alert['level'] === 'danger' ? 'text-[#EF4444]' : 'text-amber-800 dark:text-amber-300' }}">
                                {{ $alert['title'] }}
                            </p>
                            <p class="mt-0.5 text-[#64748B] dark:text-slate-300">{{ $alert['message'] }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </article>
    </section>
</div>

