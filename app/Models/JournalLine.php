<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EntryStatus;
use App\Exceptions\Finance\ImmutableLedgerException;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $journal_entry_id
 * @property int $user_id
 * @property int $account_id
 * @property int|null $category_id
 * @property int $line_order
 * @property numeric-string $debit
 * @property numeric-string $credit
 * @property string|null $memo
 * @property bool $is_reconciled
 * @property CarbonInterface|null $reconciled_at
 */
#[Fillable([
    'journal_entry_id',
    'user_id',
    'account_id',
    'category_id',
    'line_order',
    'debit',
    'credit',
    'memo',
    'is_reconciled',
    'reconciled_at',
])]
class JournalLine extends UserOwnedModel
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'line_order' => 1,
        'debit' => '0.0000',
        'credit' => '0.0000',
        'is_reconciled' => false,
    ];

    protected static function booted(): void
    {
        static::deleting(static function (self $line): void {
            $entry = $line->journalEntry()->first();

            if ($entry !== null && $entry->status !== EntryStatus::Draft) {
                throw new ImmutableLedgerException(
                    'No está permitido eliminar físicamente líneas de un asiento contable confirmado.'
                );
            }
        });

        static::updating(static function (self $line): void {
            if ($line->isDirty(['journal_entry_id', 'user_id', 'account_id', 'debit', 'credit'])) {
                $entry = $line->journalEntry()->first();

                if ($entry !== null && $entry->status !== EntryStatus::Draft) {
                    throw new ImmutableLedgerException(
                        'Los importes y cuentas de una línea contable confirmada son inmutables. Utilice un reverso.'
                    );
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'line_order' => 'integer',
            'debit' => 'decimal:4',
            'credit' => 'decimal:4',
            'is_reconciled' => 'boolean',
            'reconciled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<JournalEntry, $this>
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
