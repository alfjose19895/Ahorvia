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
#[Title('Tarjetas de Crédito — Ahorvia')]
class CardsIndex extends Component
{
    public bool $showCreateModal = false;

    public bool $showPayModal = false;

    // Campos de creación de tarjeta
    public string $name = '';

    public ?string $bankName = null;

    public string $creditLimit = '';

    public string $openingBalance = '0.00';

    public ?string $interestRateAnnual = null;

    public ?int $cutoffDay = null;

    public ?int $paymentDueDay = null;

    public ?string $notes = null;

    // Campos de pago de tarjeta
    public ?int $selectedCardDebtId = null;

    public ?int $sourceAccountId = null;

    public string $paymentDate = '';

    public string $principalAmount = '';

    public string $interestAmount = '0.00';

    public string $feeAmount = '0.00';

    public ?string $reference = null;

    public function mount(): void
    {
        $this->paymentDate = CarbonImmutable::now()->toDateString();
    }

    public function openCreateModal(): void
    {
        $this->name = '';
        $this->bankName = null;
        $this->creditLimit = '';
        $this->openingBalance = '0.00';
        $this->interestRateAnnual = null;
        $this->cutoffDay = null;
        $this->paymentDueDay = null;
        $this->notes = null;
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function saveCard(CreateDebtAction $action): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'bankName' => ['nullable', 'string', 'max:255'],
            'creditLimit' => ['required', 'regex:/^\d+(\.\d{1,4})?$/', 'not_in:0,0.0,0.00,0.000,0.0000'],
            'openingBalance' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'interestRateAnnual' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'cutoffDay' => ['nullable', 'integer', 'between:1,31'],
            'paymentDueDay' => ['nullable', 'integer', 'between:1,31'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $action->execute($user, [
            'name' => $this->name,
            'type' => DebtType::CreditCard,
            'creditor_name' => $this->bankName,
            'credit_limit' => $this->creditLimit,
            'original_principal' => $this->openingBalance !== '' ? $this->openingBalance : '0.0000',
            'interest_rate_annual' => $this->interestRateAnnual,
            'cutoff_day' => $this->cutoffDay,
            'payment_due_day' => $this->paymentDueDay,
            'notes' => $this->notes,
        ]);

        $this->showCreateModal = false;
        session()->flash('status', 'Tarjeta de crédito registrada exitosamente.');
    }

    public function openPayModal(int $cardDebtId): void
    {
        $this->selectedCardDebtId = $cardDebtId;
        $this->sourceAccountId = null;
        $this->paymentDate = CarbonImmutable::now()->toDateString();
        $this->principalAmount = '';
        $this->interestAmount = '0.00';
        $this->feeAmount = '0.00';
        $this->reference = null;
        $this->resetValidation();
        $this->showPayModal = true;
    }

    public function closePayModal(): void
    {
        $this->showPayModal = false;
        $this->resetValidation();
    }

    public function savePayment(RecordDebtPaymentAction $action): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->validate([
            'selectedCardDebtId' => ['required', 'integer', Rule::exists('debts', 'id')->where('user_id', $user->id)],
            'sourceAccountId' => ['required', 'integer', Rule::exists('accounts', 'id')->where('user_id', $user->id)->where('is_active', true)],
            'paymentDate' => ['required', 'date'],
            'principalAmount' => ['required', 'regex:/^\d+(\.\d{1,4})?$/'],
            'interestAmount' => ['required', 'regex:/^\d+(\.\d{1,4})?$/'],
            'feeAmount' => ['required', 'regex:/^\d+(\.\d{1,4})?$/'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        $p = DecimalMath::normalize($this->principalAmount !== '' ? $this->principalAmount : '0.0000');
        $i = DecimalMath::normalize($this->interestAmount !== '' ? $this->interestAmount : '0.0000');
        $f = DecimalMath::normalize($this->feeAmount !== '' ? $this->feeAmount : '0.0000');
        $total = DecimalMath::add(DecimalMath::add($p, $i), $f);

        if (DecimalMath::compare($total, DecimalMath::ZERO) <= 0) {
            $this->addError('principalAmount', 'El importe total a transferir a la tarjeta debe ser mayor a cero.');

            return;
        }

        $action->execute($user, [
            'debt_id' => (int) $this->selectedCardDebtId,
            'source_account_id' => (int) $this->sourceAccountId,
            'payment_date' => $this->paymentDate,
            'principal_amount' => $p,
            'interest_amount' => $i,
            'fee_amount' => $f,
            'total_amount' => $total,
            'reference' => $this->reference,
        ]);

        $this->showPayModal = false;
        session()->flash('status', 'Pago a tarjeta asentado contablemente sin duplicar gastos.');
    }

    public function render(DebtBalanceService $debtBalanceService): View
    {
        /** @var User $user */
        $user = Auth::user();

        $cards = Debt::query()
            ->ownedBy($user)
            ->where('type', DebtType::CreditCard)
            ->with(['liabilityAccount'])
            ->orderBy('created_at', 'desc')
            ->get();

        $cardsData = [];
        $totalCreditLimit = DecimalMath::ZERO;
        $totalUsedBalance = DecimalMath::ZERO;
        $totalAvailableCredit = DecimalMath::ZERO;

        foreach ($cards as $card) {
            $used = $debtBalanceService->pendingPrincipal($card);
            $available = $debtBalanceService->availableCredit($card);
            $limit = DecimalMath::normalize((string) ($card->credit_limit ?? '0.0000'));

            $totalCreditLimit = DecimalMath::add($totalCreditLimit, $limit);
            $totalUsedBalance = DecimalMath::add($totalUsedBalance, $used);
            $totalAvailableCredit = DecimalMath::add($totalAvailableCredit, $available);

            $usagePercent = DecimalMath::compare($limit, DecimalMath::ZERO) > 0
                ? (int) min(100, round((float) DecimalMath::mul(DecimalMath::div($used, $limit), '100')))
                : 0;

            $cardsData[] = [
                'card' => $card,
                'used_balance' => $used,
                'available_credit' => $available,
                'limit' => $limit,
                'usage_percent' => $usagePercent,
            ];
        }

        $liquidAccounts = Account::query()
            ->ownedBy($user)
            ->whereIn('type', [AccountType::Cash, AccountType::Checking, AccountType::Savings])
            ->active()
            ->orderBy('name')
            ->get();

        $selectedCard = $this->selectedCardDebtId !== null
            ? $cards->firstWhere('id', $this->selectedCardDebtId)
            : null;

        return view('livewire.debts.cards-index', [
            'cardsData' => $cardsData,
            'liquidAccounts' => $liquidAccounts,
            'selectedCard' => $selectedCard,
            'totalCreditLimit' => $totalCreditLimit,
            'totalUsedBalance' => $totalUsedBalance,
            'totalAvailableCredit' => $totalAvailableCredit,
            'currencyCode' => $user->currency_code ?: 'USD',
        ]);
    }
}
