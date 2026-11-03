<?php

declare(strict_types=1);

namespace App\Enums;

enum MovementType: string
{
    case OpeningBalance = 'opening_balance';
    case Income = 'income';
    case Expense = 'expense';
    case Transfer = 'transfer';
    case CardPurchase = 'card_purchase';
    case CardPayment = 'card_payment';
    case LoanDisbursement = 'loan_disbursement';
    case DebtPayment = 'debt_payment';
    case Interest = 'interest';
    case Fee = 'fee';
    case SavingsContribution = 'savings_contribution';
    case ReconciliationAdjustment = 'reconciliation_adjustment';
    case Reversal = 'reversal';

    public function label(): string
    {
        return match ($this) {
            self::OpeningBalance => 'Saldo inicial',
            self::Income => 'Ingreso',
            self::Expense => 'Gasto',
            self::Transfer => 'Transferencia',
            self::CardPurchase => 'Compra con tarjeta',
            self::CardPayment => 'Pago de tarjeta',
            self::LoanDisbursement => 'Desembolso de préstamo',
            self::DebtPayment => 'Abono a deuda',
            self::Interest => 'Intereses',
            self::Fee => 'Comisiones',
            self::SavingsContribution => 'Aporte de ahorro',
            self::ReconciliationAdjustment => 'Ajuste de conciliación',
            self::Reversal => 'Reverso contable',
        };
    }
}
