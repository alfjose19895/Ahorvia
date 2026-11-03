<div class="space-y-6">
    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-[#10B981]/10 px-3 py-1 text-xs font-semibold text-[#064E3B] dark:bg-[#10B981]/15 dark:text-emerald-300">
                <span>Alertas en Tiempo Real</span>
                <span>·</span>
                <span>Vencimientos y Presupuestos</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-[#0F172A] dark:text-white">
                Centro de Notificaciones
            </h1>
            <p class="text-sm text-[#64748B] dark:text-slate-400">
                Supervisión proactiva de vencimientos próximos, umbrales de consumo presupuestario y ajustes de metas.
            </p>
        </div>
    </div>

    {{-- Filtros por Nivel de Severidad --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3 dark:border-slate-800">
        <button
            type="button"
            wire:click="setFilter('all')"
            class="inline-flex items-center gap-2 rounded-xl px-3.5 py-1.5 text-xs font-semibold transition {{ $filter === 'all' ? 'bg-[#0F172A] text-white shadow-xs dark:bg-white dark:text-[#0F172A]' : 'bg-slate-100 text-[#64748B] hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300' }}"
        >
            <span>Todas</span>
            <span class="rounded-full bg-slate-200/80 px-2 py-0.2 text-[10px] {{ $filter === 'all' ? 'text-[#0F172A] dark:text-white dark:bg-slate-700' : 'text-[#64748B] dark:text-slate-400' }}">
                {{ $counts['all'] }}
            </span>
        </button>

        <button
            type="button"
            wire:click="setFilter('danger')"
            class="inline-flex items-center gap-2 rounded-xl px-3.5 py-1.5 text-xs font-semibold transition {{ $filter === 'danger' ? 'bg-[#EF4444] text-white shadow-xs' : 'bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-300' }}"
        >
            <span class="h-2 w-2 rounded-full bg-[#EF4444]"></span>
            <span>Críticas</span>
            <span class="rounded-full bg-red-100 px-2 py-0.2 text-[10px] text-red-800 dark:bg-red-900/50 dark:text-red-300">
                {{ $counts['danger'] }}
            </span>
        </button>

        <button
            type="button"
            wire:click="setFilter('warning')"
            class="inline-flex items-center gap-2 rounded-xl px-3.5 py-1.5 text-xs font-semibold transition {{ $filter === 'warning' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 dark:bg-amber-950/40 dark:text-amber-300' }}"
        >
            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
            <span>Advertencias</span>
            <span class="rounded-full bg-amber-100 px-2 py-0.2 text-[10px] text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                {{ $counts['warning'] }}
            </span>
        </button>

        <button
            type="button"
            wire:click="setFilter('info')"
            class="inline-flex items-center gap-2 rounded-xl px-3.5 py-1.5 text-xs font-semibold transition {{ $filter === 'info' ? 'bg-[#10B981] text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300' }}"
        >
            <span class="h-2 w-2 rounded-full bg-[#10B981]"></span>
            <span>Informativas</span>
            <span class="rounded-full bg-emerald-100 px-2 py-0.2 text-[10px] text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                {{ $counts['info'] }}
            </span>
        </button>
    </div>

    {{-- Lista de Notificaciones --}}
    @if (count($notifications) > 0)
        <div class="space-y-3">
            @foreach ($notifications as $notification)
                @php
                    $isDanger = $notification['level'] === 'danger';
                    $isWarning = $notification['level'] === 'warning';
                @endphp
                <div class="flex flex-col gap-3 rounded-2xl border bg-white p-5 shadow-2xs sm:flex-row sm:items-center sm:justify-between dark:bg-slate-900 {{ $isDanger ? 'border-red-200 dark:border-red-900/50' : ($isWarning ? 'border-amber-200 dark:border-amber-900/50' : 'border-slate-200/80 dark:border-slate-800') }}">
                    <div class="flex items-start gap-4">
                        <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $isDanger ? 'bg-red-50 text-[#EF4444] dark:bg-red-950/60' : ($isWarning ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/60' : 'bg-emerald-50 text-[#10B981] dark:bg-emerald-950/60') }}">
                            @if ($isDanger)
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 6.75h.008v.008H12v-.008Z" />
                                </svg>
                            @elseif ($isWarning)
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            @else
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                </svg>
                            @endif
                        </span>

                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-[#64748B] dark:bg-slate-800 dark:text-slate-300">
                                    {{ $notification['category'] }}
                                </span>
                                <span class="text-[11px] text-[#64748B] dark:text-slate-400">
                                    {{ $notification['created_at'] }}
                                </span>
                            </div>
                            <h3 class="mt-1 text-sm font-bold text-[#0F172A] dark:text-white">
                                {{ $notification['title'] }}
                            </h3>
                            <p class="mt-0.5 text-xs text-[#64748B] dark:text-slate-300">
                                {{ $notification['message'] }}
                            </p>
                        </div>
                    </div>

                    <div class="sm:shrink-0 sm:text-right">
                        <a
                            href="{{ route($notification['action_route']) }}"
                            class="inline-flex items-center gap-1 rounded-xl bg-slate-100 px-3.5 py-1.5 text-xs font-semibold text-[#0F172A] transition hover:bg-[#10B981] hover:text-white dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-[#10B981] dark:hover:text-white"
                        >
                            <span>{{ $notification['action_label'] }}</span>
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-800 dark:bg-slate-900">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-[#10B981] dark:bg-emerald-950/60">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-bold text-[#0F172A] dark:text-white">
                Bandeja limpia
            </h3>
            <p class="mx-auto mt-2 max-w-md text-xs leading-relaxed text-[#64748B] dark:text-slate-400">
                No tienes notificaciones pendientes para este filtro. Todas tus obligaciones y presupuestos están dentro de los parámetros previstos.
            </p>
        </section>
    @endif
</div>

