<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-[#10B981]">
                {{ $eyebrow }}
            </span>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-[#0F172A] dark:text-white">
                {{ $title }}
            </h1>
        </div>
        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-[#064E3B] dark:border-emerald-900/60 dark:bg-emerald-950/50 dark:text-emerald-300">
            {{ $phaseBadge }}
        </span>
    </div>

    <section class="rounded-2xl border border-slate-200/80 bg-white p-7 sm:p-9 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
        <div class="max-w-2xl space-y-4">
            <p class="text-sm leading-relaxed text-[#64748B] dark:text-slate-300">
                {{ $description }}
            </p>

            <div class="grid grid-cols-1 gap-2.5 pt-2 sm:grid-cols-2">
                @foreach ($highlights as $highlight)
                    <div class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/70 p-3 text-xs font-medium text-[#0F172A] dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-200">
                        <span class="mt-0.5 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-[#10B981]/15 text-[#10B981]">
                            ✓
                        </span>
                        <span>{{ $highlight }}</span>
                    </div>
                @endforeach
            </div>

            <div class="pt-4">
                <a
                    href="{{ route('dashboard') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#0F172A] px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-[#064E3B] dark:bg-slate-800 dark:hover:bg-[#10B981]"
                >
                    <span>&larr; Volver al Dashboard Financiero</span>
                </a>
            </div>
        </div>
    </section>
</div>

