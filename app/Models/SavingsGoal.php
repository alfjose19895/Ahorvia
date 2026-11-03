<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SavingsGoalStatus;
use App\Enums\SavingsMode;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $linked_account_id
 * @property string $name
 * @property SavingsMode $mode
 * @property SavingsGoalStatus $status
 * @property string $currency_code
 * @property numeric-string $target_amount
 * @property numeric-string $initial_amount
 * @property CarbonInterface $start_date
 * @property CarbonInterface $target_date
 * @property int $duration_months
 * @property string|null $notes
 * @property CarbonInterface|null $completed_at
 * @property-read Account|null $linkedAccount
 */
#[Fillable([
    'user_id',
    'linked_account_id',
    'name',
    'mode',
    'status',
    'currency_code',
    'target_amount',
    'initial_amount',
    'start_date',
    'target_date',
    'duration_months',
    'notes',
    'completed_at',
])]
class SavingsGoal extends UserOwnedModel
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'mode' => 'fixed',
        'status' => 'active',
        'currency_code' => 'USD',
        'initial_amount' => '0.0000',
        'duration_months' => 6,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mode' => SavingsMode::class,
            'status' => SavingsGoalStatus::class,
            'target_amount' => 'decimal:4',
            'initial_amount' => 'decimal:4',
            'start_date' => 'date',
            'target_date' => 'date',
            'duration_months' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function linkedAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'linked_account_id');
    }

    /**
     * @return HasMany<SavingsContribution, $this>
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(SavingsContribution::class);
    }
}
