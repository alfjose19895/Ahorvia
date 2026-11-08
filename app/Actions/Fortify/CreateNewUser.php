<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Enums\AccessStatus;
use App\Models\User;
use App\Services\Finance\LedgerBootstrapService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        $supportedCurrencies = array_keys((array) config('ahorvia.finance.supported_currencies', ['USD' => 'USD', 'EUR' => 'EUR']));

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'currency_code' => ['nullable', 'string', 'size:3', Rule::in($supportedCurrencies)],
        ])->validate();

        $currency = isset($input['currency_code']) && trim($input['currency_code']) !== ''
            ? strtoupper(trim($input['currency_code']))
            : (string) config('ahorvia.finance.default_currency', 'USD');

        /** @var User $user */
        $user = User::query()->create([
            'name' => trim($input['name']),
            'email' => mb_strtolower(trim($input['email'])),
            'password' => Hash::make($input['password']),
            'currency_code' => $currency,
            'locale' => 'es',
            'is_active' => true,
            'access_status' => AccessStatus::Active,
        ]);

        $bootstrap = app(LedgerBootstrapService::class);
        $bootstrap->ensureDefaultCategories($user);
        $bootstrap->ensureDefaultInstitutions($user);

        return $user;
    }
}
