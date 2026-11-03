<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Debt;
use App\Models\JournalEntry;
use App\Models\SavingsGoal;
use App\Models\User;
use App\Services\Finance\AuditLoggerService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateAhorviaBackupCommand extends Command
{
    protected $signature = 'ahorvia:backup
                            {--user= : ID o correo electrónico del usuario a respaldar (omite para respaldar todos)}
                            {--retention=7 : Días de retención para respaldos antiguos}';

    protected $description = 'Genera un respaldo contable estructurado de Ahorvia en storage/app/private/backups';

    public function handle(AuditLoggerService $auditLogger): int
    {
        $this->info('Iniciando proceso de respaldo contable de Ahorvia...');

        $userFilter = $this->option('user');
        $retentionDays = max(1, (int) $this->option('retention'));

        $usersQuery = User::query();
        if ($userFilter) {
            $usersQuery->where(function ($q) use ($userFilter) {
                if (is_numeric($userFilter)) {
                    $q->where('id', (int) $userFilter);
                } else {
                    $q->where('email', mb_strtolower(trim((string) $userFilter)));
                }
            });
        }

        $users = $usersQuery->get();
        if ($users->isEmpty()) {
            $this->error('No se encontraron usuarios para respaldar.');

            return self::FAILURE;
        }

        $backupDir = storage_path('app/private/backups');
        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true, true);
        }

        $now = CarbonImmutable::now();
        $timestamp = $now->format('Y-m-d_His');
        $fileName = "ahorvia_backup_{$timestamp}.json";
        $filePath = "{$backupDir}/{$fileName}";

        $backupPayload = [
            'app' => 'Ahorvia — Finanzas Personales',
            'version' => '1.0',
            'created_at' => $now->toIso8601String(),
            'users_count' => $users->count(),
            'users' => [],
        ];

        foreach ($users as $user) {
            $this->line("Procesando datos contables del usuario: {$user->email} (#{$user->id})");

            $accounts = Account::query()->ownedBy($user)->get()->toArray();
            $categories = Category::query()->ownedBy($user)->get()->toArray();
            $journalEntries = JournalEntry::query()->ownedBy($user)->with(['lines'])->get()->toArray();
            $budgets = Budget::query()->ownedBy($user)->with(['lines'])->get()->toArray();
            $debts = Debt::query()->ownedBy($user)->with(['payments'])->get()->toArray();
            $savingsGoals = SavingsGoal::query()->ownedBy($user)->with(['contributions'])->get()->toArray();
            $auditLogs = AuditLog::query()->where('user_id', $user->id)->get()->toArray();

            $backupPayload['users'][] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'currency_code' => $user->currency_code,
                'accounts' => $accounts,
                'categories' => $categories,
                'journal_entries' => $journalEntries,
                'budgets' => $budgets,
                'debts' => $debts,
                'savings_goals' => $savingsGoals,
                'audit_logs' => $auditLogs,
            ];

            $auditLogger->log(
                user: $user,
                event: 'backup_created',
                auditable: $user,
                newValues: [
                    'file' => $fileName,
                    'created_at' => $now->toIso8601String(),
                ]
            );
        }

        $jsonEncoded = json_encode($backupPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($jsonEncoded === false) {
            $this->error('Error al serializar los datos del respaldo a JSON.');

            return self::FAILURE;
        }

        File::put($filePath, $jsonEncoded);
        $fileSizeKb = round(filesize($filePath) / 1024, 2);

        $this->info("Respaldo contable generado exitosamente: [{$fileName}] ({$fileSizeKb} KB)");

        // Limpieza de respaldos antiguos según retención
        $this->pruneOldBackups($backupDir, $retentionDays);

        return self::SUCCESS;
    }

    private function pruneOldBackups(string $backupDir, int $retentionDays): void
    {
        $files = File::files($backupDir);
        $cutoff = CarbonImmutable::now()->subDays($retentionDays);
        $prunedCount = 0;

        foreach ($files as $file) {
            if ($file->getExtension() !== 'json' || ! str_starts_with($file->getFilename(), 'ahorvia_backup_')) {
                continue;
            }

            $lastModified = CarbonImmutable::createFromTimestamp($file->getMTime());
            if ($lastModified->lessThan($cutoff)) {
                File::delete($file->getPathname());
                $prunedCount++;
            }
        }

        if ($prunedCount > 0) {
            $this->comment("Se eliminaron {$prunedCount} archivo(s) de respaldo anteriores a {$retentionDays} días.");
        }
    }
}
