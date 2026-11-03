<?php

declare(strict_types=1);

namespace App\Actions\Transactions;

use App\Data\Finance\JournalLineInputData;
use App\Data\Finance\PostJournalEntryData;
use App\Enums\DebtStatus;
use App\Enums\EntryStatus;
use App\Enums\MovementType;
use App\Enums\SavingsGoalStatus;
use App\Exceptions\Finance\FinancialDomainException;
use App\Models\DebtPayment;
use App\Models\JournalEntry;
use App\Models\SavingsContribution;
use App\Models\User;
use App\Services\Debt\DebtBalanceService;
use App\Services\Finance\AuditLoggerService;
use App\Services\Finance\JournalPosterService;
use App\Services\Savings\SavingsPlannerService;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ReverseJournalEntryAction
{
    public function __construct(
        private readonly JournalPosterService $journalPoster,
        private readonly AuditLoggerService $auditLogger,
        private readonly DebtBalanceService $debtBalanceService,
        private readonly SavingsPlannerService $savingsPlannerService,
    ) {}

    public function execute(User $user, JournalEntry|int $entry, string $reversalReason): JournalEntry
    {
        $reason = trim($reversalReason);

        if ($reason === '') {
            throw new FinancialDomainException('Debe proporcionar un motivo justificado para ejecutar el reverso contable.');
        }

        return DB::transaction(function () use ($user, $entry, $reason): JournalEntry {
            $entryId = $entry instanceof JournalEntry ? $entry->getKey() : $entry;

            /** @var JournalEntry $originalEntry */
            $originalEntry = JournalEntry::query()
                ->ownedBy($user)
                ->whereKey($entryId)
                ->lockForUpdate()
                ->with(['lines', 'debtPayment.debt', 'savingsContribution.savingsGoal'])
                ->firstOrFail();

            if ($originalEntry->reversed_by_entry_id !== null || $originalEntry->status === EntryStatus::Reversed) {
                throw new FinancialDomainException('El asiento contable ya ha sido reversado previamente.');
            }

            if ($originalEntry->status !== EntryStatus::Posted) {
                throw new FinancialDomainException(
                    "Solo es posible reversar asientos en estado confirmado (actual: {$originalEntry->status->value})."
                );
            }

            $reversalLines = [];
            foreach ($originalEntry->lines as $line) {
                $reversalLines[] = new JournalLineInputData(
                    accountId: (int) $line->account_id,
                    debit: (string) $line->credit,
                    credit: (string) $line->debit,
                    categoryId: $line->category_id !== null ? (int) $line->category_id : null,
                    memo: "Reverso de línea #{$line->line_order} ({$line->memo})",
                );
            }

            $reversalPostData = new PostJournalEntryData(
                entryDate: CarbonImmutable::now($user->timezone ?: 'UTC'),
                movementType: MovementType::Reversal,
                description: "Reverso de asiento {$originalEntry->entry_number}: {$reason}",
                lines: $reversalLines,
                currencyCode: strtoupper($originalEntry->currency_code),
                reference: "REV-{$originalEntry->entry_number}",
                metadata: [
                    'reversal_of_entry_id' => $originalEntry->getKey(),
                    'reversal_reason' => $reason,
                ],
            );

            $reversalEntry = $this->journalPoster->post($user, $reversalPostData);

            $reversalEntry->forceFill([
                'reversal_of_entry_id' => $originalEntry->getKey(),
            ])->save();

            $originalEntry->forceFill([
                'status' => EntryStatus::Reversed,
                'reversed_by_entry_id' => $reversalEntry->getKey(),
                'reversed_at' => now(),
                'reversal_reason' => $reason,
            ])->save();

            if ($originalEntry->debtPayment !== null) {
                /** @var DebtPayment $debtPayment */
                $debtPayment = $originalEntry->debtPayment;
                $debtPayment->forceFill(['status' => EntryStatus::Reversed])->save();

                $debt = $debtPayment->debt;
                if ($debt !== null && $debt->status === DebtStatus::PaidOff) {
                    $pending = $this->debtBalanceService->pendingPrincipal($debt);
                    if (DecimalMath::compare($pending, DecimalMath::ZERO) > 0) {
                        $debt->forceFill(['status' => DebtStatus::Active])->save();
                    }
                }
            }

            if ($originalEntry->savingsContribution !== null) {
                /** @var SavingsContribution $contribution */
                $contribution = $originalEntry->savingsContribution;
                $contribution->forceFill(['status' => EntryStatus::Reversed])->save();

                $goal = $contribution->savingsGoal;
                if ($goal !== null && $goal->status === SavingsGoalStatus::Completed) {
                    $recognized = $this->savingsPlannerService->recognizedSavingsForGoal($goal);
                    if (DecimalMath::compare($recognized, (string) $goal->target_amount) < 0) {
                        $goal->forceFill([
                            'status' => SavingsGoalStatus::Active,
                            'completed_at' => null,
                        ])->save();
                    }
                }
            }

            $this->auditLogger->log(
                user: $user,
                event: 'journal_entry.reversed',
                auditable: $originalEntry,
                oldValues: ['status' => EntryStatus::Posted->value],
                newValues: [
                    'status' => EntryStatus::Reversed->value,
                    'reversed_by_entry_id' => $reversalEntry->getKey(),
                    'reversal_reason' => $reason,
                ]
            );

            return $reversalEntry;
        });
    }
}
