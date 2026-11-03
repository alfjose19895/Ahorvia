<?php

declare(strict_types=1);

namespace App\Actions\Accounts;

use App\Data\Finance\JournalLineInputData;
use App\Data\Finance\PostJournalEntryData;
use App\Enums\AccountType;
use App\Enums\MovementType;
use App\Exceptions\Finance\FinancialDomainException;
use App\Models\Account;
use App\Models\User;
use App\Services\Finance\JournalPosterService;
use App\Services\Finance\LedgerBootstrapService;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CreateAccountAction
{
    public function __construct(
        private readonly JournalPosterService $journalPoster,
        private readonly LedgerBootstrapService $ledgerBootstrap,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     type: AccountType,
     *     currency_code?: string,
     *     institution_name?: string|null,
     *     account_number_last4?: string|null,
     *     opening_balance?: string|int|null,
     *     credit_limit?: string|int|null,
     *     billing_cutoff_day?: int|null,
     *     payment_due_day?: int|null,
     *     interest_rate_annual?: string|int|null,
     *     include_in_net_worth?: bool,
     *     notes?: string|null,
     *     idempotency_key?: string|null,
     * }  $attributes
     */
    public function execute(User $user, array $attributes): Account
    {
        /** @var AccountType $type */
        $type = $attributes['type'];

        if (! $type->isUserSelectable()) {
            throw new FinancialDomainException("El tipo de cuenta [{$type->value}] está reservado para el sistema contable.");
        }

        $currency = strtoupper($attributes['currency_code'] ?? ($user->currency_code ?: 'USD'));
        $openingBalanceRaw = $attributes['opening_balance'] ?? '0.0000';
        $openingBalance = DecimalMath::normalize($openingBalanceRaw !== '' ? (string) $openingBalanceRaw : '0.0000');

        if (DecimalMath::isNegative($openingBalance)) {
            throw new FinancialDomainException('El saldo inicial de la cuenta no puede ser un valor negativo.');
        }

        $creditLimit = null;
        if (isset($attributes['credit_limit']) && $attributes['credit_limit'] !== '') {
            $creditLimit = DecimalMath::normalize($attributes['credit_limit']);
        }

        $interestRate = '0.0000';
        if (isset($attributes['interest_rate_annual']) && $attributes['interest_rate_annual'] !== '') {
            $interestRate = DecimalMath::normalize($attributes['interest_rate_annual']);
        }

        return DB::transaction(function () use ($user, $type, $currency, $openingBalance, $creditLimit, $interestRate, $attributes): Account {
            $account = Account::query()->create([
                'user_id' => $user->getKey(),
                'name' => trim($attributes['name']),
                'type' => $type,
                'ledger_nature' => $type->ledgerNature(),
                'currency_code' => $currency,
                'institution_name' => isset($attributes['institution_name']) ? trim((string) $attributes['institution_name']) : null,
                'account_number_last4' => isset($attributes['account_number_last4']) ? trim((string) $attributes['account_number_last4']) : null,
                'credit_limit' => $creditLimit,
                'billing_cutoff_day' => $attributes['billing_cutoff_day'] ?? null,
                'payment_due_day' => $attributes['payment_due_day'] ?? null,
                'interest_rate_annual' => $interestRate,
                'is_active' => true,
                'is_system' => false,
                'include_in_net_worth' => $attributes['include_in_net_worth'] ?? true,
                'notes' => isset($attributes['notes']) ? trim((string) $attributes['notes']) : null,
            ]);

            if (DecimalMath::compare($openingBalance, DecimalMath::ZERO) > 0) {
                $equityAccount = $this->ledgerBootstrap->systemEquityAccount($user, $currency);

                $lines = $account->ledger_nature->isDebitNormal()
                    ? [
                        new JournalLineInputData(
                            accountId: (int) $account->getKey(),
                            debit: $openingBalance,
                            credit: DecimalMath::ZERO,
                            memo: 'Saldo inicial de apertura',
                        ),
                        new JournalLineInputData(
                            accountId: (int) $equityAccount->getKey(),
                            debit: DecimalMath::ZERO,
                            credit: $openingBalance,
                            memo: "Contrapartida de saldo inicial: {$account->name}",
                        ),
                    ]
                    : [
                        new JournalLineInputData(
                            accountId: (int) $equityAccount->getKey(),
                            debit: $openingBalance,
                            credit: DecimalMath::ZERO,
                            memo: "Contrapartida de saldo inicial: {$account->name}",
                        ),
                        new JournalLineInputData(
                            accountId: (int) $account->getKey(),
                            debit: DecimalMath::ZERO,
                            credit: $openingBalance,
                            memo: 'Saldo inicial de pasivo',
                        ),
                    ];

                $idempotencyKey = isset($attributes['idempotency_key']) && trim((string) $attributes['idempotency_key']) !== ''
                    ? 'open_acc_'.$account->getKey().'_'.trim((string) $attributes['idempotency_key'])
                    : 'open_acc_'.$account->getKey();

                $this->journalPoster->post($user, new PostJournalEntryData(
                    entryDate: CarbonImmutable::now($user->timezone ?: 'UTC'),
                    movementType: MovementType::OpeningBalance,
                    description: "Registro de saldo inicial en {$account->name}",
                    lines: $lines,
                    currencyCode: $currency,
                    reference: 'SALDO-INICIAL',
                    idempotencyKey: $idempotencyKey,
                    metadata: ['account_id' => $account->getKey()],
                ));
            }

            return $account;
        });
    }
}
