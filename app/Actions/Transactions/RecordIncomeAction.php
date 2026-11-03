<?php

declare(strict_types=1);

namespace App\Actions\Transactions;

use App\Data\Finance\JournalLineInputData;
use App\Data\Finance\PostJournalEntryData;
use App\Enums\MovementType;
use App\Exceptions\Finance\CrossTenantOwnershipException;
use App\Exceptions\Finance\FinancialDomainException;
use App\Models\Account;
use App\Models\Category;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\Finance\JournalPosterService;
use App\Services\Finance\LedgerBootstrapService;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;

class RecordIncomeAction
{
    public function __construct(
        private readonly JournalPosterService $journalPoster,
        private readonly LedgerBootstrapService $ledgerBootstrap,
    ) {}

    /**
     * @param  array{
     *     account_id: int,
     *     category_id: int,
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
            throw new FinancialDomainException('El importe del ingreso debe ser mayor a cero.');
        }

        $account = Account::query()->findOrFail($attributes['account_id']);
        if (! $account->isOwnedBy($user)) {
            throw new CrossTenantOwnershipException('Intento de registrar movimiento sobre una cuenta ajena al usuario autorizado.');
        }

        $category = Category::query()->findOrFail($attributes['category_id']);
        if (! $category->isOwnedBy($user)) {
            throw new CrossTenantOwnershipException('Intento de registrar movimiento sobre una categoría ajena al usuario autorizado.');
        }

        $currency = strtoupper($account->currency_code);
        $systemIncomeAccount = $this->ledgerBootstrap->systemIncomeAccount($user, $currency);

        $entryDate = isset($attributes['entry_date'])
            ? ($attributes['entry_date'] instanceof CarbonImmutable
                ? $attributes['entry_date']
                : CarbonImmutable::parse((string) $attributes['entry_date']))
            : CarbonImmutable::now($user->timezone ?: 'UTC');

        $lines = [
            new JournalLineInputData(
                accountId: (int) $account->getKey(),
                debit: $amount,
                credit: DecimalMath::ZERO,
                categoryId: (int) $category->getKey(),
                memo: "Ingreso en {$account->name}",
            ),
            new JournalLineInputData(
                accountId: (int) $systemIncomeAccount->getKey(),
                debit: DecimalMath::ZERO,
                credit: $amount,
                categoryId: (int) $category->getKey(),
                memo: "Reconocimiento contable de ingreso: {$category->name}",
            ),
        ];

        return $this->journalPoster->post($user, new PostJournalEntryData(
            entryDate: $entryDate,
            movementType: MovementType::Income,
            description: trim($attributes['description']),
            lines: $lines,
            currencyCode: $currency,
            reference: isset($attributes['reference']) ? trim((string) $attributes['reference']) : null,
            idempotencyKey: isset($attributes['idempotency_key']) ? trim((string) $attributes['idempotency_key']) : null,
            metadata: $attributes['metadata'] ?? null,
        ));
    }
}
