@props([
    'size' => 'md',
    'showText' => true,
    'showSlogan' => false,
])

@php
    $iconDimensions = match ($size) {
        'sm' => 'h-8 w-8',
        'lg' => 'h-12 w-12',
        default => 'h-10 w-10',
    };

    $titleClass = match ($size) {
        'sm' => 'text-lg',
        'lg' => 'text-2xl',
        default => 'text-xl',
    };
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <span class="{{ $iconDimensions }} relative inline-flex shrink-0 items-center justify-center rounded-xl bg-gradient-to-tr from-[#064E3B] to-[#10B981] shadow-sm shadow-emerald-900/20 ring-1 ring-white/15">
        <svg viewBox="0 0 64 64" fill="none" class="h-6 w-6" aria-hidden="true">
            <path d="M15 45L27.5 23.5L35.5 34L47 18" stroke="#FFFFFF" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M39 18H47V26" stroke="#FFFFFF" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="47" cy="45" r="4.5" fill="#A7F3D0" />
        </svg>
    </span>

    @if ($showText)
        <div class="flex flex-col leading-tight">
            <span class="{{ $titleClass }} font-bold tracking-tight text-[#0F172A] dark:text-white">
                Ahorvia
            </span>
            @if ($showSlogan)
                <span class="text-xs font-medium text-[#64748B] dark:text-slate-400">
                    Tu dinero bajo control. Tus metas más cerca.
                </span>
            @endif
        </div>
    @endif
</div>

