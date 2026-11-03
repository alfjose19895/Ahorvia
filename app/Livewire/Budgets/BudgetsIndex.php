<?php

declare(strict_types=1);

namespace App\Livewire\Budgets;

use App\Actions\Budgets\UpsertMonthlyBudgetAction;
use App\Data\Budget\BudgetExecutionData;
use App\Enums\CategoryType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use App\Services\Budget\BudgetMonitorService;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presupuestos — Ahorvia')]
class BudgetsIndex extends Component
{
    public int $selectedYear;

    public int $selectedMonth;

    public bool $showManageModal = false;

    /**
     * @var array<int, string> category_id => limit_amount
     */
    public array $limits = [];

    public ?string $budgetNotes = null;

    public function mount(): void
    {
        $now = CarbonImmutable::now();
        $this->selectedYear = $now->year;
        $this->selectedMonth = $now->month;
    }

    public function previousMonth(): void
    {
        $date = CarbonImmutable::create($this->selectedYear, $this->selectedMonth, 1)->subMonth();
        $this->selectedYear = $date->year;
        $this->selectedMonth = $date->month;
    }

    public function nextMonth(): void
    {
        $date = CarbonImmutable::create($this->selectedYear, $this->selectedMonth, 1)->addMonth();
        $this->selectedYear = $date->year;
        $this->selectedMonth = $date->month;
    }

    public function currentMonth(): void
    {
        $now = CarbonImmutable::now();
        $this->selectedYear = $now->year;
        $this->selectedMonth = $now->month;
    }

    public function openManageModal(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $budget = Budget::query()
            ->ownedBy($user)
            ->where('year', $this->selectedYear)
            ->where('month', $this->selectedMonth)
            ->where('is_active', true)
            ->with(['lines'])
            ->first();

        $expenseCategories = Category::query()
            ->ownedBy($user)
            ->where('type', CategoryType::Expense)
            ->active()
            ->orderBy('name')
            ->get();

        $this->limits = [];
        $existingLines = $budget !== null
            ? $budget->lines->keyBy('category_id')
            : collect();

        foreach ($expenseCategories as $category) {
            $existing = $existingLines->get($category->id);
            $this->limits[$category->id] = $existing !== null
                ? (string) $existing->limit_amount
                : '';
        }

        $this->budgetNotes = $budget?->notes;
        $this->resetValidation();
        $this->showManageModal = true;
    }

    public function closeManageModal(): void
    {
        $this->showManageModal = false;
        $this->resetValidation();
    }

    public function saveBudget(UpsertMonthlyBudgetAction $action): void
    {
        /** @var User $user */
        $user = Auth::user();

        $validatedLines = [];

        foreach ($this->limits as $categoryId => $limitValue) {
            $limitStr = trim((string) $limitValue);
            if ($limitStr === '' || ! is_numeric($limitStr)) {
                continue;
            }

            if (DecimalMath::compare($limitStr, DecimalMath::ZERO) > 0) {
                $validatedLines[] = [
                    'category_id' => (int) $categoryId,
                    'limit_amount' => DecimalMath::normalize($limitStr),
                ];
            }
        }

        if ($validatedLines === []) {
            $this->addError('limits', 'Debes ingresar al menos un límite mayor a cero para alguna categoría.');

            return;
        }

        $action->execute($user, [
            'year' => $this->selectedYear,
            'month' => $this->selectedMonth,
            'currency_code' => $user->currency_code ?: 'USD',
            'notes' => $this->budgetNotes,
            'lines' => $validatedLines,
        ]);

        $this->showManageModal = false;
        session()->flash('status', 'Presupuesto mensual actualizado exitosamente.');
    }

    public function render(BudgetMonitorService $monitorService): View
    {
        /** @var User $user */
        $user = Auth::user();

        $budget = Budget::query()
            ->ownedBy($user)
            ->where('year', $this->selectedYear)
            ->where('month', $this->selectedMonth)
            ->where('is_active', true)
            ->with(['lines.category'])
            ->first();

        /** @var BudgetExecutionData|null $execution */
        $execution = $budget !== null ? $monitorService->evaluate($budget) : null;

        /** @var Collection<int, Category> $expenseCategories */
        $expenseCategories = Category::query()
            ->ownedBy($user)
            ->where('type', CategoryType::Expense)
            ->active()
            ->orderBy('name')
            ->get();

        $periodDate = CarbonImmutable::create($this->selectedYear, $this->selectedMonth, 1);
        $monthName = $periodDate->translatedFormat('F Y');

        return view('livewire.budgets.budgets-index', [
            'budget' => $budget,
            'execution' => $execution,
            'expenseCategories' => $expenseCategories,
            'monthName' => ucfirst($monthName),
        ]);
    }
}
