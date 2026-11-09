<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property bool $is_system
 * @property bool $is_active
 */
#[Fillable([
    'user_id',
    'name',
    'slug',
    'is_system',
    'is_active',
])]
class DebtTypeCatalog extends UserOwnedModel
{
    /**
     * @var string
     */
    protected $table = 'debt_types';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_system' => false,
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
