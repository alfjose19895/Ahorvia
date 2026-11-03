<?php

declare(strict_types=1);

namespace App\Actions\Savings;

use App\Data\Finance\JournalLineInputData;
use App\Data\Finance\PostJournalEntryData;
use App\Enums\EntryStatus;
use App\Enums\MovementType;
use App\Enums\SavingsContributionType;
use App\Enums\SavingsGoalStatus;
use App\Exceptions\Finance\FinancialDomainException;
use App\Exceptions\Finance\InsufficientAvailableFundsException;
use App\Models\Account;
use App\Models\SavingsContribution;
use App\Models\SavingsGoal;
use App\Models\User;
use App\Services\Finance\AccountBalanceService;
use App\Services\Finance\JournalPosterService;
use App\Services\Savings\SavingsPlannerService;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class RecordSavingsContributionAction
{
    public function __construct(
        private readonly JournalPosterService $journalPoster,
        private readonly AccountBalanceService $accountBalanceService,
        private readonly SavingsPlannerService $savingsPlannerService,
    ) {}

    /**
     * @param  array{
     *     savings_goal_id: int,
     *     contribution_type: SavingsContributionType,
     *     amount: string|int,
     *     source_account_id?: int|null,
     *     destination_account_id?: int|null,
     *     account_id?: int|null,
     *     contribution_date?: CarbonImmutable|string|null,
     *     reference?: string|null,
     *     notes?: string|null,
     *     idempotency_key?: string|null,
     * }  $attributes
     */
    public function execute(User $user, array $attributes): SavingsContribution
    {
        $amount = DecimalMath::normalize($attributes['amount']);

        if (DecimalMath::compare($amount, DecimalMath::ZERO) <= 0) {
            throw new FinancialDomainException('El importe del aporte de ahorro debe ser mayor a cero.');
        }

        /** @var SavingsContributionType $type */
        $type = $attributes['contribution_type'];
        $goalId = (int) $attributes['savings_goal_id'];

        return DB::transaction(function () use ($user, $type, $goalId, $amount, $attributes): SavingsContribution {
            /** @var SavingsGoal $goal */
            $goal = SavingsGoal::query()
                ->ownedBy($user)
                ->lockForUpdate()
                ->findOrFail($goalId);

            if ($goal->status === SavingsGoalStatus::Cancelled) {
                throw new FinancialDomainException('No es posible registrar aportes en una meta de ahorro cancelada.');
            }

            $idempotencyKey = isset($attributes['idempotency_key']) && trim((string) $attributes['idempotency_key']) !== ''
                ? trim((string) $attributes['idempotency_key'])
                : null;

            if ($idempotencyKey !== null) {
                $existing = SavingsContribution::query()
                    ->ownedBy($user)
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existing !== null) {
                    return $existing;
                }
            }

            $date = isset($attributes['contribution_date'])
                ? ($attributes['contribution_date'] instanceof CarbonImmutable
                    ? $attributes['contribution_date']
                    : CarbonImmutable::parse((string) $attributes['contribution_date']))
                : CarbonImmutable::now($user->timezone ?: 'UTC');

            $journalEntryId = null;
            $accountId = null;
            $sourceAccountId = null;

            if ($type === SavingsContributionType::Transfer) {
                $srcId = (int) ($attributes['source_account_id'] ?? 0);
                $destId = (int) ($attributes['destination_account_id'] ?? ($goal->linked_account_id ?? 0));

                if ($srcId === 0 || $destId === 0) {
                    throw new FinancialDomainException(
                        'Para registrar un aporte por transferencia debe indicar la cuenta de origen y la cuenta de destino (o tener una cuenta vinculada a la meta).'
                    );
                }

                if ($srcId === $destId) {
                    throw new FinancialDomainException('La cuenta de origen y destino de ahorro no pueden ser la misma.');
                }

                /** @var Account $sourceAccount */
                $sourceAccount = Account::query()->ownedBy($user)->lockForUpdate()->findOrFail($srcId);
                /** @var Account $destAccount */
                $destAccount = Account::query()->ownedBy($user)->lockForUpdate()->findOrFail($destId);

                $currency = strtoupper($sourceAccount->currency_code);

                $lines = [
                    new JournalLineInputData(
                        accountId: $destId,
                        debit: $amount,
                        credit: DecimalMath::ZERO,
                        memo: "Aporte recibido hacia meta {$goal->name}",
                    ),
                    new JournalLineInputData(
                        accountId: $srcId,
                        debit: DecimalMath::ZERO,
                        credit: $amount,
                        memo: "Aporte debitado desde {$sourceAccount->name}",
                    ),
                ];

                $entry = $this->journalPoster->post($user, new PostJournalEntryData(
                    entryDate: $date,
                    movementType: MovementType::SavingsContribution,
                    description: "Aporte a meta de ahorro: {$goal->name}",
                    lines: $lines,
                    currencyCode: $currency,
                    reference: isset($attributes['reference']) ? trim((string) $attributes['reference']) : "AHORRO-{$goal->getKey()}",
                    idempotencyKey: $idempotencyKey !== null ? 'je_'.$idempotencyKey : null,
                    metadata: ['savings_goal_id' => $goal->getKey()],
                ));

                $journalEntryId = (int) $entry->getKey();
                $accountId = $destId;
                $sourceAccountId = $srcId;
            } elseif ($type === SavingsContributionType::Reserve) {
                // Reserva identificable dentro de la misma cuenta: sin movimiento contable externo
                $accId = (int) ($attributes['account_id'] ?? ($attributes['source_account_id'] ?? ($goal->linked_account_id ?? 0)));

                if ($accId === 0) {
                    throw new FinancialDomainException('Debe especificar la cuenta sobre la cual se aparta la reserva de ahorro.');
                }

                /** @var Account $account */
                $account = Account::query()->ownedBy($user)->lockForUpdate()->findOrFail($accId);

                $availableFunds = $this->accountBalanceService->availableForAccount($account);

                if (DecimalMath::compare($availableFunds, $amount) < 0) {
                    throw new InsufficientAvailableFundsException(
                        "Fondos disponibles insuficientes en {$account->name} ({$availableFunds}) para reservar {$amount}."
                    );
                }

                $accountId = $accId;
            } else {
                throw new FinancialDomainException("Tipo de aporte [{$type->value}] no admitido en este flujo.");
            }

            $contribution = SavingsContribution::query()->create([
                'user_id' => $user->getKey(),
                'savings_goal_id' => $goal->getKey(),
                'account_id' => $accountId,
                'source_account_id' => $sourceAccountId,
                'journal_entry_id' => $journalEntryId,
                'contribution_type' => $type,
                'contribution_date' => $date->toDateString(),
                'amount' => $amount,
                'status' => EntryStatus::Posted,
                'idempotency_key' => $idempotencyKey,
                'reference' => isset($attributes['reference']) ? trim((string) $attributes['reference']) : null,
                'notes' => isset($attributes['notes']) ? trim((string) $attributes['notes']) : null,
            ]);

            // Verificar si con este aporte la meta se cumple en su totalidad
            $recognized = $this->savingsPlannerService->recognizedSavingsForGoal($goal);
            if (DecimalMath::compare($recognized, (string) $goal->target_amount) >= 0) {
                $goal->forceFill([
                    'status' => SavingsGoalStatus::Completed,
                    'completed_at' => now(),
                ])->save();
            }

            return $contribution->load(['savingsGoal', 'account', 'sourceAccount', 'journalEntry']);
        });
    }
}
