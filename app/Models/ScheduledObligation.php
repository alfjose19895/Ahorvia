<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ObligationStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $category_id
 * @property int|null $debt_id
 * @property int|null $recurring_rule_id
 * @property string $title
 * @property numeric-string $amount
 * @property string $currency_code
 * @property CarbonInterface $due_date
 * @property ObligationStatus $status
 * @property bool $is_included_in_budget
 * @property int|null $paid_journal_entry_id
 * @property string|null $notes
 * @property-read Category|null $category
 * @property-read Debt|null $debt
 */
#[Fillable([
    'user_id',
    'category_id',
    'debt_id',
    'recurring_rule_id',
    'title',
    'amount',
    'currency_code',
    'due_date',
    'status',
    'is_included_in_budget',
    'paid_journal_entry_id',
    'notes',
])]
class ScheduledObligation extends UserOwnedModel
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency_code' => 'USD',
        'status' => 'pending',
        'is_included_in_budget' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'due_date' => 'date',
            'status' => ObligationStatus::class,
            'is_included_in_budget' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Debt, $this>
     */
    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }

    /**
     * @return BelongsTo<RecurringRule, $this>
     */
    public function recurringRule(): BelongsTo
    {
        return $this->belongsTo(RecurringRule::class);
    }

    /**
     * @return BelongsTo<JournalEntry, $this>
     */
    public function paidJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'paid_journal_entry_id');
    }
}
