<?php

declare(strict_types=1);

namespace App\Livewire\Debts;

use App\Actions\Debts\CreateDebtAction;
use App\Actions\Debts\RecordDebtPaymentAction;
use App\Actions\Transactions\ReverseJournalEntryAction;
use App\Data\Finance\JournalLineInputData;
use App\Data\Finance\PostJournalEntryData;
use App\Enums\AccountType;
use App\Enums\DebtStatus;
use App\Enums\DebtType;
use App\Enums\EntryStatus;
use App\Enums\MovementType;
use App\Models\Account;
use App\Models\Debt;
use App\Models\FinancialInstitution;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\Debt\DebtBalanceService;
use App\Services\Finance\JournalPosterService;
use App\Services\Finance\LedgerBootstrapService;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Deudas y Préstamos — Ahorvia')]
class DebtsIndex extends Component
{
    public bool $showCreateModal = false;

    public bool $showEditModal = false;

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

    // Campos de edición de deuda
    public ?int $editingDebtId = null;

    public string $editName = '';

    public string $editOriginalPrincipal = '';

    public string $editDebtType = 'personal_loan';

    public ?string $editCreditorName = null;

    public ?string $editInterestRateAnnual = null;

    public ?string $editMinimumPayment = null;

    public ?string $editDueDate = null;

    public ?int $editPaymentDueDay = null;

    public ?string $editNotes = null;

    public string $editStatus = 'active';

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

        if (! empty($this->creditorName)) {
            FinancialInstitution::query()->firstOrCreate(
                ['user_id' => $user->id, 'name' => trim((string) $this->creditorName)],
                ['is_active' => true]
            );
        }

        $this->showCreateModal = false;
        session()->flash('status', 'Deuda / crédito registrada exitosamente con su cuenta contable de pasivo.');
    }

    public function openEditModal(int $debtId): void
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Debt $debt */
        $debt = Debt::query()->ownedBy($user)->findOrFail($debtId);

        $this->editingDebtId = $debt->id;
        $this->editName = $debt->name;
        $this->editOriginalPrincipal = DecimalMath::formatDecimal((string) $debt->original_principal);
        $this->editDebtType = $debt->type->value;
        $this->editCreditorName = $debt->creditor_name;
        $this->editInterestRateAnnual = $debt->interest_rate_annual !== null ? DecimalMath::formatDecimal((string) $debt->interest_rate_annual) : null;
        $this->editMinimumPayment = $debt->minimum_payment !== null ? DecimalMath::formatDecimal((string) $debt->minimum_payment) : null;
        $this->editDueDate = $debt->due_date ? CarbonImmutable::parse($debt->due_date)->toDateString() : null;
        $this->editPaymentDueDay = $debt->payment_due_day;
        $this->editNotes = $debt->notes;
        $this->editStatus = $debt->status->value;

        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingDebtId = null;
        $this->resetValidation();
    }

    public function updateDebt(
        ReverseJournalEntryAction $reverseAction,
        JournalPosterService $journalPoster,
        LedgerBootstrapService $ledgerBootstrap,
    ): void {
        if ($this->editingDebtId === null) {
            return;
        }

        /** @var User $user */
        $user = Auth::user();
        /** @var Debt $debt */
        $debt = Debt::query()->ownedBy($user)->with(['liabilityAccount'])->findOrFail($this->editingDebtId);

        $validated = $this->validate([
            'editName' => ['required', 'string', 'max:255'],
            'editOriginalPrincipal' => ['required', 'regex:/^\d+(\.\d{1,4})?$/', 'not_in:0,0.0,0.00,0.000,0.0000'],
            'editDebtType' => ['required', Rule::in(['personal_loan', 'bank_loan', 'mortgage', 'informal_debt'])],
            'editCreditorName' => ['nullable', 'string', 'max:255'],
            'editInterestRateAnnual' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'editMinimumPayment' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'editDueDate' => ['nullable', 'date'],
            'editPaymentDueDay' => ['nullable', 'integer', 'between:1,31'],
            'editNotes' => ['nullable', 'string', 'max:1000'],
            'editStatus' => ['required', Rule::in(['active', 'paid_off', 'cancelled'])],
        ]);

        $newPrincipal = DecimalMath::normalize($validated['editOriginalPrincipal']);
        $oldPrincipal = DecimalMath::normalize((string) $debt->original_principal);

        if (! DecimalMath::equals($newPrincipal, $oldPrincipal)) {
            if ($debt->payments()->count() > 0) {
                $this->addError('editOriginalPrincipal', 'No se puede modificar el monto original porque este crédito ya tiene abonos registrados. Reversa primero los abonos en Movimientos para corregir el monto inicial.');

                return;
            }

            DB::transaction(function () use ($user, $debt, $newPrincipal, $reverseAction, $journalPoster, $ledgerBootstrap): void {
                $initialEntry = JournalEntry::query()
                    ->ownedBy($user)
                    ->where('status', EntryStatus::Posted)
                    ->where(function ($query) use ($debt): void {
                        $query->where('reference', "DEBT-{$debt->id}")
                            ->orWhereJsonContains('metadata->debt_id', $debt->id);
                    })
                    ->first();

                $disbursementAccountId = null;
                $entryDate = $debt->start_date ? CarbonImmutable::parse($debt->start_date) : CarbonImmutable::now();

                if ($initialEntry !== null) {
                    $reverseAction->execute($user, $initialEntry, "Ajuste de monto principal en crédito [{$debt->name}] de {$debt->original_principal} a {$newPrincipal}.");

                    if ($initialEntry->movement_type === MovementType::LoanDisbursement) {
                        $disbursementLine = $initialEntry->lines->firstWhere('account_id', '!=', $debt->liability_account_id);
                        if ($disbursementLine !== null) {
                            $disbursementAccountId = $disbursementLine->account_id;
                        }
                    }
                }

                $liabilityAccount = $debt->liabilityAccount;
                if ($liabilityAccount !== null && DecimalMath::compare($newPrincipal, DecimalMath::ZERO) > 0) {
                    if ($disbursementAccountId !== null) {
                        $disbursementAccount = Account::query()->ownedBy($user)->find($disbursementAccountId);
                        if ($disbursementAccount !== null) {
                            $lines = [
                                new JournalLineInputData(
                                    accountId: (int) $disbursementAccount->id,
                                    debit: $newPrincipal,
                                    credit: DecimalMath::ZERO,
                                    memo: "Desembolso corregido de deuda {$debt->name}",
                                ),
                                new JournalLineInputData(
                                    accountId: (int) $liabilityAccount->id,
                                    debit: DecimalMath::ZERO,
                                    credit: $newPrincipal,
                                    memo: "Reconocimiento de obligación por desembolso corregido: {$debt->name}",
                                ),
                            ];
                            $movType = MovementType::LoanDisbursement;
                            $desc = "Desembolso de {$debt->name} en {$disbursementAccount->name}";
                        } else {
                            $equityAccount = $ledgerBootstrap->systemEquityAccount($user, $debt->currency_code);
                            $lines = [
                                new JournalLineInputData(
                                    accountId: (int) $equityAccount->id,
                                    debit: $newPrincipal,
                                    credit: DecimalMath::ZERO,
                                    memo: "Contrapartida de saldo inicial pasivo corregido: {$debt->name}",
                                ),
                                new JournalLineInputData(
                                    accountId: (int) $liabilityAccount->id,
                                    debit: DecimalMath::ZERO,
                                    credit: $newPrincipal,
                                    memo: "Saldo inicial corregido de deuda: {$debt->name}",
                                ),
                            ];
                            $movType = MovementType::OpeningBalance;
                            $desc = "Saldo inicial de deuda {$debt->name}";
                        }
                    } else {
                        $equityAccount = $ledgerBootstrap->systemEquityAccount($user, $debt->currency_code);
                        $lines = [
                            new JournalLineInputData(
                                accountId: (int) $equityAccount->id,
                                debit: $newPrincipal,
                                credit: DecimalMath::ZERO,
                                memo: "Contrapartida de saldo inicial pasivo corregido: {$debt->name}",
                            ),
                            new JournalLineInputData(
                                accountId: (int) $liabilityAccount->id,
                                debit: DecimalMath::ZERO,
                                credit: $newPrincipal,
                                memo: "Saldo inicial corregido de deuda: {$debt->name}",
                            ),
                        ];
                        $movType = MovementType::OpeningBalance;
                        $desc = "Saldo inicial de deuda {$debt->name}";
                    }

                    $journalPoster->post($user, new PostJournalEntryData(
                        entryDate: $entryDate,
                        movementType: $movType,
                        description: $desc,
                        lines: $lines,
                        currencyCode: $debt->currency_code,
                        reference: "DEBT-{$debt->id}",
                        metadata: ['debt_id' => $debt->id],
                    ));
                }

                $debt->original_principal = $newPrincipal;
            });
        }

        $interest = isset($validated['editInterestRateAnnual']) && $validated['editInterestRateAnnual'] !== ''
            ? DecimalMath::normalize($validated['editInterestRateAnnual'])
            : '0.0000';

        $minPayment = isset($validated['editMinimumPayment']) && $validated['editMinimumPayment'] !== ''
            ? DecimalMath::normalize($validated['editMinimumPayment'])
            : '0.0000';

        $debt->update([
            'name' => trim($validated['editName']),
            'type' => DebtType::from($validated['editDebtType']),
            'creditor_name' => isset($validated['editCreditorName']) && trim((string) $validated['editCreditorName']) !== '' ? trim((string) $validated['editCreditorName']) : null,
            'interest_rate_annual' => $interest,
            'minimum_payment' => $minPayment,
            'due_date' => $validated['editDueDate'] ?? null,
            'payment_due_day' => $validated['editPaymentDueDay'] ?? null,
            'notes' => isset($validated['editNotes']) && trim((string) $validated['editNotes']) !== '' ? trim((string) $validated['editNotes']) : null,
            'status' => DebtStatus::from($validated['editStatus']),
        ]);

        if ($debt->liabilityAccount !== null) {
            $debt->liabilityAccount->update([
                'name' => trim($validated['editName']).' (Pasivo)',
                'institution_name' => isset($validated['editCreditorName']) && trim((string) $validated['editCreditorName']) !== '' ? trim((string) $validated['editCreditorName']) : null,
                'interest_rate_annual' => $interest,
                'payment_due_day' => $validated['editPaymentDueDay'] ?? null,
                'is_active' => $validated['editStatus'] === 'active',
            ]);
        }

        if (! empty($validated['editCreditorName'])) {
            FinancialInstitution::query()->firstOrCreate(
                ['user_id' => $user->id, 'name' => trim((string) $validated['editCreditorName'])],
                ['is_active' => true]
            );
        }

        $this->closeEditModal();
        session()->flash('status', '¡Crédito / Préstamo actualizado con éxito!');
    }

    public function deleteDebt(int $debtId, ReverseJournalEntryAction $reverseAction): void
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Debt $debt */
        $debt = Debt::query()->ownedBy($user)->findOrFail($debtId);

        if ($debt->payments()->count() > 0) {
            session()->flash('error', 'No se puede eliminar un crédito con abonos de pago registrados. Reversa primero sus abonos en la sección de Movimientos.');

            return;
        }

        DB::transaction(function () use ($user, $debt, $reverseAction): void {
            // Reversar asiento contable inicial vinculado a esta deuda
            $entries = JournalEntry::query()
                ->ownedBy($user)
                ->where('status', EntryStatus::Posted)
                ->where(function ($query) use ($debt): void {
                    $query->where('reference', "DEBT-{$debt->id}")
                        ->orWhereJsonContains('metadata->debt_id', $debt->id);
                })
                ->get();

            foreach ($entries as $entry) {
                $reverseAction->execute($user, $entry, "Anulación de crédito [{$debt->name}] creado por error.");
            }

            $liabilityAccount = $debt->liabilityAccount;
            $debt->delete();

            if ($liabilityAccount !== null) {
                $hasActiveLines = $liabilityAccount->journalLines()
                    ->whereHas('journalEntry', static fn ($q) => $q->where('status', EntryStatus::Posted))
                    ->exists();

                if (! $hasActiveLines) {
                    $liabilityAccount->update(['is_active' => false]);
                }
            }
        });

        session()->flash('status', 'El crédito creado por error ha sido anulado y eliminado correctamente.');
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

        $institutions = FinancialInstitution::query()
            ->ownedBy($user)
            ->active()
            ->orderBy('name')
            ->pluck('name');

        return view('livewire.debts.debts-index', [
            'debtsData' => $debtsData,
            'liquidAccounts' => $liquidAccounts,
            'selectedDebt' => $selectedDebt,
            'institutions' => $institutions,
            'totalPendingPrincipal' => $totalPendingPrincipal,
            'totalPrincipalPaid' => $totalPrincipalPaid,
            'totalInterestFeesPaid' => $totalInterestFeesPaid,
            'currencyCode' => $user->currency_code ?: 'USD',
        ]);
    }
}
