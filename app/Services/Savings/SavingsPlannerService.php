<?php

declare(strict_types=1);

namespace App\Services\Savings;

use App\Data\Savings\SavingsGoalPlanData;
use App\Enums\EntryStatus;
use App\Enums\SavingsGoalStatus;
use App\Enums\SavingsMode;
use App\Models\SavingsContribution;
use App\Models\SavingsGoal;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;

class SavingsPlannerService
{
    /**
     * Evalúa una meta del Planificador Inteligente de Ahorro (horizonte inicial 6 meses).
     *
     * Fórmulas oficiales:
     * - Ahorro pendiente = Monto objetivo - Ahorro reconocido
     * - Cuota mensual = Ahorro pendiente / Meses restantes
     *
     * Si el usuario omite un aporte, las cuotas futuras se recalculan sin alterar el historial.
     */
    public function analyzeGoal(SavingsGoal $goal, ?CarbonImmutable $asOfDate = null): SavingsGoalPlanData
    {
        $referenceDate = ($asOfDate ?? CarbonImmutable::now())->startOfDay();
        $startDate = CarbonImmutable::parse($goal->start_date->toDateString())->startOfDay();
        $targetDate = CarbonImmutable::parse($goal->target_date->toDateString())->startOfDay();

        $targetAmount = DecimalMath::normalize((string) $goal->target_amount);
        $initialAmount = DecimalMath::normalize((string) $goal->initial_amount);
        $recognizedSavings = $this->recognizedSavingsForGoal($goal);

        $rawPending = DecimalMath::sub($targetAmount, $recognizedSavings);
        $pendingSavings = DecimalMath::isNegative($rawPending) ? DecimalMath::ZERO : $rawPending;
        $isCompleted = DecimalMath::compare($pendingSavings, DecimalMath::ZERO) === 0
            || $goal->status === SavingsGoalStatus::Completed;

        $durationMonths = max(1, (int) $goal->duration_months);
        $rawOriginalNetTarget = DecimalMath::sub($targetAmount, $initialAmount);
        $originalNetTarget = DecimalMath::isNegative($rawOriginalNetTarget) ? DecimalMath::ZERO : $rawOriginalNetTarget;
        $originalFixedQuota = DecimalMath::div($originalNetTarget, $durationMonths);

        $effectiveReference = $referenceDate->lessThan($startDate) ? $startDate : $referenceDate;
        $monthDiff = (($targetDate->year - $effectiveReference->year) * 12)
            + ($targetDate->month - $effectiveReference->month);

        $remainingMonths = $isCompleted
            ? max(0, $monthDiff)
            : max(1, min($durationMonths, $monthDiff));

        $divisorMonths = max(1, $remainingMonths);
        $recalculatedQuota = $isCompleted
            ? DecimalMath::ZERO
            : DecimalMath::div($pendingSavings, $divisorMonths);

        $requiresAdjustment = ! $isCompleted
            && DecimalMath::compare($recalculatedQuota, $originalFixedQuota) > 0;

        $recommendedMonthlyQuota = $this->resolveRecommendedQuota(
            mode: $goal->mode,
            isCompleted: $isCompleted,
            pendingSavings: $pendingSavings,
            originalFixedQuota: $originalFixedQuota,
            recalculatedQuota: $recalculatedQuota,
        );

        $completionPercentage = $this->calculateCompletionPercentage($recognizedSavings, $targetAmount);

        return new SavingsGoalPlanData(
            goalId: (int) $goal->getKey(),
            goalName: $goal->name,
            mode: $goal->mode,
            status: $isCompleted ? SavingsGoalStatus::Completed : $goal->status,
            currencyCode: strtoupper($goal->currency_code),
            targetAmount: $targetAmount,
            recognizedSavings: $recognizedSavings,
            pendingSavings: $pendingSavings,
            remainingMonths: $remainingMonths,
            recommendedMonthlyQuota: $recommendedMonthlyQuota,
            originalFixedMonthlyQuota: $originalFixedQuota,
            completionPercentage: $completionPercentage,
            targetDate: $targetDate->toDateString(),
            isCompleted: $isCompleted,
            requiresAdjustment: $requiresAdjustment,
        );
    }

    /**
     * Suma únicamente aportes reales confirmados (transferencias contables, ahorro inicial o reservas identificables).
     *
     * @return numeric-string
     */
    public function recognizedSavingsForGoal(SavingsGoal $goal): string
    {
        $amounts = SavingsContribution::query()
            ->ownedBy($goal->user_id)
            ->where('savings_goal_id', $goal->getKey())
            ->where('status', EntryStatus::Posted)
            ->pluck('amount');

        $recognized = DecimalMath::ZERO;

        foreach ($amounts as $amount) {
            $recognized = DecimalMath::add($recognized, (string) $amount);
        }

        return $recognized;
    }

    /**
     * @param  numeric-string  $pendingSavings
     * @param  numeric-string  $originalFixedQuota
     * @param  numeric-string  $recalculatedQuota
     * @return numeric-string
     */
    private function resolveRecommendedQuota(
        SavingsMode $mode,
        bool $isCompleted,
        string $pendingSavings,
        string $originalFixedQuota,
        string $recalculatedQuota,
    ): string {
        if ($isCompleted) {
            return DecimalMath::ZERO;
        }

        if ($mode === SavingsMode::Adjustable) {
            return $recalculatedQuota;
        }

        // En ahorro fijo la cuota es constante, pero si se omitió un aporte y la cuota requerida
        // supera la cuota fija original, se recalculan las cuotas futuras sin alterar el historial.
        if (DecimalMath::compare($recalculatedQuota, $originalFixedQuota) > 0) {
            return $recalculatedQuota;
        }

        return DecimalMath::compare($pendingSavings, $originalFixedQuota) < 0
            ? $pendingSavings
            : $originalFixedQuota;
    }

    private function calculateCompletionPercentage(string $recognized, string $target): int
    {
        if (DecimalMath::compare($target, DecimalMath::ZERO) <= 0) {
            return 100;
        }

        $scaled = DecimalMath::mul($recognized, 100);
        $percent = (int) bcdiv($scaled, DecimalMath::normalize($target), 0);

        return min(100, max(0, $percent));
    }
}
