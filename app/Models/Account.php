<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountType;
use App\Enums\LedgerNature;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property AccountType $type
 * @property LedgerNature $ledger_nature
 * @property string $currency_code
 * @property string|null $institution_name
 * @property string|null $account_number_last4
 * @property numeric-string|null $credit_limit
 * @property int|null $billing_cutoff_day
 * @property int|null $payment_due_day
 * @property numeric-string $interest_rate_annual
 * @property bool $is_active
 * @property bool $is_system
 * @property bool $include_in_net_worth
 * @property CarbonInterface|null $last_reconciled_at
 * @property numeric-string|null $last_reconciled_balance
 * @property string|null $notes
 */
#[Fillable([
    'user_id',
    'name',
    'type',
    'ledger_nature',
    'currency_code',
    'institution_name',
    'account_number_last4',
    'credit_limit',
    'billing_cutoff_day',
    'payment_due_day',
    'interest_rate_annual',
    'is_active',
    'is_system',
    'include_in_net_worth',
    'last_reconciled_at',
    'last_reconciled_balance',
    'notes',
])]
class Account extends UserOwnedModel
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency_code' => 'USD',
        'is_active' => true,
        'is_system' => false,
        'include_in_net_worth' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'ledger_nature' => LedgerNature::class,
            'credit_limit' => 'decimal:4',
            'interest_rate_annual' => 'decimal:4',
            'last_reconciled_balance' => 'decimal:4',
            'billing_cutoff_day' => 'integer',
            'payment_due_day' => 'integer',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'include_in_net_worth' => 'boolean',
            'last_reconciled_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<JournalLine, $this>
     */
    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    /**
     * @return HasMany<Debt, $this>
     */
    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class, 'liability_account_id');
    }

    /**
     * @return HasMany<SavingsGoal, $this>
     */
    public function savingsGoals(): HasMany
    {
        return $this->hasMany(SavingsGoal::class, 'linked_account_id');
    }

    /**
     * @return HasMany<SavingsContribution, $this>
     */
    public function savingsContributions(): HasMany
    {
        return $this->hasMany(SavingsContribution::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeUserFacing(Builder $query): Builder
    {
        return $query->where('is_system', false);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
