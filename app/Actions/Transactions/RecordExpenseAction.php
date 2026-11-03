<?php

declare(strict_types=1);

namespace App\Actions\Transactions;

use App\Data\Finance\JournalLineInputData;
use App\Data\Finance\PostJournalEntryData;
use App\Enums\AccountType;
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

class RecordExpenseAction
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
     *     movement_type?: MovementType|null,
     *     idempotency_key?: string|null,
     *     metadata?: array<string, mixed>|null,
     * }  $attributes
     */
    public function execute(User $user, array $attributes): JournalEntry
    {
        $amount = DecimalMath::normalize($attributes['amount']);

        if (DecimalMath::compare($amount, DecimalMath::ZERO) <= 0) {
            throw new FinancialDomainException('El importe del gasto debe ser mayor a cero.');
        }

        $account = Account::query()->findOrFail($attributes['account_id']);
        if (! $account->isOwnedBy($user)) {
            throw new CrossTenantOwnershipException('Intento de registrar gasto sobre una cuenta ajena al usuario autorizado.');
        }

        $category = Category::query()->findOrFail($attributes['category_id']);
        if (! $category->isOwnedBy($user)) {
            throw new CrossTenantOwnershipException('Intento de registrar gasto sobre una categoría ajena al usuario autorizado.');
        }

        $currency = strtoupper($account->currency_code);
        $systemExpenseAccount = $this->ledgerBootstrap->systemExpenseAccount($user, $currency);

        $movementType = $attributes['movement_type'] ?? (
            $account->type === AccountType::CreditCard
                ? MovementType::CardPurchase
                : MovementType::Expense
        );

        $entryDate = isset($attributes['entry_date'])
            ? ($attributes['entry_date'] instanceof CarbonImmutable
                ? $attributes['entry_date']
                : CarbonImmutable::parse((string) $attributes['entry_date']))
            : CarbonImmutable::now($user->timezone ?: 'UTC');

        $lines = [
            new JournalLineInputData(
                accountId: (int) $systemExpenseAccount->getKey(),
                debit: $amount,
                credit: DecimalMath::ZERO,
                categoryId: (int) $category->getKey(),
                memo: "Gasto en {$category->name}",
            ),
            new JournalLineInputData(
                accountId: (int) $account->getKey(),
                debit: DecimalMath::ZERO,
                credit: $amount,
                categoryId: (int) $category->getKey(),
                memo: "Salida/Cargo contable en {$account->name}",
            ),
        ];

        return $this->journalPoster->post($user, new PostJournalEntryData(
            entryDate: $entryDate,
            movementType: $movementType,
            description: trim($attributes['description']),
            lines: $lines,
            currencyCode: $currency,
            reference: isset($attributes['reference']) ? trim((string) $attributes['reference']) : null,
            idempotencyKey: isset($attributes['idempotency_key']) ? trim((string) $attributes['idempotency_key']) : null,
            metadata: $attributes['metadata'] ?? null,
        ));
    }
}
