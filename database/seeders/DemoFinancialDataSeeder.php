<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Accounts\CreateAccountAction;
use App\Actions\Budgets\UpsertMonthlyBudgetAction;
use App\Actions\Debts\CreateDebtAction;
use App\Actions\Debts\RecordDebtPaymentAction;
use App\Actions\Savings\CreateSavingsGoalAction;
use App\Actions\Savings\RecordSavingsContributionAction;
use App\Actions\Transactions\RecordExpenseAction;
use App\Actions\Transactions\RecordIncomeAction;
use App\Actions\Transactions\RecordTransferAction;
use App\Enums\AccessStatus;
use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Enums\DebtType;
use App\Enums\SavingsContributionType;
use App\Enums\SavingsMode;
use App\Models\Category;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoFinancialDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = CarbonImmutable::now();

        /** @var User $user */
        $user = User::query()->firstOrCreate(
            ['email' => 'demo@ahorvia.com'],
            [
                'name' => 'Usuario Demo Ahorvia',
                'email_verified_at' => $now,
                'password' => Hash::make('AhorviaDemo2026!'),
                'currency_code' => 'USD',
                'locale' => 'es',
                'is_active' => true,
                'access_status' => AccessStatus::Active,
            ]
        );

        $createAccountAction = app(CreateAccountAction::class);
        $recordIncomeAction = app(RecordIncomeAction::class);
        $recordExpenseAction = app(RecordExpenseAction::class);
        $recordTransferAction = app(RecordTransferAction::class);
        $upsertBudgetAction = app(UpsertMonthlyBudgetAction::class);
        $createDebtAction = app(CreateDebtAction::class);
        $recordPaymentAction = app(RecordDebtPaymentAction::class);
        $createGoalAction = app(CreateSavingsGoalAction::class);
        $recordContributionAction = app(RecordSavingsContributionAction::class);

        // 1. Cuentas principales de activo
        $checking = $createAccountAction->execute($user, [
            'name' => 'Cuenta Corriente Principal',
            'type' => AccountType::Checking,
            'opening_balance' => '3500.00',
            'institution_name' => 'Banco Santander',
        ]);

        $savings = $createAccountAction->execute($user, [
            'name' => 'Cuenta de Ahorros',
            'type' => AccountType::Savings,
            'opening_balance' => '1200.00',
            'institution_name' => 'Banco Santander',
        ]);

        $cash = $createAccountAction->execute($user, [
            'name' => 'Efectivo en Billetera',
            'type' => AccountType::Cash,
            'opening_balance' => '250.00',
        ]);

        // 2. Categorías
        $catSalary = Category::query()->firstOrCreate(
            ['user_id' => $user->id, 'slug' => 'sueldo'],
            ['name' => 'Sueldo / Nómina', 'type' => CategoryType::Income, 'color' => '#10B981', 'is_active' => true]
        );

        $catFreelance = Category::query()->firstOrCreate(
            ['user_id' => $user->id, 'slug' => 'consultoria'],
            ['name' => 'Ingresos Freelance', 'type' => CategoryType::Income, 'color' => '#3B82F6', 'is_active' => true]
        );

        $catFood = Category::query()->firstOrCreate(
            ['user_id' => $user->id, 'slug' => 'alimentacion'],
            ['name' => 'Alimentación y Supermercado', 'type' => CategoryType::Expense, 'color' => '#10B981', 'is_active' => true]
        );

        $catHousing = Category::query()->firstOrCreate(
            ['user_id' => $user->id, 'slug' => 'vivienda'],
            ['name' => 'Vivienda y Servicios', 'type' => CategoryType::Expense, 'color' => '#F59E0B', 'is_active' => true]
        );

        $catTransport = Category::query()->firstOrCreate(
            ['user_id' => $user->id, 'slug' => 'transporte'],
            ['name' => 'Transporte y Movilidad', 'type' => CategoryType::Expense, 'color' => '#64748B', 'is_active' => true]
        );

        $catLeisure = Category::query()->firstOrCreate(
            ['user_id' => $user->id, 'slug' => 'ocio'],
            ['name' => 'Ocio y Restaurantes', 'type' => CategoryType::Expense, 'color' => '#EC4899', 'is_active' => true]
        );

        // 3. Movimientos del mes actual
        $recordIncomeAction->execute($user, [
            'account_id' => $checking->id,
            'category_id' => $catSalary->id,
            'amount' => '3200.00',
            'entry_date' => $now->startOfMonth()->toDateString(),
            'description' => 'Pago sueldo mensual',
        ]);

        $recordIncomeAction->execute($user, [
            'account_id' => $checking->id,
            'category_id' => $catFreelance->id,
            'amount' => '650.00',
            'entry_date' => $now->startOfMonth()->addDays(5)->toDateString(),
            'description' => 'Servicios de consultoría externa',
        ]);

        $recordExpenseAction->execute($user, [
            'account_id' => $checking->id,
            'category_id' => $catHousing->id,
            'amount' => '850.00',
            'entry_date' => $now->startOfMonth()->addDays(2)->toDateString(),
            'description' => 'Alquiler departamento',
        ]);

        $recordExpenseAction->execute($user, [
            'account_id' => $checking->id,
            'category_id' => $catFood->id,
            'amount' => '420.00',
            'entry_date' => $now->startOfMonth()->addDays(6)->toDateString(),
            'description' => 'Supermercado quincenal',
        ]);

        $recordExpenseAction->execute($user, [
            'account_id' => $cash->id,
            'category_id' => $catLeisure->id,
            'amount' => '75.00',
            'entry_date' => $now->startOfMonth()->addDays(8)->toDateString(),
            'description' => 'Cena fin de semana',
        ]);

        $recordTransferAction->execute($user, [
            'source_account_id' => $checking->id,
            'destination_account_id' => $savings->id,
            'amount' => '300.00',
            'entry_date' => $now->startOfMonth()->addDays(10)->toDateString(),
            'description' => 'Traspaso a cuenta de ahorro',
        ]);

        // 4. Presupuesto mensual
        $upsertBudgetAction->execute($user, [
            'year' => $now->year,
            'month' => $now->month,
            'lines' => [
                ['category_id' => $catFood->id, 'limit_amount' => '500.00'],
                ['category_id' => $catHousing->id, 'limit_amount' => '900.00'],
                ['category_id' => $catTransport->id, 'limit_amount' => '200.00'],
                ['category_id' => $catLeisure->id, 'limit_amount' => '150.00'],
            ],
        ]);

        // 5. Tarjeta de crédito
        $card = $createDebtAction->execute($user, [
            'name' => 'Visa Signature Oro',
            'type' => DebtType::CreditCard,
            'creditor_name' => 'Banco Santander',
            'credit_limit' => '3000.00',
            'original_principal' => '450.00',
            'interest_rate_annual' => '24.5',
            'cutoff_day' => 12,
            'payment_due_day' => 28,
        ]);

        // 6. Préstamo
        $loan = $createDebtAction->execute($user, [
            'name' => 'Préstamo Equipamiento',
            'type' => DebtType::BankLoan,
            'creditor_name' => 'Banco Santander',
            'original_principal' => '2400.00',
            'interest_rate_annual' => '12.0',
            'due_date' => $now->addMonths(12)->toDateString(),
            'payment_due_day' => 15,
        ]);

        $recordPaymentAction->execute($user, [
            'debt_id' => $loan->id,
            'source_account_id' => $checking->id,
            'payment_date' => $now->startOfMonth()->addDays(12)->toDateString(),
            'principal_amount' => '200.00',
            'interest_amount' => '24.00',
            'fee_amount' => '2.50',
            'total_amount' => '226.50',
            'reference' => 'CUOTA-01',
        ]);

        // 7. Planificador de Ahorro a 6 Meses
        $goal = $createGoalAction->execute($user, [
            'name' => 'Fondo de Emergencia Familiar',
            'target_amount' => '1800.00',
            'initial_amount' => '300.00',
            'duration_months' => 6,
            'mode' => SavingsMode::Adjustable,
            'linked_account_id' => $savings->id,
        ]);

        $recordContributionAction->execute($user, [
            'savings_goal_id' => $goal->id,
            'contribution_type' => SavingsContributionType::Transfer,
            'source_account_id' => $checking->id,
            'destination_account_id' => $savings->id,
            'amount' => '250.00',
            'contribution_date' => $now->startOfMonth()->addDays(14)->toDateString(),
            'reference' => 'APORTE-01',
        ]);
    }
}

