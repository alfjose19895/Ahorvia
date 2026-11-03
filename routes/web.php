<?php

declare(strict_types=1);

use App\Livewire\Accounts\AccountsIndex;
use App\Livewire\Budgets\BudgetsIndex;
use App\Livewire\Dashboard\FinancialOverview;
use App\Livewire\Debts\CardsIndex;
use App\Livewire\Debts\DebtsIndex;
use App\Livewire\Notifications\NotificationsIndex;
use App\Livewire\Reports\ReportsIndex;
use App\Livewire\Savings\SavingsPlannerIndex;
use App\Livewire\Settings\SettingsManager;
use App\Livewire\Transactions\TransactionsIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', static function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::view('/offline', 'pwa.offline')->name('pwa.offline');

Route::middleware(['auth', 'active.access'])->group(static function (): void {
    Route::get('/dashboard', FinancialOverview::class)->name('dashboard');
    Route::get('/cuentas', AccountsIndex::class)->name('accounts.index');
    Route::get('/movimientos', TransactionsIndex::class)->name('transactions.index');
    Route::get('/presupuestos', BudgetsIndex::class)->name('budgets.index');
    Route::get('/deudas', DebtsIndex::class)->name('debts.index');
    Route::get('/tarjetas', CardsIndex::class)->name('cards.index');
    Route::get('/ahorro', SavingsPlannerIndex::class)->name('savings.index');
    Route::get('/reportes', ReportsIndex::class)->name('reports.index');
    Route::get('/notificaciones', NotificationsIndex::class)->name('notifications.index');
    Route::get('/configuracion', SettingsManager::class)->name('settings.index');
});
