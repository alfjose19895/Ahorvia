<?php

declare(strict_types=1);

namespace App\Actions\Transactions;

use App\Data\Finance\JournalLineInputData;
use App\Data\Finance\PostJournalEntryData;
use App\Enums\MovementType;
use App\Exceptions\Finance\CrossTenantOwnershipException;
use App\Exceptions\Finance\FinancialDomainException;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\Finance\JournalPosterService;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;

class RecordTransferAction
{
    public function __construct(
        private readonly JournalPosterService $journalPoster,
    ) {}

    /**
     * @param  array{
     *     source_account_id: int,
     *     destination_account_id: int,
     *     amount: string|int,
     *     entry_date?: CarbonImmutable|string|null,
     *     description: string,
     *     reference?: string|null,
     *     idempotency_key?: string|null,
     *     metadata?: array<string, mixed>|null,
     * }  $attributes
     */
    public function execute(User $user, array $attributes): JournalEntry
    {
        $amount = DecimalMath::normalize($attributes['amount']);

        if (DecimalMath::compare($amount, DecimalMath::ZERO) <= 0) {
            throw new FinancialDomainException('El importe de la transferencia debe ser mayor a cero.');
        }

        $sourceId = (int) $attributes['source_account_id'];
        $destId = (int) $attributes['destination_account_id'];

        if ($sourceId === $destId) {
            throw new FinancialDomainException('La cuenta de origen y destino deben ser distintas para registrar una transferencia.');
        }

        $sourceAccount = Account::query()->findOrFail($sourceId);
        if (! $sourceAccount->isOwnedBy($user)) {
            throw new CrossTenantOwnershipException('Intento de transferir desde una cuenta ajena al usuario autorizado.');
        }

        $destinationAccount = Account::query()->findOrFail($destId);
        if (! $destinationAccount->isOwnedBy($user)) {
            throw new CrossTenantOwnershipException('Intento de transferir hacia una cuenta ajena al usuario autorizado.');
        }

        $currency = strtoupper($sourceAccount->currency_code);

        if (strtoupper($destinationAccount->currency_code) !== $currency) {
            throw new FinancialDomainException(
                "Las cuentas deben tener la misma moneda para transferencias ({$currency} vs {$destinationAccount->currency_code})."
            );
        }

        $entryDate = isset($attributes['entry_date'])
            ? ($attributes['entry_date'] instanceof CarbonImmutable
                ? $attributes['entry_date']
                : CarbonImmutable::parse((string) $attributes['entry_date']))
            : CarbonImmutable::now($user->timezone ?: 'UTC');

        $lines = [
            new JournalLineInputData(
                accountId: $destId,
                debit: $amount,
                credit: DecimalMath::ZERO,
                memo: "Transferencia recibida desde {$sourceAccount->name}",
            ),
            new JournalLineInputData(
                accountId: $sourceId,
                debit: DecimalMath::ZERO,
                credit: $amount,
                memo: "Transferencia enviada hacia {$destinationAccount->name}",
            ),
        ];

        return $this->journalPoster->post($user, new PostJournalEntryData(
            entryDate: $entryDate,
            movementType: MovementType::Transfer,
            description: trim($attributes['description']),
            lines: $lines,
            currencyCode: $currency,
            reference: isset($attributes['reference']) ? trim((string) $attributes['reference']) : null,
            idempotencyKey: isset($attributes['idempotency_key']) ? trim((string) $attributes['idempotency_key']) : null,
            metadata: $attributes['metadata'] ?? null,
        ));
    }
}
