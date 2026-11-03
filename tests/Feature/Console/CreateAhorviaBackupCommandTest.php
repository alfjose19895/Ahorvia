<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Actions\Accounts\CreateAccountAction;
use App\Actions\Transactions\RecordIncomeAction;
use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CreateAhorviaBackupCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $backupDir = storage_path('app/private/backups');
        if (File::isDirectory($backupDir)) {
            File::deleteDirectory($backupDir);
        }

        parent::tearDown();
    }

    public function test_backup_command_creates_valid_json_snapshot_of_financial_records(): void
    {
        $user = User::factory()->create([
            'email' => 'backup_test@ahorvia.com',
            'currency_code' => 'USD',
        ]);

        $createAccountAction = $this->app->make(CreateAccountAction::class);
        $recordIncomeAction = $this->app->make(RecordIncomeAction::class);

        $account = $createAccountAction->execute($user, [
            'name' => 'Cuenta Ahorro Respaldo',
            'type' => AccountType::Savings,
            'opening_balance' => '1500.00',
        ]);

        $category = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Consultoría',
            'slug' => 'consultoria',
            'type' => CategoryType::Income,
            'color' => '#10B981',
            'is_active' => true,
        ]);

        $recordIncomeAction->execute($user, [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => '800.00',
            'entry_date' => now()->toDateString(),
            'description' => 'Honorarios profesionales',
        ]);

        $this->artisan('ahorvia:backup', ['--user' => $user->id])
            ->expectsOutputToContain('Iniciando proceso de respaldo contable')
            ->expectsOutputToContain('Respaldo contable generado exitosamente')
            ->assertSuccessful();

        $backupDir = storage_path('app/private/backups');
        $this->assertTrue(File::isDirectory($backupDir));

        $files = File::files($backupDir);
        $this->assertCount(1, $files);

        $content = (string) File::get($files[0]->getPathname());
        $data = json_decode($content, true);

        $this->assertIsArray($data);
        $this->assertSame('Ahorvia — Finanzas Personales', $data['app']);
        $this->assertSame(1, $data['users_count']);
        $this->assertSame($user->id, $data['users'][0]['id']);
        $this->assertNotEmpty($data['users'][0]['accounts']);
        $this->assertNotEmpty($data['users'][0]['journal_entries']);
    }

    public function test_backup_command_prunes_older_backups_according_to_retention(): void
    {
        User::factory()->create();

        $backupDir = storage_path('app/private/backups');
        File::makeDirectory($backupDir, 0755, true, true);

        $oldFile = "{$backupDir}/ahorvia_backup_2025-01-01_120000.json";
        File::put($oldFile, '{}');
        touch($oldFile, CarbonImmutable::now()->subDays(15)->timestamp);

        $this->artisan('ahorvia:backup', ['--retention' => 7])
            ->assertSuccessful();

        $this->assertFileDoesNotExist($oldFile);
    }
}
