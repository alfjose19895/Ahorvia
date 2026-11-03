<?php

declare(strict_types=1);

namespace App\Http\Requests\Finance;

use App\Models\Debt;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class StoreDebtPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->can('create', Debt::class);
    }

    /**
     * @return array<string, array<int, ValidationRule|Stringable|string>>
     */
    public function rules(): array
    {
        $userId = $this->user()?->getKey();

        return [
            'debt_id' => [
                'required',
                'integer',
                Rule::exists('debts', 'id')->where('user_id', $userId),
            ],
            'source_account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where('user_id', $userId)->where('is_active', true),
            ],
            'payment_date' => ['required', 'date'],
            'principal_amount' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'interest_amount' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'fee_amount' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'total_amount' => ['required', 'regex:/^\d+(\.\d{1,4})?$/', 'not_in:0,0.0,0.00,0.000,0.0000'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ];
    }
}
