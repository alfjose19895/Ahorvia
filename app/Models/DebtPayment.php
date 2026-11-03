<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EntryStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $debt_id
 * @property int $source_account_id
 * @property int $journal_entry_id
 * @property CarbonInterface $payment_date
 * @property numeric-string $total_amount
 * @property numeric-string $principal_amount
 * @property numeric-string $interest_amount
 * @property numeric-string $fee_amount
 * @property EntryStatus $status
 * @property string|null $reference
 * @property string|null $notes
 * @property-read Debt|null $debt
 * @property-read Account|null $sourceAccount
 * @property-read JournalEntry|null $journalEntry
 */
#[Fillable([
    'user_id',
    'debt_id',
    'source_account_id',
    'journal_entry_id',
    'payment_date',
    'total_amount',
    'principal_amount',
    'interest_amount',
    'fee_amount',
    'status',
    'reference',
    'notes',
])]
class DebtPayment extends UserOwnedModel
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'interest_amount' => '0.0000',
        'fee_amount' => '0.0000',
        'status' => 'posted',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'total_amount' => 'decimal:4',
            'principal_amount' => 'decimal:4',
            'interest_amount' => 'decimal:4',
            'fee_amount' => 'decimal:4',
            'status' => EntryStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Debt, $this>
     */
    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
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

    /**
     * @return MorphMany<Attachment, $this>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
