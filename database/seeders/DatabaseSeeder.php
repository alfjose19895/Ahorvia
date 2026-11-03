<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AccessStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $ownerEmail = (string) env('AHORVIA_OWNER_EMAIL', '');
        $ownerPassword = (string) env('AHORVIA_OWNER_PASSWORD', '');

        if ($ownerEmail !== '' && $ownerPassword !== '') {
            User::query()->firstOrCreate(
                ['email' => mb_strtolower(trim($ownerEmail))],
                [
                    'name' => (string) env('AHORVIA_OWNER_NAME', 'Propietario Ahorvia'),
                    'email_verified_at' => now(),
                    'password' => Hash::make($ownerPassword),
                    'currency_code' => (string) config('ahorvia.finance.default_currency', 'USD'),
                    'locale' => 'es',
                    'is_active' => true,
                    'access_status' => AccessStatus::Active,
                ]
            );
        }
    }
}
