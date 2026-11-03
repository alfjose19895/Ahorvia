<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'budget_id',
    'user_id',
    'category_id',
    'limit_amount',
    'alert_80_notified_at',
    'alert_90_notified_at',
    'alert_100_notified_at',
])]
class BudgetLine extends UserOwnedModel
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'limit_amount' => 'decimal:4',
            'alert_80_notified_at' => 'datetime',
            'alert_90_notified_at' => 'datetime',
            'alert_100_notified_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Budget, $this>
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
