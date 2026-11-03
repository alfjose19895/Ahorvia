<?php

declare(strict_types=1);

namespace App\Actions\Savings;

use App\Enums\EntryStatus;
use App\Enums\SavingsContributionType;
use App\Enums\SavingsGoalStatus;
use App\Enums\SavingsMode;
use App\Exceptions\Finance\FinancialDomainException;
use App\Models\Account;
use App\Models\SavingsContribution;
use App\Models\SavingsGoal;
use App\Models\User;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CreateSavingsGoalAction
{
    /**
     * @param  array{
     *     name: string,
     *     mode?: SavingsMode|string|null,
     *     currency_code?: string|null,
     *     target_amount: string|int,
     *     initial_amount?: string|int|null,
     *     start_date?: CarbonImmutable|string|null,
     *     target_date?: CarbonImmutable|string|null,
     *     duration_months?: int|null,
     *     linked_account_id?: int|null,
     *     notes?: string|null,
     * }  $attributes
     */
    public function execute(User $user, array $attributes): SavingsGoal
    {
        $targetAmount = DecimalMath::normalize($attributes['target_amount']);

        if (DecimalMath::compare($targetAmount, DecimalMath::ZERO) <= 0) {
            throw new FinancialDomainException('El monto objetivo de la meta de ahorro debe ser mayor a cero.');
        }

        $initialAmountRaw = $attributes['initial_amount'] ?? '0.0000';
        $initialAmount = DecimalMath::normalize($initialAmountRaw !== '' ? (string) $initialAmountRaw : '0.0000');

        if (DecimalMath::isNegative($initialAmount)) {
            throw new FinancialDomainException('El ahorro inicial no puede ser un importe negativo.');
        }

        $mode = $attributes['mode'] ?? SavingsMode::Fixed;
        if (is_string($mode)) {
            $mode = SavingsMode::from($mode);
        }

        $currency = strtoupper($attributes['currency_code'] ?? ($user->currency_code ?: 'USD'));
        $durationMonths = max(1, (int) ($attributes['duration_months'] ?? 6));

        $startDate = isset($attributes['start_date'])
            ? ($attributes['start_date'] instanceof CarbonImmutable ? $attributes['start_date']->toDateString() : (string) $attributes['start_date'])
            : now()->toDateString();

        $targetDate = isset($attributes['target_date'])
            ? ($attributes['target_date'] instanceof CarbonImmutable ? $attributes['target_date']->toDateString() : (string) $attributes['target_date'])
            : CarbonImmutable::parse($startDate)->addMonths($durationMonths)->toDateString();

        $linkedAccountId = isset($attributes['linked_account_id'])
            ? (int) $attributes['linked_account_id']
            : null;

        if ($linkedAccountId !== null) {
            Account::query()->ownedBy($user)->findOrFail($linkedAccountId);
        }

        return DB::transaction(function () use (
            $user,
            $mode,
            $currency,
            $targetAmount,
            $initialAmount,
            $startDate,
            $targetDate,
            $durationMonths,
            $linkedAccountId,
            $attributes
        ): SavingsGoal {
            $goal = SavingsGoal::query()->create([
                'user_id' => $user->getKey(),
                'linked_account_id' => $linkedAccountId,
                'name' => trim($attributes['name']),
                'mode' => $mode,
                'status' => SavingsGoalStatus::Active,
                'currency_code' => $currency,
                'target_amount' => $targetAmount,
                'initial_amount' => $initialAmount,
                'start_date' => $startDate,
                'target_date' => $targetDate,
                'duration_months' => $durationMonths,
                'notes' => isset($attributes['notes']) ? trim((string) $attributes['notes']) : null,
            ]);

            if (DecimalMath::compare($initialAmount, DecimalMath::ZERO) > 0) {
                SavingsContribution::query()->create([
                    'user_id' => $user->getKey(),
                    'savings_goal_id' => $goal->getKey(),
                    'account_id' => $linkedAccountId,
                    'contribution_type' => SavingsContributionType::Opening,
                    'contribution_date' => $startDate,
                    'amount' => $initialAmount,
                    'status' => EntryStatus::Posted,
                    'reference' => 'AHORRO-INICIAL',
                    'notes' => 'Ahorro base reconocido al crear la meta',
                ]);

                if (DecimalMath::compare($initialAmount, $targetAmount) >= 0) {
                    $goal->forceFill([
                        'status' => SavingsGoalStatus::Completed,
                        'completed_at' => now(),
                    ])->save();
                }
            }

            return $goal->load(['linkedAccount', 'contributions']);
        });
    }
}
