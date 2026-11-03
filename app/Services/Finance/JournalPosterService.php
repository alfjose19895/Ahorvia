<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Data\Finance\JournalLineInputData;
use App\Data\Finance\PostJournalEntryData;
use App\Enums\EntryStatus;
use App\Exceptions\Finance\CrossTenantOwnershipException;
use App\Exceptions\Finance\FinancialDomainException;
use App\Exceptions\Finance\UnbalancedJournalEntryException;
use App\Models\Account;
use App\Models\Category;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\User;
use App\Support\DecimalMath;
use Illuminate\Support\Facades\DB;

class JournalPosterService
{
    public function __construct(
        private readonly AuditLoggerService $auditLogger,
    ) {}

    /**
     * Registra un asiento contable confirmado por partida doble simplificada
     * garantizando atomicidad ACID, bloqueo pesimista e idempotencia por usuario.
     */
    public function post(User $user, PostJournalEntryData $data): JournalEntry
    {
        $this->validateLinesAndBalance($data->lines);

        return DB::transaction(function () use ($user, $data): JournalEntry {
            // Bloqueo pesimista sobre el propietario para serializar idempotencia y numeración secuencial
            User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            $idempotencyKey = $data->idempotencyKey !== null && trim($data->idempotencyKey) !== ''
                ? trim($data->idempotencyKey)
                : null;

            if ($idempotencyKey !== null) {
                $existing = JournalEntry::query()
                    ->ownedBy($user)
                    ->where('idempotency_key', $idempotencyKey)
                    ->with('lines')
                    ->first();

                if ($existing !== null) {
                    return $existing;
                }
            }

            $currency = strtoupper($data->currencyCode ?: ($user->currency_code ?: 'USD'));
            $this->assertOwnershipAndCurrency($user, $data->lines, $currency);

            $entryNumber = $this->nextEntryNumber($user, $data->entryDate->format('Ym'));

            $entry = JournalEntry::query()->create([
                'user_id' => $user->getKey(),
                'entry_number' => $entryNumber,
                'entry_date' => $data->entryDate->toDateString(),
                'movement_type' => $data->movementType,
                'status' => EntryStatus::Posted,
                'currency_code' => $currency,
                'description' => trim($data->description),
                'reference' => $data->reference !== null ? trim($data->reference) : null,
                'idempotency_key' => $idempotencyKey,
                'posted_at' => now(),
                'metadata' => $data->metadata,
            ]);

            foreach ($data->lines as $index => $lineInput) {
                JournalLine::query()->create([
                    'journal_entry_id' => $entry->getKey(),
                    'user_id' => $user->getKey(),
                    'account_id' => $lineInput->accountId,
                    'category_id' => $lineInput->categoryId,
                    'line_order' => $index + 1,
                    'debit' => $lineInput->debit,
                    'credit' => $lineInput->credit,
                    'memo' => $lineInput->memo,
                ]);
            }

            $loadedEntry = $entry->load('lines');

            if (! $loadedEntry->isBalanced()) {
                throw new UnbalancedJournalEntryException('El asiento contable persistido no cumple el balance de partida doble.');
            }

            $this->auditLogger->log(
                user: $user,
                event: 'journal_entry.posted',
                auditable: $loadedEntry,
                newValues: [
                    'entry_number' => $loadedEntry->entry_number,
                    'movement_type' => $loadedEntry->movement_type->value,
                    'total_debits' => $loadedEntry->totalDebits(),
                    'total_credits' => $loadedEntry->totalCredits(),
                ]
            );

            return $loadedEntry;
        });
    }

    /**
     * @param  list<JournalLineInputData>  $lines
     */
    private function validateLinesAndBalance(array $lines): void
    {
        if (count($lines) < 2) {
            throw new UnbalancedJournalEntryException(
                'Todo asiento contable confirmado debe contener al menos dos líneas.'
            );
        }

        $totalDebits = DecimalMath::ZERO;
        $totalCredits = DecimalMath::ZERO;

        foreach ($lines as $line) {
            $totalDebits = DecimalMath::add($totalDebits, $line->debit);
            $totalCredits = DecimalMath::add($totalCredits, $line->credit);
        }

        if (DecimalMath::compare($totalDebits, DecimalMath::ZERO) <= 0) {
            throw new UnbalancedJournalEntryException('El importe total del asiento contable debe ser mayor a cero.');
        }

        if (! DecimalMath::equals($totalDebits, $totalCredits)) {
            throw new UnbalancedJournalEntryException(
                "Asiento desbalanceado: débitos [{$totalDebits}] difieren de créditos [{$totalCredits}]."
            );
        }
    }

    /**
     * @param  list<JournalLineInputData>  $lines
     */
    private function assertOwnershipAndCurrency(User $user, array $lines, string $currency): void
    {
        $accountIds = array_values(array_unique(array_map(
            static fn (JournalLineInputData $line): int => $line->accountId,
            $lines
        )));

        $accounts = Account::query()
            ->whereIn('id', $accountIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($accounts->count() !== count($accountIds)) {
            throw new CrossTenantOwnershipException('Una o más cuentas referenciadas en el asiento no existen.');
        }

        foreach ($accounts as $account) {
            if (! $account->isOwnedBy($user)) {
                throw new CrossTenantOwnershipException('Intento de registrar movimiento sobre una cuenta ajena al usuario autorizado.');
            }

            if (! $account->is_active) {
                throw new FinancialDomainException("La cuenta [{$account->name}] se encuentra inactiva.");
            }

            if (strtoupper($account->currency_code) !== $currency) {
                throw new FinancialDomainException(
                    "La moneda de la cuenta [{$account->name}] ({$account->currency_code}) no coincide con la moneda del asiento ({$currency})."
                );
            }
        }

        $categoryIds = array_values(array_unique(array_filter(array_map(
            static fn (JournalLineInputData $line): ?int => $line->categoryId,
            $lines
        ))));

        if ($categoryIds !== []) {
            $ownedCategoriesCount = Category::query()
                ->ownedBy($user)
                ->whereIn('id', $categoryIds)
                ->count();

            if ($ownedCategoriesCount !== count($categoryIds)) {
                throw new CrossTenantOwnershipException('Una o más categorías referenciadas no pertenecen al usuario autorizado.');
            }
        }
    }

    private function nextEntryNumber(User $user, string $yearMonth): string
    {
        $prefix = "JE-{$yearMonth}-";

        $count = JournalEntry::query()
            ->ownedBy($user)
            ->where('entry_number', 'like', "{$prefix}%")
            ->count();

        return sprintf('%s%06d', $prefix, $count + 1);
    }
}
