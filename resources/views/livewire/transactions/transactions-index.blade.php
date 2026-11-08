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
                <span>Libro Diario de Movimientos</span>
                <span>·</span>
                <span>Partida Doble DECIMAL(19,4)</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-[#0F172A] dark:text-white">
                Ingresos, Gastos y Transferencias
            </h1>
            <p class="text-sm text-[#64748B] dark:text-slate-400">
                Historial contable inmutable, control de consumos y reversos auditables.
            </p>
            @if ($movementFilter === 'income' || request()->query('tipo') === 'income')
                <p class="mt-1.5 text-xs font-semibold text-[#10B981]">
                    Nuevo ingreso seleccionado desde acceso rápido
                </p>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                wire:click="openCreateModal('income')"
                class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300/80 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-[#064E3B] shadow-2xs transition hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300"
            >
                <svg class="h-4 w-4 text-[#10B981]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>+ Ingreso</span>
            </button>

            <button
                type="button"
                wire:click="openCreateModal('expense')"
                class="inline-flex items-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2 text-xs font-semibold text-[#EF4444] shadow-2xs transition hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/40"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                </svg>
                <span>− Gasto</span>
            </button>

            <button
                type="button"
                wire:click="openCreateModal('transfer')"
                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-[#0F172A] shadow-2xs transition hover:border-[#10B981] dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
            >
                <svg class="h-4 w-4 text-[#10B981]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
                <span>Transferir</span>
            </button>
        </div>
    </div>

    {{-- Filtros y Búsqueda --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por descripción, referencia o # asiento..."
                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                />
            </div>

            <div>
                <select wire:model.live="movementFilter" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                    <option value="all">Todos los tipos</option>
                    <option value="income">Solo ingresos</option>
                    <option value="expense">Solo gastos</option>
                    <option value="transfer">Solo transferencias</option>
                    <option value="reversal">Solo reversos</option>
                </select>
            </div>

            <div>
                <select wire:model.live="accountFilter" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                    <option value="">Todas las cuentas</option>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <input
                    type="date"
                    wire:model.live="dateFrom"
                    placeholder="Desde"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                />
            </div>

            <div class="flex items-center gap-2">
                <input
                    type="date"
                    wire:model.live="dateTo"
                    placeholder="Hasta"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                />
                @if ($search || $movementFilter !== 'all' || $accountFilter || $categoryFilter || $dateFrom || $dateTo)
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="shrink-0 rounded-lg p-2 text-slate-400 hover:text-red-500"
                        title="Limpiar filtros"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    </section>

    {{-- Tabla de Movimientos --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-2xs dark:border-slate-800 dark:bg-slate-900">
        @if ($entries->isEmpty())
            <div class="p-12 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-[#64748B] dark:bg-slate-800">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="mt-3 text-sm font-bold text-[#0F172A] dark:text-white">
                    Sin movimientos registrados
                </h3>
                <p class="mt-1 text-xs text-[#64748B] dark:text-slate-400">
                    No se encontraron asientos contables con los filtros seleccionados.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70 font-semibold text-[#64748B] dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-400">
                            <th class="py-3 px-4">Fecha / N° Asiento</th>
                            <th class="py-3 px-4">Tipo</th>
                            <th class="py-3 px-4">Descripción / Referencia</th>
                            <th class="py-3 px-4">Detalle Contable</th>
                            <th class="py-3 px-4 text-right">Importe</th>
                            <th class="py-3 px-4 text-center">Estado</th>
                            <th class="py-3 px-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($entries as $entry)
                            @php
                                $total = $entry->totalDebits();
                                $isIncome = $entry->movement_type === \App\Enums\MovementType::Income;
                                $isExpense = in_array($entry->movement_type, [\App\Enums\MovementType::Expense, \App\Enums\MovementType::CardPurchase], true);
                                $isTransfer = $entry->movement_type === \App\Enums\MovementType::Transfer;
                                $isReversal = $entry->movement_type === \App\Enums\MovementType::Reversal;
                                $isReversed = $entry->status === \App\Enums\EntryStatus::Reversed;
                            @endphp
                            <tr class="transition hover:bg-slate-50/50 dark:hover:bg-slate-800/40 {{ $isReversed ? 'opacity-60 bg-slate-50/30' : '' }}">
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <p class="font-bold text-[#0F172A] dark:text-white">{{ $entry->entry_date->format('d/m/Y') }}</p>
                                    <p class="text-[10px] font-mono text-[#64748B] dark:text-slate-400">{{ $entry->entry_number }}</p>
                                </td>

                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider
                                        {{ $isIncome ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                                        {{ $isExpense ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300' : '' }}
                                        {{ $isTransfer ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : '' }}
                                        {{ $isReversal ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                                        {{ ! $isIncome && ! $isExpense && ! $isTransfer && ! $isReversal ? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' : '' }}
                                    ">
                                        {{ $entry->movement_type->label() }}
                                    </span>
                                </td>

                                <td class="py-3 px-4">
                                    <p class="font-medium text-[#0F172A] dark:text-white {{ $isReversed ? 'line-through' : '' }}">
                                        {{ $entry->description }}
                                    </p>
                                    @if ($entry->reference)
                                        <p class="text-[11px] text-[#64748B] dark:text-slate-400 font-mono">Ref: {{ $entry->reference }}</p>
                                    @endif
                                </td>

                                <td class="py-3 px-4 text-[11px] text-[#64748B] dark:text-slate-400">
                                    @foreach ($entry->lines as $line)
                                        @if (! $line->account?->is_system)
                                            <p>{{ $line->account?->name }} ({{ bccomp((string) $line->debit, '0.0000', 4) > 0 ? 'Débito' : 'Crédito' }})</p>
                                        @endif
                                        @if ($line->category)
                                            <p class="text-[10px] text-emerald-700 dark:text-emerald-400">Cat: {{ $line->category->name }}</p>
                                        @endif
                                    @endforeach
                                </td>

                                <td class="py-3 px-4 text-right font-bold tabular-nums whitespace-nowrap {{ $isReversed ? 'line-through text-slate-400' : ($isIncome ? 'text-[#10B981]' : ($isExpense ? 'text-[#EF4444]' : 'text-[#0F172A] dark:text-white')) }}">
                                    {{ $isIncome ? '+' : ($isExpense ? '−' : '') }}
                                    {{ \App\Support\MoneyFormatter::format($total, $entry->currency_code) }}
                                </td>

                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $entry->status === \App\Enums\EntryStatus::Posted ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ $entry->status->label() }}
                                    </span>
                                </td>

                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    @if ($entry->status === \App\Enums\EntryStatus::Posted && $entry->movement_type !== \App\Enums\MovementType::Reversal)
                                        <button
                                            type="button"
                                            wire:click="openReversalModal({{ $entry->id }})"
                                            class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50/50 px-2 py-1 text-[11px] font-semibold text-red-600 transition hover:bg-red-100 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400"
                                            title="Reversar asiento"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                            </svg>
                                            <span>Reversar</span>
                                        </button>
                                    @elseif ($entry->status === \App\Enums\EntryStatus::Reversed)
                                        <span class="text-[10px] text-slate-400 italic">Reversado</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $entries->links() }}
            </div>
        @endif
    </section>

    {{-- Modal: Registro Rápido (Ingreso / Gasto / Transferencia) --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="$set('transactionType', 'income')"
                            class="rounded-lg px-3 py-1 text-xs font-semibold {{ $transactionType === 'income' ? 'bg-[#10B981] text-white' : 'text-[#64748B] hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                        >
                            Ingreso
                        </button>
                        <button
                            type="button"
                            wire:click="$set('transactionType', 'expense')"
                            class="rounded-lg px-3 py-1 text-xs font-semibold {{ $transactionType === 'expense' ? 'bg-[#EF4444] text-white' : 'text-[#64748B] hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                        >
                            Gasto
                        </button>
                        <button
                            type="button"
                            wire:click="$set('transactionType', 'transfer')"
                            class="rounded-lg px-3 py-1 text-xs font-semibold {{ $transactionType === 'transfer' ? 'bg-[#064E3B] text-white' : 'text-[#64748B] hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                        >
                            Transferencia
                        </button>
                    </div>
                    <button type="button" wire:click="closeCreateModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveTransaction" class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">
                                {{ $transactionType === 'transfer' ? 'Cuenta origen *' : 'Cuenta *' }}
                            </label>
                            <select wire:model="accountId" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                <option value="">Selecciona cuenta</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency_code }})</option>
                                @endforeach
                            </select>
                            @error('accountId') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        @if ($transactionType === 'transfer')
                            <div>
                                <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Cuenta destino *</label>
                                <select wire:model="destinationAccountId" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                    <option value="">Selecciona destino</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency_code }})</option>
                                    @endforeach
                                </select>
                                @error('destinationAccountId') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <div>
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Categoría *</label>
                                    <button
                                        type="button"
                                        wire:click="openQuickCategoryModal('{{ $transactionType }}')"
                                        class="text-[11px] font-semibold text-[#10B981] hover:underline"
                                    >
                                        + Nueva opción
                                    </button>
                                </div>
                                <select wire:model="categoryId" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                    <option value="">Selecciona categoría</option>
                                    @if ($transactionType === 'income')
                                        @foreach ($incomeCategories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    @else
                                        @foreach ($expenseCategories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('categoryId') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Importe ({{ $currency }}) *</label>
                            <input type="text" wire:model="amount" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" placeholder="0.00" />
                            @error('amount') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Fecha contable *</label>
                            <input type="date" wire:model="entryDate" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" />
                            @error('entryDate') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Descripción *</label>
                        <input type="text" wire:model="description" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" placeholder="Ej. Almuerzo familiar, Sueldo quincenal" />
                        @error('description') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Referencia / Comprobante (opcional)</label>
                        <input type="text" wire:model="reference" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white" placeholder="N° de factura, recibo o voucher" />
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="closeCreateModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-[#64748B] hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-xl bg-[#10B981] px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-[#064E3B]">
                            Guardar movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal: Reverso Contable Auditable --}}
    @if ($showReversalModal && $reversingEntry)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <h3 class="text-base font-bold text-red-600 dark:text-red-400">
                        Reverso Contable Auditable
                    </h3>
                    <button type="button" wire:click="closeReversalModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 rounded-xl border border-red-100 bg-red-50/60 p-3 text-xs dark:border-red-950 dark:bg-red-950/20">
                    <p class="font-bold text-[#0F172A] dark:text-white">Asiento: {{ $reversingEntry->entry_number }}</p>
                    <p class="text-[#64748B] dark:text-slate-300 mt-1">{{ $reversingEntry->description }}</p>
                    <p class="mt-1 font-semibold text-red-600 dark:text-red-400">
                        Importe: {{ \App\Support\MoneyFormatter::format($reversingEntry->totalDebits(), $reversingEntry->currency_code) }}
                    </p>
                </div>

                <p class="mt-3 text-xs text-[#64748B] dark:text-slate-400">
                    Por principio de inmutabilidad contable, este movimiento no se eliminará físicamente. Se creará un contra-asiento de reverso exacto vinculado para fines de auditoría.
                </p>

                <form wire:submit="executeReversal" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Motivo del reverso *</label>
                        <input
                            type="text"
                            wire:model="reversalReason"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            placeholder="Ej. Cobro duplicado, error en importe, devolución"
                        />
                        @error('reversalReason') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="closeReversalModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-[#64748B] hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-red-700">
                            Confirmar reverso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal: Crear Categoría Rápida al Vuelo --}}
    @if ($showQuickCategoryModal)
        <div class="fixed inset-0 z-60 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="relative w-full max-w-sm rounded-2xl bg-white p-5 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-[#0F172A] dark:text-white">
                        + Nueva Categoría de {{ $quickCategoryType === 'income' ? 'Ingreso' : 'Gasto' }}
                    </h3>
                    <button type="button" wire:click="closeQuickCategoryModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveQuickCategory" class="mt-4 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Nombre de la categoría *</label>
                        <input
                            type="text"
                            wire:model="quickCategoryName"
                            placeholder="Ej. Mascotas, Streaming, Cursos..."
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-[#0F172A] dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            autofocus
                        />
                        @error('quickCategoryName') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0F172A] dark:text-slate-200">Color distintivo</label>
                        <div class="mt-1 flex items-center gap-2">
                            <input
                                type="color"
                                wire:model="quickCategoryColor"
                                class="h-8 w-12 cursor-pointer rounded-lg border border-slate-200 bg-transparent p-0.5"
                            />
                            <span class="text-xs font-mono text-slate-500">{{ $quickCategoryColor }}</span>
                        </div>
                        @error('quickCategoryColor') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            wire:click="closeQuickCategoryModal"
                            class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-[#64748B] hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="rounded-xl bg-[#10B981] px-4 py-1.5 text-xs font-semibold text-white shadow-2xs hover:bg-[#064E3B]"
                        >
                            Crear y Seleccionar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

