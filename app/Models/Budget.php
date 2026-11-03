<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'year',
    'month',
    'currency_code',
    'name',
    'total_limit',
    'notes',
    'is_active',
])]
class Budget extends UserOwnedModel
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency_code' => 'USD',
        'total_limit' => '0.0000',
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'total_limit' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<BudgetLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(BudgetLine::class);
    }
}
