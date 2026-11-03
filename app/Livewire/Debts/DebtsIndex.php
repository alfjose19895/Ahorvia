<?php

declare(strict_types=1);

namespace App\Livewire\Debts;

use App\Actions\Debts\CreateDebtAction;
use App\Actions\Debts\RecordDebtPaymentAction;
use App\Enums\AccountType;
use App\Enums\DebtType;
use App\Models\Account;
use App\Models\Debt;
use App\Models\User;
use App\Services\Debt\DebtBalanceService;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Deudas y Préstamos — Ahorvia')]
class DebtsIndex extends Component
{
    public bool $showCreateModal = false;

    public bool $showPaymentModal = false;

    // Campos de creación de deuda
    public string $name = '';

    public string $debtType = 'personal_loan';

    public ?string $creditorName = null;

    public string $originalPrincipal = '';

    public ?string $interestRateAnnual = null;

    public ?string $minimumPayment = null;

    public ?string $dueDate = null;

    public ?int $paymentDueDay = null;

    public ?int $disbursementAccountId = null;

    public ?string $notes = null;

    // Campos de abono a deuda
    public ?int $selectedDebtId = null;

    public ?int $sourceAccountId = null;

    public string $paymentDate = '';

    public string $principalAmount = '';

    public string $interestAmount = '0.00';

    public string $feeAmount = '0.00';

    public ?string $reference = null;

    public ?string $paymentNotes = null;

    public function mount(): void
    {
        $this->paymentDate = CarbonImmutable::now()->toDateString();
    }

    public function openCreateModal(): void
    {
        $this->name = '';
        $this->debtType = 'personal_loan';
        $this->creditorName = null;
        $this->originalPrincipal = '';
        $this->interestRateAnnual = null;
        $this->minimumPayment = null;
        $this->dueDate = null;
        $this->paymentDueDay = null;
        $this->disbursementAccountId = null;
        $this->notes = null;
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function saveDebt(CreateDebtAction $action): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'debtType' => ['required', Rule::in(['personal_loan', 'bank_loan', 'mortgage', 'informal_debt'])],
            'creditorName' => ['nullable', 'string', 'max:255'],
            'originalPrincipal' => ['required', 'regex:/^\d+(\.\d{1,4})?$/', 'not_in:0,0.0,0.00,0.000,0.0000'],
            'interestRateAnnual' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'minimumPayment' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'dueDate' => ['nullable', 'date'],
            'paymentDueDay' => ['nullable', 'integer', 'between:1,31'],
            'disbursementAccountId' => ['nullable', 'integer', Rule::exists('accounts', 'id')->where('user_id', $user->id)->where('is_active', true)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $action->execute($user, [
            'name' => $this->name,
            'type' => DebtType::from($this->debtType),
            'creditor_name' => $this->creditorName,
            'original_principal' => $this->originalPrincipal,
            'interest_rate_annual' => $this->interestRateAnnual,
            'minimum_payment' => $this->minimumPayment,
            'due_date' => $this->dueDate,
            'payment_due_day' => $this->paymentDueDay,
            'disbursement_account_id' => $this->disbursementAccountId,
            'notes' => $this->notes,
        ]);

        $this->showCreateModal = false;
        session()->flash('status', 'Deuda registrada exitosamente con su cuenta contable de pasivo.');
    }

    public function openPaymentModal(int $debtId): void
    {
        $this->selectedDebtId = $debtId;
        $this->sourceAccountId = null;
        $this->paymentDate = CarbonImmutable::now()->toDateString();
        $this->principalAmount = '';
        $this->interestAmount = '0.00';
        $this->feeAmount = '0.00';
        $this->reference = null;
        $this->paymentNotes = null;
        $this->resetValidation();
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->resetValidation();
    }

    public function savePayment(RecordDebtPaymentAction $action): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->validate([
            'selectedDebtId' => ['required', 'integer', Rule::exists('debts', 'id')->where('user_id', $user->id)],
            'sourceAccountId' => ['required', 'integer', Rule::exists('accounts', 'id')->where('user_id', $user->id)->where('is_active', true)],
            'paymentDate' => ['required', 'date'],
            'principalAmount' => ['required', 'regex:/^\d+(\.\d{1,4})?$/'],
            'interestAmount' => ['required', 'regex:/^\d+(\.\d{1,4})?$/'],
            'feeAmount' => ['required', 'regex:/^\d+(\.\d{1,4})?$/'],
            'reference' => ['nullable', 'string', 'max:100'],
            'paymentNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $p = DecimalMath::normalize($this->principalAmount !== '' ? $this->principalAmount : '0.0000');
        $i = DecimalMath::normalize($this->interestAmount !== '' ? $this->interestAmount : '0.0000');
        $f = DecimalMath::normalize($this->feeAmount !== '' ? $this->feeAmount : '0.0000');
        $total = DecimalMath::add(DecimalMath::add($p, $i), $f);

        if (DecimalMath::compare($total, DecimalMath::ZERO) <= 0) {
            $this->addError('principalAmount', 'El importe total del abono (capital + interés + comisiones) debe ser mayor a cero.');

            return;
        }

        $action->execute($user, [
            'debt_id' => (int) $this->selectedDebtId,
            'source_account_id' => (int) $this->sourceAccountId,
            'payment_date' => $this->paymentDate,
            'principal_amount' => $p,
            'interest_amount' => $i,
            'fee_amount' => $f,
            'total_amount' => $total,
            'reference' => $this->reference,
            'notes' => $this->paymentNotes,
        ]);

        $this->showPaymentModal = false;
        session()->flash('status', 'Abono registrado contablemente: capital reducido e intereses asentados.');
    }

    public function render(DebtBalanceService $debtBalanceService): View
    {
        /** @var User $user */
        $user = Auth::user();

        $debts = Debt::query()
            ->ownedBy($user)
            ->where('type', '!=', DebtType::CreditCard)
            ->with(['liabilityAccount'])
            ->orderBy('created_at', 'desc')
            ->get();

        $debtsData = [];
        $totalPendingPrincipal = DecimalMath::ZERO;
        $totalPrincipalPaid = DecimalMath::ZERO;
        $totalInterestFeesPaid = DecimalMath::ZERO;

        foreach ($debts as $debt) {
            $pending = $debtBalanceService->pendingPrincipal($debt);
            $breakdown = $debtBalanceService->paymentBreakdownTotals($debt);

            $totalPendingPrincipal = DecimalMath::add($totalPendingPrincipal, $pending);
            $totalPrincipalPaid = DecimalMath::add($totalPrincipalPaid, $breakdown['principal_paid']);
            $interestAndFees = DecimalMath::add($breakdown['interest_paid'], $breakdown['fees_paid']);
            $totalInterestFeesPaid = DecimalMath::add($totalInterestFeesPaid, $interestAndFees);

            $debtsData[] = [
                'debt' => $debt,
                'pending_principal' => $pending,
                'breakdown' => $breakdown,
            ];
        }

        $liquidAccounts = Account::query()
            ->ownedBy($user)
            ->whereIn('type', [AccountType::Cash, AccountType::Checking, AccountType::Savings])
            ->active()
            ->orderBy('name')
            ->get();

        $selectedDebt = $this->selectedDebtId !== null
            ? $debts->firstWhere('id', $this->selectedDebtId)
            : null;

        return view('livewire.debts.debts-index', [
            'debtsData' => $debtsData,
            'liquidAccounts' => $liquidAccounts,
            'selectedDebt' => $selectedDebt,
            'totalPendingPrincipal' => $totalPendingPrincipal,
            'totalPrincipalPaid' => $totalPrincipalPaid,
            'totalInterestFeesPaid' => $totalInterestFeesPaid,
            'currencyCode' => $user->currency_code ?: 'USD',
        ]);
    }
}
