<?php

declare(strict_types=1);

namespace App\Data\Finance;

use App\Enums\MovementType;
use Carbon\CarbonImmutable;

final readonly class PostJournalEntryData
{
    /**
     * @param  list<JournalLineInputData>  $lines
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public CarbonImmutable $entryDate,
        public MovementType $movementType,
        public string $description,
        public array $lines,
        public ?string $currencyCode = null,
        public ?string $reference = null,
        public ?string $idempotencyKey = null,
        public ?array $metadata = null,
    ) {}
}
