<?php

declare(strict_types=1);

namespace App\Actions\Budgets;

use App\Exceptions\Finance\CrossTenantOwnershipException;
use App\Exceptions\Finance\FinancialDomainException;
use App\Models\Budget;
use App\Models\BudgetLine;
use App\Models\Category;
use App\Models\User;
use App\Support\DecimalMath;
use Illuminate\Support\Facades\DB;

class UpsertMonthlyBudgetAction
{
    /**
     * @param  array{
     *     year: int,
     *     month: int,
     *     currency_code?: string|null,
     *     name?: string|null,
     *     notes?: string|null,
     *     lines: list<array{
     *         category_id: int,
     *         limit_amount: string|int,
     *     }>,
     * }  $attributes
     */
    public function execute(User $user, array $attributes): Budget
    {
        $year = (int) $attributes['year'];
        $month = (int) $attributes['month'];

        if ($month < 1 || $month > 12) {
            throw new FinancialDomainException("Mes inválido [{$month}] para el presupuesto mensual.");
        }

        $currency = strtoupper($attributes['currency_code'] ?? ($user->currency_code ?: 'USD'));
        $name = isset($attributes['name']) && trim((string) $attributes['name']) !== ''
            ? trim((string) $attributes['name'])
            : sprintf('Presupuesto %04d-%02d', $year, $month);

        $linesData = $attributes['lines'];
        if ($linesData === []) {
            throw new FinancialDomainException('El presupuesto debe contener al menos una categoría con su límite asignado.');
        }

        $categoryIds = array_map(static fn (array $l): int => (int) $l['category_id'], $linesData);
        $ownedCategoriesCount = Category::query()
            ->ownedBy($user)
            ->whereIn('id', $categoryIds)
            ->count();

        if ($ownedCategoriesCount !== count(array_unique($categoryIds))) {
            throw new CrossTenantOwnershipException('Una o más categorías del presupuesto no pertenecen al usuario autorizado.');
        }

        return DB::transaction(function () use ($user, $year, $month, $currency, $name, $linesData, $attributes): Budget {
            /** @var Budget $budget */
            $budget = Budget::query()->firstOrCreate(
                [
                    'user_id' => $user->getKey(),
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => $currency,
                ],
                [
                    'name' => $name,
                    'total_limit' => DecimalMath::ZERO,
                    'notes' => isset($attributes['notes']) ? trim((string) $attributes['notes']) : null,
                    'is_active' => true,
                ]
            );

            $totalLimit = DecimalMath::ZERO;
            $processedCategoryIds = [];

            foreach ($linesData as $lineItem) {
                $catId = (int) $lineItem['category_id'];
                $limit = DecimalMath::normalize($lineItem['limit_amount']);

                if (DecimalMath::isNegative($limit)) {
                    throw new FinancialDomainException('El límite presupuestario por categoría no puede ser negativo.');
                }

                $totalLimit = DecimalMath::add($totalLimit, $limit);
                $processedCategoryIds[] = $catId;

                BudgetLine::query()->updateOrCreate(
                    [
                        'budget_id' => $budget->getKey(),
                        'user_id' => $user->getKey(),
                        'category_id' => $catId,
                    ],
                    [
                        'limit_amount' => $limit,
                    ]
                );
            }

            // Eliminar líneas de categorías que ya no están presentes en la actualización
            BudgetLine::query()
                ->where('budget_id', $budget->getKey())
                ->whereNotIn('category_id', $processedCategoryIds)
                ->delete();

            $budget->forceFill([
                'name' => $name,
                'total_limit' => $totalLimit,
                'notes' => isset($attributes['notes']) ? trim((string) $attributes['notes']) : $budget->notes,
                'is_active' => true,
            ])->save();

            return $budget->load('lines.category');
        });
    }
}
