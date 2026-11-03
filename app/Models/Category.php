<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $parent_id
 * @property string $name
 * @property string $slug
 * @property CategoryType $type
 * @property string $color
 * @property string|null $icon
 * @property bool $is_system
 * @property bool $is_active
 */
#[Fillable([
    'user_id',
    'parent_id',
    'name',
    'slug',
    'type',
    'color',
    'icon',
    'is_system',
    'is_active',
])]
class Category extends UserOwnedModel
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'color' => '#10B981',
        'is_system' => false,
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CategoryType::class,
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<self, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany<JournalLine, $this>
     */
    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    /**
     * @return HasMany<BudgetLine, $this>
     */
    public function budgetLines(): HasMany
    {
        return $this->hasMany(BudgetLine::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeUserFacing(Builder $query): Builder
    {
        return $query->where('is_system', false);
    }
}
