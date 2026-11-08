<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Enums\AccessStatus;
use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;
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

        $this->seedDefaultCategories($user);

        return $user;
    }

    private function seedDefaultCategories(User $user): void
    {
        $defaults = [
            // Ingresos
            ['name' => 'Sueldo / Nómina', 'slug' => 'sueldo', 'type' => CategoryType::Income, 'color' => '#10B981'],
            ['name' => 'Negocio / Freelance', 'slug' => 'negocio-freelance', 'type' => CategoryType::Income, 'color' => '#3B82F6'],
            ['name' => 'Rentas e Inversiones', 'slug' => 'inversiones', 'type' => CategoryType::Income, 'color' => '#8B5CF6'],
            ['name' => 'Otros Ingresos', 'slug' => 'otros-ingresos', 'type' => CategoryType::Income, 'color' => '#64748B'],

            // Gastos esenciales y estilo de vida
            ['name' => 'Alimentación y Supermercado', 'slug' => 'alimentacion', 'type' => CategoryType::Expense, 'color' => '#10B981'],
            ['name' => 'Vivienda y Servicios', 'slug' => 'vivienda', 'type' => CategoryType::Expense, 'color' => '#F59E0B'],
            ['name' => 'Transporte y Movilidad', 'slug' => 'transporte', 'type' => CategoryType::Expense, 'color' => '#64748B'],
            ['name' => 'Salud y Farmacia', 'slug' => 'salud', 'type' => CategoryType::Expense, 'color' => '#06B6D4'],
            ['name' => 'Educación y Libros', 'slug' => 'educacion', 'type' => CategoryType::Expense, 'color' => '#8B5CF6'],
            ['name' => 'Ocio y Restaurantes', 'slug' => 'ocio', 'type' => CategoryType::Expense, 'color' => '#EC4899'],
            ['name' => 'Financiero e Intereses', 'slug' => 'intereses-comisiones', 'type' => CategoryType::Expense, 'color' => '#EF4444'],
            ['name' => 'Otros Gastos', 'slug' => 'otros-gastos', 'type' => CategoryType::Expense, 'color' => '#94A3B8'],
        ];

        foreach ($defaults as $cat) {
            Category::query()->firstOrCreate(
                [
                    'user_id' => $user->getKey(),
                    'slug' => $cat['slug'],
                ],
                [
                    'name' => $cat['name'],
                    'type' => $cat['type'],
                    'color' => $cat['color'],
                    'is_system' => false,
                    'is_active' => true,
                ]
            );
        }
    }
}
