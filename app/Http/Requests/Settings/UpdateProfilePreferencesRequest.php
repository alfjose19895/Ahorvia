<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class UpdateProfilePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->can('update', $user);
    }

    /**
     * @return array<string, array<int, ValidationRule|Stringable|string>>
     */
    public function rules(): array
    {
        /** @var array<string, mixed> $currencies */
        $currencies = config('ahorvia.finance.supported_currencies', []);
        $userId = $this->user()?->getKey();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'currency_code' => ['required', 'string', 'size:3', Rule::in(array_keys($currencies))],
            'timezone' => ['required', 'string', 'timezone:all'],
        ];
    }
}
