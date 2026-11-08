<?php

declare(strict_types=1);

namespace App\Livewire\Accounts;

use App\Actions\Accounts\CreateAccountAction;
use App\Actions\Transactions\RecordTransferAction;
use App\Actions\Transactions\ReverseJournalEntryAction;
use App\Enums\AccountType;
use App\Enums\EntryStatus;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\Finance\AccountBalanceService;
use App\Support\DecimalMath;
use App\Support\MoneyFormatter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cuentas Financieras — Ahorvia')]
class AccountsIndex extends Component
{
    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showTransferModal = false;

    // Campos formulario creación
    public string $name = '';

    public string $type = 'checking';

    public string $currency_code = 'USD';

    public ?string $institution_name = null;

    public ?string $account_number_last4 = null;

    public string $opening_balance = '0.0000';

    public ?string $credit_limit = null;

    public ?int $billing_cutoff_day = null;

    public ?int $payment_due_day = null;

    public string $interest_rate_annual = '0.0000';

    public bool $include_in_net_worth = true;

    public ?string $notes = null;

    // Campos edición
    public ?int $editingAccountId = null;

    public string $editName = '';

    public ?string $editInstitutionName = null;

    public ?string $editAccountNumberLast4 = null;

    public ?string $editCreditLimit = null;

    public ?int $editBillingCutoffDay = null;

    public ?int $editPaymentDueDay = null;

    public string $editInterestRateAnnual = '0.0000';

    public bool $editIncludeInNetWorth = true;

    public bool $editIsActive = true;

    public ?string $editNotes = null;

    // Campos transferencia rápida
    public ?int $transferSourceId = null;

    public ?int $transferDestinationId = null;

    public string $transferAmount = '';

    public string $transferDescription = 'Transferencia entre cuentas';

    public ?string $transferReference = null;

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $this->currency_code = strtoupper($user->currency_code ?: 'USD');
    }

    public function openCreateModal(): void
    {
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    public function saveAccount(CreateAccountAction $createAccountAction): void
    {
        /** @var User $user */
        $user = Auth::user();

        $userCaseValues = array_map(static fn (AccountType $case): string => $case->value, AccountType::userCases());

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', Rule::in($userCaseValues)],
            'institution_name' => ['nullable', 'string', 'max:100'],
            'account_number_last4' => ['nullable', 'string', 'digits:4'],
            'opening_balance' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'credit_limit' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'interest_rate_annual' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'billing_cutoff_day' => ['nullable', 'integer', 'between:1,31'],
            'payment_due_day' => ['nullable', 'integer', 'between:1,31'],
            'include_in_net_worth' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $createAccountAction->execute($user, [
            'name' => $validated['name'],
            'type' => AccountType::from($validated['type']),
            'currency_code' => $this->currency_code,
            'institution_name' => $validated['institution_name'] ?? null,
            'account_number_last4' => $validated['account_number_last4'] ?? null,
            'opening_balance' => $validated['opening_balance'] ?? '0.0000',
            'credit_limit' => $validated['credit_limit'] ?? null,
            'interest_rate_annual' => $validated['interest_rate_annual'] ?? '0.0000',
            'billing_cutoff_day' => $validated['billing_cutoff_day'] ?? null,
            'payment_due_day' => $validated['payment_due_day'] ?? null,
            'include_in_net_worth' => (bool) ($validated['include_in_net_worth'] ?? true),
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->closeCreateModal();
        session()->flash('status', '¡Cuenta financiera creada exitosamente!');
    }

    public function openEditModal(int $accountId): void
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Account $account */
        $account = Account::query()->ownedBy($user)->findOrFail($accountId);

        $this->editingAccountId = $accountId;
        $this->editName = $account->name;
        $this->editInstitutionName = $account->institution_name;
        $this->editAccountNumberLast4 = $account->account_number_last4;
        $this->editCreditLimit = $account->credit_limit !== null ? (string) $account->credit_limit : null;
        $this->editBillingCutoffDay = $account->billing_cutoff_day;
        $this->editPaymentDueDay = $account->payment_due_day;
        $this->editInterestRateAnnual = (string) $account->interest_rate_annual;
        $this->editIncludeInNetWorth = (bool) $account->include_in_net_worth;
        $this->editIsActive = (bool) $account->is_active;
        $this->editNotes = $account->notes;

        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingAccountId = null;
    }

    public function updateAccount(): void
    {
        if ($this->editingAccountId === null) {
            return;
        }

        /** @var User $user */
        $user = Auth::user();
        /** @var Account $account */
        $account = Account::query()->ownedBy($user)->findOrFail($this->editingAccountId);

        $validated = $this->validate([
            'editName' => ['required', 'string', 'max:100'],
            'editInstitutionName' => ['nullable', 'string', 'max:100'],
            'editAccountNumberLast4' => ['nullable', 'string', 'digits:4'],
            'editCreditLimit' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'editInterestRateAnnual' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'editBillingCutoffDay' => ['nullable', 'integer', 'between:1,31'],
            'editPaymentDueDay' => ['nullable', 'integer', 'between:1,31'],
            'editIncludeInNetWorth' => ['boolean'],
            'editIsActive' => ['boolean'],
            'editNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $account->update([
            'name' => trim($validated['editName']),
            'institution_name' => isset($validated['editInstitutionName']) ? trim((string) $validated['editInstitutionName']) : null,
            'account_number_last4' => isset($validated['editAccountNumberLast4']) ? trim((string) $validated['editAccountNumberLast4']) : null,
            'credit_limit' => isset($validated['editCreditLimit']) && $validated['editCreditLimit'] !== '' ? DecimalMath::normalize($validated['editCreditLimit']) : null,
            'interest_rate_annual' => isset($validated['editInterestRateAnnual']) && $validated['editInterestRateAnnual'] !== '' ? DecimalMath::normalize($validated['editInterestRateAnnual']) : '0.0000',
            'billing_cutoff_day' => $validated['editBillingCutoffDay'] ?? null,
            'payment_due_day' => $validated['editPaymentDueDay'] ?? null,
            'include_in_net_worth' => (bool) $validated['editIncludeInNetWorth'],
            'is_active' => (bool) $validated['editIsActive'],
            'notes' => isset($validated['editNotes']) ? trim((string) $validated['editNotes']) : null,
        ]);

        $this->closeEditModal();
        session()->flash('status', '¡Cuenta actualizada con éxito!');
    }

    public function toggleStatus(int $accountId): void
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Account $account */
        $account = Account::query()->ownedBy($user)->findOrFail($accountId);

        $account->update(['is_active' => ! $account->is_active]);

        $msg = $account->is_active ? 'Cuenta activada correctamente.' : 'Cuenta desactivada.';
        session()->flash('status', $msg);
    }

    public function deleteAccount(int $accountId, ReverseJournalEntryAction $reverseAction): void
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Account $account */
        $account = Account::query()->ownedBy($user)->findOrFail($accountId);

        // Contar asientos que involucran esta cuenta
        $entries = JournalEntry::query()
            ->ownedBy($user)
            ->where('status', EntryStatus::Posted)
            ->whereHas('lines', static fn ($q) => $q->where('account_id', $account->id))
            ->get();

        // Si tiene más asientos aparte del saldo inicial, o asientos que no son de apertura
        $nonOpeningEntries = $entries->filter(static fn (JournalEntry $e) => ! str_starts_with((string) $e->reference, 'OPENING-'));

        if ($nonOpeningEntries->isNotEmpty()) {
            session()->flash('error', 'No se puede eliminar una cuenta que tiene transacciones registradas. Puedes desactivarla para ocultarla.');

            return;
        }

        DB::transaction(function () use ($user, $account, $entries, $reverseAction): void {
            foreach ($entries as $entry) {
                $reverseAction->execute($user, $entry, "Anulación de cuenta [{$account->name}] creada por error.");
            }

            $account->delete();
        });

        if ($this->editingAccountId === $accountId) {
            $this->closeEditModal();
        }

        session()->flash('status', 'Cuenta eliminada con éxito y asientos de apertura revertidos.');
    }

    public function openTransferModal(?int $sourceAccountId = null): void
    {
        $this->transferSourceId = $sourceAccountId;
        $this->transferDestinationId = null;
        $this->transferAmount = '';
        $this->transferDescription = 'Transferencia entre cuentas';
        $this->transferReference = null;
        $this->showTransferModal = true;
    }

    public function closeTransferModal(): void
    {
        $this->showTransferModal = false;
        $this->transferSourceId = null;
        $this->transferDestinationId = null;
        $this->transferAmount = '';
    }

    public function executeTransfer(RecordTransferAction $recordTransferAction): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->validate([
            'transferSourceId' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where('user_id', $user->getKey())->where('is_active', true),
            ],
            'transferDestinationId' => [
                'required',
                'integer',
                'different:transferSourceId',
                Rule::exists('accounts', 'id')->where('user_id', $user->getKey())->where('is_active', true),
            ],
            'transferAmount' => ['required', 'regex:/^\d+(\.\d{1,4})?$/', 'not_in:0,0.0,0.00,0.000,0.0000'],
            'transferDescription' => ['required', 'string', 'max:255'],
            'transferReference' => ['nullable', 'string', 'max:100'],
        ]);

        $recordTransferAction->execute($user, [
            'source_account_id' => (int) $this->transferSourceId,
            'destination_account_id' => (int) $this->transferDestinationId,
            'amount' => $this->transferAmount,
            'description' => $this->transferDescription,
            'reference' => $this->transferReference,
        ]);

        $this->closeTransferModal();
        session()->flash('status', '¡Transferencia registrada y balanceada por partida doble!');
    }

    public function render(AccountBalanceService $balanceService): View
    {
        /** @var User $user */
        $user = Auth::user();
        $currency = strtoupper($user->currency_code ?: 'USD');

        $accounts = Account::query()
            ->ownedBy($user)
            ->userFacing()
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        $totalAssets = DecimalMath::ZERO;
        $totalLiabilities = DecimalMath::ZERO;
        $availableLiquidity = DecimalMath::ZERO;

        $accountsData = [];

        foreach ($accounts as $account) {
            $balance = $balanceService->balanceForAccount($account);
            $available = $balanceService->availableForAccount($account);
            $reserved = $balanceService->reservedAmountForAccount($account);

            if ($account->is_active && $account->include_in_net_worth) {
                if ($account->type->isAsset()) {
                    $totalAssets = DecimalMath::add($totalAssets, $balance);
                } else {
                    $totalLiabilities = DecimalMath::add($totalLiabilities, $balance);
                }
            }

            if ($account->is_active && $account->type->isAvailableLiquidity()) {
                $availableLiquidity = DecimalMath::add($availableLiquidity, $available);
            }

            $accountsData[] = [
                'model' => $account,
                'balance' => $balance,
                'available' => $available,
                'reserved' => $reserved,
                'formatted_balance' => MoneyFormatter::format($balance, $account->currency_code),
                'formatted_available' => MoneyFormatter::format($available, $account->currency_code),
                'formatted_reserved' => MoneyFormatter::format($reserved, $account->currency_code),
            ];
        }

        $activeTransferAccounts = $accounts->where('is_active', true)->values();

        return view('livewire.accounts.accounts-index', [
            'accountsData' => $accountsData,
            'activeTransferAccounts' => $activeTransferAccounts,
            'currency' => $currency,
            'totalAssetsFormatted' => MoneyFormatter::format($totalAssets, $currency),
            'totalLiabilitiesFormatted' => MoneyFormatter::format($totalLiabilities, $currency),
            'netWorthFormatted' => MoneyFormatter::format(DecimalMath::sub($totalAssets, $totalLiabilities), $currency),
            'availableLiquidityFormatted' => MoneyFormatter::format($availableLiquidity, $currency),
            'userCaseTypes' => AccountType::userCases(),
        ]);
    }

    private function resetCreateForm(): void
    {
        $this->name = '';
        $this->type = 'checking';
        $this->institution_name = null;
        $this->account_number_last4 = null;
        $this->opening_balance = '0.0000';
        $this->credit_limit = null;
        $this->billing_cutoff_day = null;
        $this->payment_due_day = null;
        $this->interest_rate_annual = '0.0000';
        $this->include_in_net_worth = true;
        $this->notes = null;
        $this->resetValidation();
    }
}
