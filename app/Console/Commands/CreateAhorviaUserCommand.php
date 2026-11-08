<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\AccessStatus;
use App\Models\User;
use App\Services\Finance\LedgerBootstrapService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAhorviaUserCommand extends Command
{
    protected $signature = 'ahorvia:create-user
                            {--name= : Nombre completo del usuario}
                            {--email= : Correo electrónico}
                            {--password= : Contraseña segura}
                            {--currency=USD : Código ISO de moneda principal (ej. USD, EUR, PEN, MXN)}';

    protected $description = 'Crea un usuario autorizado en Ahorvia (útil con registro público deshabilitado).';

    public function handle(): int
    {
        $name = (string) ($this->option('name') ?: $this->ask('Nombre completo', 'Propietario Ahorvia'));
        $email = (string) ($this->option('email') ?: $this->ask('Correo electrónico'));
        $password = (string) ($this->option('password') ?: $this->secret('Contraseña (mínimo 8 caracteres)'));
        $currency = strtoupper((string) ($this->option('currency') ?: 'USD'));

        /** @var array<string, mixed> $currencies */
        $currencies = config('ahorvia.finance.supported_currencies', []);

        $validator = Validator::make([
            'name' => $name,
            'email' => mb_strtolower(trim($email)),
            'password' => $password,
            'currency_code' => $currency,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
            'currency_code' => ['required', 'string', 'size:3', 'in:'.implode(',', array_keys($currencies))],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = new User;
        $user->forceFill([
            'name' => trim($name),
            'email' => mb_strtolower(trim($email)),
            'email_verified_at' => now(),
            'password' => Hash::make($password),
            'currency_code' => $currency,
            'locale' => 'es',
            'is_active' => true,
            'access_status' => AccessStatus::Active,
        ])->save();

        app(LedgerBootstrapService::class)->ensureDefaultCategories($user);

        $this->info("Usuario [{$user->email}] creado y activado correctamente en Ahorvia.");

        return self::SUCCESS;
    }
}
