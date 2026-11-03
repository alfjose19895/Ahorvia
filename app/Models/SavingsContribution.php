<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EntryStatus;
use App\Enums\SavingsContributionType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $savings_goal_id
 * @property int|null $account_id
 * @property int|null $source_account_id
 * @property int|null $journal_entry_id
 * @property SavingsContributionType $contribution_type
 * @property CarbonInterface $contribution_date
 * @property numeric-string $amount
 * @property EntryStatus $status
 * @property string|null $idempotency_key
 * @property string|null $reference
 * @property string|null $notes
 * @property-read SavingsGoal|null $savingsGoal
 * @property-read Account|null $account
 * @property-read Account|null $sourceAccount
 * @property-read JournalEntry|null $journalEntry
 */
#[Fillable([
    'user_id',
    'savings_goal_id',
    'account_id',
    'source_account_id',
    'journal_entry_id',
    'contribution_type',
    'contribution_date',
    'amount',
    'status',
    'idempotency_key',
    'reference',
    'notes',
])]
class SavingsContribution extends UserOwnedModel
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'posted',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'contribution_type' => SavingsContributionType::class,
            'contribution_date' => 'date',
            'amount' => 'decimal:4',
            'status' => EntryStatus::class,
        ];
    }

    /**
     * @return BelongsTo<SavingsGoal, $this>
     */
    public function savingsGoal(): BelongsTo
    {
        return $this->belongsTo(SavingsGoal::class);
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
    public function sourceAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'source_account_id');
    }

    /**
     * @return BelongsTo<JournalEntry, $this>
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
