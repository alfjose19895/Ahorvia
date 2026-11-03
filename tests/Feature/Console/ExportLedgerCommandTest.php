<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Actions\Accounts\CreateAccountAction;
use App\Actions\Transactions\RecordIncomeAction;
use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ExportLedgerCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $exportDir = storage_path('app/private/exports');
        if (File::isDirectory($exportDir)) {
            File::deleteDirectory($exportDir);
        }

        parent::tearDown();
    }

    public function test_export_ledger_to_csv_generates_valid_accounting_rows(): void
    {
        $user = User::factory()->create(['email' => 'export_test@ahorvia.com']);

        $createAccountAction = $this->app->make(CreateAccountAction::class);
        $recordIncomeAction = $this->app->make(RecordIncomeAction::class);

        $account = $createAccountAction->execute($user, [
            'name' => 'Banco Débito',
            'type' => AccountType::Checking,
            'opening_balance' => '1000.00',
        ]);

        $category = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Sueldo',
            'slug' => 'sueldo',
            'type' => CategoryType::Income,
            'color' => '#10B981',
            'is_active' => true,
        ]);

        $recordIncomeAction->execute($user, [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => '2000.00',
            'entry_date' => now()->toDateString(),
            'description' => 'Pago nómina mensual',
            'reference' => 'NOM-01',
        ]);

        $this->artisan('ahorvia:export-ledger', [
            'user' => $user->email,
            '--format' => 'csv',
        ])
            ->expectsOutputToContain('Libro Diario exportado a CSV exitosamente')
            ->assertSuccessful();

        $exportDir = storage_path('app/private/exports');
        $files = File::files($exportDir);
        $this->assertCount(1, $files);

        $csvContent = (string) File::get($files[0]->getPathname());
        $this->assertStringContainsString('Numero_Asiento', $csvContent);
        $this->assertStringContainsString('Pago nómina mensual', $csvContent);
        $this->assertStringContainsString('2000.0000', $csvContent);
    }

    public function test_export_ledger_to_json_generates_valid_payload(): void
    {
        $user = User::factory()->create();

        $this->artisan('ahorvia:export-ledger', [
            'user' => $user->id,
            '--format' => 'json',
        ])->assertSuccessful();
    }

    public function test_export_ledger_fails_when_user_not_found(): void
    {
        $this->artisan('ahorvia:export-ledger', [
            'user' => 99999,
        ])
            ->expectsOutputToContain('No se encontró ningún usuario')
            ->assertFailed();
    }
}
