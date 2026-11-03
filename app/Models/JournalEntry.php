<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EntryStatus;
use App\Enums\MovementType;
use App\Exceptions\Finance\ImmutableLedgerException;
use App\Support\DecimalMath;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $entry_number
 * @property CarbonInterface $entry_date
 * @property MovementType $movement_type
 * @property EntryStatus $status
 * @property string $currency_code
 * @property string $description
 * @property string|null $reference
 * @property string|null $idempotency_key
 * @property int|null $reversal_of_entry_id
 * @property int|null $reversed_by_entry_id
 * @property CarbonInterface|null $reversed_at
 * @property string|null $reversal_reason
 * @property CarbonInterface $posted_at
 * @property array<string, mixed>|null $metadata
 */
#[Fillable([
    'user_id',
    'entry_number',
    'entry_date',
    'movement_type',
    'status',
    'currency_code',
    'description',
    'reference',
    'idempotency_key',
    'reversal_of_entry_id',
    'reversed_by_entry_id',
    'reversed_at',
    'reversal_reason',
    'posted_at',
    'metadata',
])]
class JournalEntry extends UserOwnedModel
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'posted',
        'currency_code' => 'USD',
    ];

    protected static function booted(): void
    {
        static::deleting(static function (self $entry): void {
            if ($entry->status !== EntryStatus::Draft) {
                throw new ImmutableLedgerException(
                    'No está permitido eliminar físicamente un asiento contable confirmado. Ejecute un asiento de reverso.'
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'movement_type' => MovementType::class,
            'status' => EntryStatus::class,
            'reversed_at' => 'datetime',
            'posted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return HasMany<JournalLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class)->orderBy('line_order');
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_entry_id');
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversed_by_entry_id');
    }

    /**
     * @return HasOne<DebtPayment, $this>
     */
    public function debtPayment(): HasOne
    {
        return $this->hasOne(DebtPayment::class);
    }

    /**
     * @return HasOne<SavingsContribution, $this>
     */
    public function savingsContribution(): HasOne
    {
        return $this->hasOne(SavingsContribution::class);
    }

    /**
     * @return MorphMany<Attachment, $this>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * @return numeric-string
     */
    public function totalDebits(): string
    {
        $total = DecimalMath::ZERO;

        foreach ($this->lines as $line) {
            $total = DecimalMath::add($total, (string) $line->debit);
        }

        return $total;
    }

    /**
     * @return numeric-string
     */
    public function totalCredits(): string
    {
        $total = DecimalMath::ZERO;

        foreach ($this->lines as $line) {
            $total = DecimalMath::add($total, (string) $line->credit);
        }

        return $total;
    }

    public function isBalanced(): bool
    {
        return $this->lines->count() >= 2 && DecimalMath::equals($this->totalDebits(), $this->totalCredits());
    }
}
