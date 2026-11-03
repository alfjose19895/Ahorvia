<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DebtStatus;
use App\Enums\DebtType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $liability_account_id
 * @property int|null $budget_category_id
 * @property string $name
 * @property DebtType $type
 * @property DebtStatus $status
 * @property string|null $creditor_name
 * @property string $currency_code
 * @property numeric-string $original_principal
 * @property numeric-string|null $credit_limit
 * @property numeric-string $interest_rate_annual
 * @property numeric-string $minimum_payment
 * @property CarbonInterface $start_date
 * @property CarbonInterface|null $due_date
 * @property int|null $payment_due_day
 * @property int|null $cutoff_day
 * @property string|null $notes
 * @property-read Account|null $liabilityAccount
 * @property-read Category|null $budgetCategory
 */
#[Fillable([
    'user_id',
    'liability_account_id',
    'budget_category_id',
    'name',
    'type',
    'status',
    'creditor_name',
    'currency_code',
    'original_principal',
    'credit_limit',
    'interest_rate_annual',
    'minimum_payment',
    'start_date',
    'due_date',
    'payment_due_day',
    'cutoff_day',
    'notes',
])]
class Debt extends UserOwnedModel
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'active',
        'currency_code' => 'USD',
        'interest_rate_annual' => '0.0000',
        'minimum_payment' => '0.0000',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => DebtType::class,
            'status' => DebtStatus::class,
            'original_principal' => 'decimal:4',
            'credit_limit' => 'decimal:4',
            'interest_rate_annual' => 'decimal:4',
            'minimum_payment' => 'decimal:4',
            'start_date' => 'date',
            'due_date' => 'date',
            'payment_due_day' => 'integer',
            'cutoff_day' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function liabilityAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'liability_account_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function budgetCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'budget_category_id');
    }

    /**
     * @return HasMany<DebtPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }
}
