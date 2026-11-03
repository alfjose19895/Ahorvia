<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\JournalEntry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportLedgerCommand extends Command
{
    protected $signature = 'ahorvia:export-ledger
                            {user : ID o correo electrónico del usuario}
                            {--format=csv : Formato de exportación (csv o json)}
                            {--from= : Fecha inicial YYYY-MM-DD}
                            {--to= : Fecha final YYYY-MM-DD}
                            {--output= : Ruta específica del archivo destino}';

    protected $description = 'Exporta el Libro Diario contable del usuario a CSV o JSON para auditoría o archivo.';

    public function handle(): int
    {
        $userInput = (string) $this->argument('user');
        $format = mb_strtolower((string) $this->option('format'));
        $fromDate = $this->option('from');
        $toDate = $this->option('to');
        $outputPath = $this->option('output');

        if (! in_array($format, ['csv', 'json'], true)) {
            $this->error('Formato no válido. Debe ser "csv" o "json".');

            return self::FAILURE;
        }

        /** @var User|null $user */
        $user = User::query()
            ->where(function ($q) use ($userInput) {
                if (is_numeric($userInput)) {
                    $q->where('id', (int) $userInput);
                } else {
                    $q->where('email', mb_strtolower(trim($userInput)));
                }
            })
            ->first();

        if ($user === null) {
            $this->error("No se encontró ningún usuario con el identificador [{$userInput}].");

            return self::FAILURE;
        }

        $query = JournalEntry::query()
            ->ownedBy($user)
            ->with(['lines.account', 'lines.category'])
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc');

        if ($fromDate) {
            $query->where('entry_date', '>=', (string) $fromDate);
        }

        if ($toDate) {
            $query->where('entry_date', '<=', (string) $toDate);
        }

        $entries = $query->get();
        if ($entries->isEmpty()) {
            $this->warn('No se encontraron asientos contables en el rango especificado.');

            return self::SUCCESS;
        }

        $exportDir = storage_path('app/private/exports');
        if (! File::isDirectory($exportDir)) {
            File::makeDirectory($exportDir, 0755, true, true);
        }

        $now = CarbonImmutable::now();
        $targetFile = $outputPath ?: "{$exportDir}/ahorvia_ledger_{$user->id}_{$now->format('Ymd_His')}.{$format}";

        if ($format === 'csv') {
            $handle = fopen($targetFile, 'w');
            if ($handle === false) {
                $this->error("No se pudo abrir el archivo para escritura: {$targetFile}");

                return self::FAILURE;
            }

            // UTF-8 BOM para compatibilidad con Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Cabecera CSV
            fputcsv($handle, [
                'Numero_Asiento',
                'Fecha',
                'Tipo_Movimiento',
                'Estado',
                'Cuenta',
                'Naturaleza_Cuenta',
                'Categoria',
                'Debito',
                'Credito',
                'Moneda',
                'Descripcion',
                'Referencia',
            ]);

            $totalLines = 0;
            foreach ($entries as $entry) {
                foreach ($entry->lines as $line) {
                    fputcsv($handle, [
                        $entry->entry_number,
                        $entry->entry_date->toDateString(),
                        $entry->movement_type->value,
                        $entry->status->value,
                        $line->account ? $line->account->name : 'N/A',
                        $line->account ? $line->account->ledger_nature->value : 'N/A',
                        $line->category ? $line->category->name : '',
                        (string) $line->debit,
                        (string) $line->credit,
                        $entry->currency_code,
                        $entry->description,
                        $entry->reference ?? '',
                    ]);
                    $totalLines++;
                }
            }

            fclose($handle);
            $this->info("Libro Diario exportado a CSV exitosamente: [{$targetFile}] ({$entries->count()} asientos, {$totalLines} líneas contables).");
        } else {
            $data = [
                'app' => 'Ahorvia — Finanzas Personales',
                'exported_at' => $now->toIso8601String(),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'entries_count' => $entries->count(),
                'entries' => $entries->toArray(),
            ];

            File::put($targetFile, (string) json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info("Libro Diario exportado a JSON exitosamente: [{$targetFile}] ({$entries->count()} asientos).");
        }

        return self::SUCCESS;
    }
}
