<?php

declare(strict_types=1);

namespace App\Http\Requests\Finance;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->can('create', Account::class);
    }

    /**
     * @return array<string, array<int, ValidationRule|Stringable|string>>
     */
    public function rules(): array
    {
        /** @var array<string, mixed> $currencies */
        $currencies = config('ahorvia.finance.supported_currencies', []);
        $userCaseValues = array_map(static fn (AccountType $case): string => $case->value, AccountType::userCases());

        return [
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', Rule::in($userCaseValues)],
            'currency_code' => ['nullable', 'string', 'size:3', Rule::in(array_keys($currencies))],
            'institution_name' => ['nullable', 'string', 'max:100'],
            'account_number_last4' => ['nullable', 'string', 'digits:4'],
            'opening_balance' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'credit_limit' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'interest_rate_annual' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'billing_cutoff_day' => ['nullable', 'integer', 'between:1,31'],
            'payment_due_day' => ['nullable', 'integer', 'between:1,31'],
            'include_in_net_worth' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
