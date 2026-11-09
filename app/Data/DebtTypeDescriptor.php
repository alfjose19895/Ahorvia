<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\DebtType;
use Stringable;

final readonly class DebtTypeDescriptor implements Stringable
{
    public function __construct(
        public string $value,
        public string $label,
        public bool $isCreditCard = false,
    ) {}

    public static function fromValue(string $value, ?string $label = null): self
    {
        $enum = DebtType::tryFrom($value);
        if ($enum !== null) {
            return new self(
                value: $enum->value,
                label: $label ?? $enum->label(),
                isCreditCard: $enum === DebtType::CreditCard,
            );
        }

        return new self(
            value: $value,
            label: $label ?? ucwords(str_replace(['_', '-'], ' ', $value)),
            isCreditCard: $value === DebtType::CreditCard->value,
        );
    }

    public function label(): string
    {
        return $this->label;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
