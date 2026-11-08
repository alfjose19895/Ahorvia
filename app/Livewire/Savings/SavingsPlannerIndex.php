<?php

declare(strict_types=1);

namespace App\Livewire\Savings;

use App\Actions\Savings\CreateSavingsGoalAction;
use App\Actions\Savings\RecordSavingsContributionAction;
use App\Data\Savings\SavingsCapacityData;
use App\Data\Savings\SavingsGoalPlanData;
use App\Enums\AccountType;
use App\Enums\SavingsContributionType;
use App\Enums\SavingsGoalStatus;
use App\Enums\SavingsMode;
use App\Models\Account;
use App\Models\SavingsGoal;
use App\Models\User;
use App\Services\Savings\SavingsCapacityService;
use App\Services\Savings\SavingsPlannerService;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Planificador de Ahorro — Ahorvia')]
class SavingsPlannerIndex extends Component
{
    public bool $showCreateModal = false;

    public bool $showContributionModal = false;

    // Formulario de creación de meta
    public string $name = '';

    public string $targetAmount = '';

    public string $initialAmount = '0.00';

    public int $durationMonths = 6;

    public string $mode = 'adjustable'; // 'fixed', 'adjustable'

    public ?int $linkedAccountId = null;

    public ?string $notes = null;

    // Formulario de aporte
    public ?int $selectedGoalId = null;

    public string $contributionType = 'transfer'; // 'transfer', 'reserve'

    public string $contributionAmount = '';

    public ?int $sourceAccountId = null;

    public ?int $destinationAccountId = null;

    public string $contributionDate = '';

    public ?string $reference = null;

    public ?string $contributionNotes = null;

    // Formulario de edición de meta
    public bool $showEditModal = false;

    public ?int $editGoalId = null;

    public string $editName = '';

    public string $editTargetAmount = '';

    public int $editDurationMonths = 6;

    public string $editMode = 'adjustable';

    public ?int $editLinkedAccountId = null;

    public string $editStatus = 'active';

    public ?string $editNotes = null;

    public function mount(): void
    {
        $this->contributionDate = CarbonImmutable::now()->toDateString();
    }

    public function openCreateModal(): void
    {
        $this->name = '';
        $this->targetAmount = '';
        $this->initialAmount = '0.00';
        $this->durationMonths = 6;
        $this->mode = 'adjustable';
        $this->linkedAccountId = null;
        $this->notes = null;
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function saveGoal(CreateSavingsGoalAction $action): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'targetAmount' => ['required', 'regex:/^\d+(\.\d{1,4})?$/', 'not_in:0,0.0,0.00,0.000,0.0000'],
            'initialAmount' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'durationMonths' => ['required', 'integer', 'between:1,60'],
            'mode' => ['required', Rule::in(['fixed', 'adjustable'])],
            'linkedAccountId' => ['nullable', 'integer', Rule::exists('accounts', 'id')->where('user_id', $user->id)->where('is_active', true)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $action->execute($user, [
            'name' => $this->name,
            'target_amount' => $this->targetAmount,
            'initial_amount' => $this->initialAmount !== '' ? $this->initialAmount : '0.0000',
            'duration_months' => $this->durationMonths,
            'mode' => SavingsMode::from($this->mode),
            'linked_account_id' => $this->linkedAccountId,
            'notes' => $this->notes,
        ]);

        $this->showCreateModal = false;
        session()->flash('status', 'Meta de ahorro a 6 meses programada con éxito.');
    }

    public function openContributionModal(int $goalId): void
    {
        $this->selectedGoalId = $goalId;
        $this->contributionType = 'transfer';
        $this->contributionAmount = '';
        $this->sourceAccountId = null;
        $this->destinationAccountId = null;
        $this->contributionDate = CarbonImmutable::now()->toDateString();
        $this->reference = null;
        $this->contributionNotes = null;

        /** @var User $user */
        $user = Auth::user();

        /** @var SavingsGoal|null $goal */
        $goal = SavingsGoal::query()->ownedBy($user)->find($goalId);
        if ($goal?->linked_account_id !== null) {
            $this->destinationAccountId = (int) $goal->linked_account_id;
        }

        $this->resetValidation();
        $this->showContributionModal = true;
    }

    public function closeContributionModal(): void
    {
        $this->showContributionModal = false;
        $this->resetValidation();
    }

    public function saveContribution(RecordSavingsContributionAction $action): void
    {
        /** @var User $user */
        $user = Auth::user();

        $rules = [
            'selectedGoalId' => ['required', 'integer', Rule::exists('savings_goals', 'id')->where('user_id', $user->id)],
            'contributionType' => ['required', Rule::in(['transfer', 'reserve'])],
            'contributionAmount' => ['required', 'regex:/^\d+(\.\d{1,4})?$/', 'not_in:0,0.0,0.00,0.000,0.0000'],
            'contributionDate' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'contributionNotes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($this->contributionType === 'transfer') {
            $rules['sourceAccountId'] = ['required', 'integer', Rule::exists('accounts', 'id')->where('user_id', $user->id)->where('is_active', true)];
            $rules['destinationAccountId'] = ['required', 'integer', 'different:sourceAccountId', Rule::exists('accounts', 'id')->where('user_id', $user->id)->where('is_active', true)];
        } else {
            $rules['sourceAccountId'] = ['required', 'integer', Rule::exists('accounts', 'id')->where('user_id', $user->id)->where('is_active', true)];
        }

        $this->validate($rules);

        $payload = [
            'savings_goal_id' => (int) $this->selectedGoalId,
            'contribution_type' => $this->contributionType === 'transfer' ? SavingsContributionType::Transfer : SavingsContributionType::Reserve,
            'amount' => $this->contributionAmount,
            'contribution_date' => $this->contributionDate,
            'reference' => $this->reference,
            'notes' => $this->contributionNotes,
        ];

        if ($this->contributionType === 'transfer') {
            $payload['source_account_id'] = (int) $this->sourceAccountId;
            $payload['destination_account_id'] = (int) $this->destinationAccountId;
        } else {
            $payload['account_id'] = (int) $this->sourceAccountId;
        }

        $action->execute($user, $payload);

        $this->showContributionModal = false;
        session()->flash('status', 'Aporte registrado con respaldo financiero contable.');
    }

    public function openEditModal(int $goalId): void
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var SavingsGoal $goal */
        $goal = SavingsGoal::query()->ownedBy($user)->findOrFail($goalId);

        $this->editGoalId = $goal->id;
        $this->editName = $goal->name;
        $this->editTargetAmount = DecimalMath::formatDecimal((string) $goal->target_amount);
        $this->editDurationMonths = $goal->duration_months;
        $this->editMode = $goal->mode->value;
        $this->editLinkedAccountId = $goal->linked_account_id ? (int) $goal->linked_account_id : null;
        $this->editStatus = $goal->status->value;
        $this->editNotes = $goal->notes;

        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editGoalId = null;
        $this->resetValidation();
    }

    public function updateGoal(): void
    {
        if ($this->editGoalId === null) {
            return;
        }

        /** @var User $user */
        $user = Auth::user();
        /** @var SavingsGoal $goal */
        $goal = SavingsGoal::query()->ownedBy($user)->findOrFail($this->editGoalId);

        $validated = $this->validate([
            'editName' => ['required', 'string', 'max:255'],
            'editTargetAmount' => ['required', 'regex:/^\d+(\.\d{1,4})?$/', 'not_in:0,0.0,0.00,0.000,0.0000'],
            'editDurationMonths' => ['required', 'integer', 'between:1,60'],
            'editMode' => ['required', Rule::in(['fixed', 'adjustable'])],
            'editLinkedAccountId' => ['nullable', 'integer', Rule::exists('accounts', 'id')->where('user_id', $user->id)->where('is_active', true)],
            'editStatus' => ['required', Rule::in(['active', 'completed', 'cancelled', 'paused'])],
            'editNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $status = SavingsGoalStatus::from($validated['editStatus']);

        $goal->update([
            'name' => trim($validated['editName']),
            'target_amount' => DecimalMath::normalize($validated['editTargetAmount']),
            'duration_months' => (int) $validated['editDurationMonths'],
            'mode' => SavingsMode::from($validated['editMode']),
            'linked_account_id' => $validated['editLinkedAccountId'] ?? null,
            'status' => $status,
            'notes' => isset($validated['editNotes']) && trim((string) $validated['editNotes']) !== '' ? trim((string) $validated['editNotes']) : null,
            'completed_at' => $status === SavingsGoalStatus::Completed && $goal->completed_at === null ? CarbonImmutable::now() : ($status !== SavingsGoalStatus::Completed ? null : $goal->completed_at),
        ]);

        $this->closeEditModal();
        session()->flash('status', 'Meta de ahorro actualizada con éxito.');
    }

    public function deleteGoal(int $goalId): void
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var SavingsGoal $goal */
        $goal = SavingsGoal::query()->ownedBy($user)->findOrFail($goalId);

        if ($goal->contributions()->count() > 0) {
            session()->flash('error', 'No se puede eliminar una meta de ahorro que tiene aportes contables registrados. Puedes marcarla como cancelada en Editar.');

            return;
        }

        $goal->delete();

        if ($this->editGoalId === $goalId) {
            $this->closeEditModal();
        }

        session()->flash('status', 'Meta de ahorro eliminada con éxito.');
    }

    public function render(
        SavingsPlannerService $plannerService,
        SavingsCapacityService $capacityService,
    ): View {
        /** @var User $user */
        $user = Auth::user();

        $goals = SavingsGoal::query()
            ->ownedBy($user)
            ->whereIn('status', [SavingsGoalStatus::Active, SavingsGoalStatus::Completed])
            ->with(['linkedAccount'])
            ->orderBy('target_date', 'asc')
            ->get();

        /** @var list<SavingsGoalPlanData> $evaluatedGoals */
        $evaluatedGoals = [];
        $totalTarget = DecimalMath::ZERO;
        $totalRecognized = DecimalMath::ZERO;
        $totalPending = DecimalMath::ZERO;

        foreach ($goals as $goal) {
            $data = $plannerService->analyzeGoal($goal);
            $evaluatedGoals[] = $data;

            $totalTarget = DecimalMath::add($totalTarget, $data->targetAmount);
            $totalRecognized = DecimalMath::add($totalRecognized, $data->recognizedSavings);
            $totalPending = DecimalMath::add($totalPending, $data->pendingSavings);
        }

        /** @var SavingsCapacityData $capacity */
        $capacity = $capacityService->evaluateForMonth($user);

        $liquidAccounts = Account::query()
            ->ownedBy($user)
            ->whereIn('type', [AccountType::Cash, AccountType::Checking, AccountType::Savings])
            ->active()
            ->orderBy('name')
            ->get();

        $selectedGoal = $this->selectedGoalId !== null
            ? $goals->firstWhere('id', $this->selectedGoalId)
            : null;

        return view('livewire.savings.savings-planner-index', [
            'evaluatedGoals' => $evaluatedGoals,
            'capacity' => $capacity,
            'liquidAccounts' => $liquidAccounts,
            'selectedGoal' => $selectedGoal,
            'totalTarget' => $totalTarget,
            'totalRecognized' => $totalRecognized,
            'totalPending' => $totalPending,
            'currencyCode' => $user->currency_code ?: 'USD',
        ]);
    }
}
