<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MovementType;
use App\Enums\RecurrenceFrequency;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'account_id',
    'destination_account_id',
    'category_id',
    'name',
    'movement_type',
    'frequency',
    'amount',
    'currency_code',
    'start_date',
    'end_date',
    'next_run_date',
    'auto_post',
    'is_active',
])]
class RecurringRule extends UserOwnedModel
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency_code' => 'USD',
        'auto_post' => false,
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'movement_type' => MovementType::class,
            'frequency' => RecurrenceFrequency::class,
            'amount' => 'decimal:4',
            'start_date' => 'date',
            'end_date' => 'date',
            'next_run_date' => 'date',
            'auto_post' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'destination_account_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<RecurringOccurrence, $this>
     */
    public function occurrences(): HasMany
    {
        return $this->hasMany(RecurringOccurrence::class);
    }
}
