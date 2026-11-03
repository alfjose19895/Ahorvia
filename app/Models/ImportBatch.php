<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ImportBatchStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'account_id',
    'source_filename',
    'file_path',
    'status',
    'total_rows',
    'processed_rows',
    'failed_rows',
    'summary',
    'completed_at',
])]
class ImportBatch extends UserOwnedModel
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
        'total_rows' => 0,
        'processed_rows' => 0,
        'failed_rows' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ImportBatchStatus::class,
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'failed_rows' => 'integer',
            'summary' => 'array',
            'completed_at' => 'datetime',
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
     * @return HasMany<ImportRow, $this>
     */
    public function rows(): HasMany
    {
        return $this->hasMany(ImportRow::class);
    }
}
