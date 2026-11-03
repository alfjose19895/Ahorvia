<?php

declare(strict_types=1);

namespace App\Http\Requests\Finance;

use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->can('create', JournalEntry::class);
    }

    /**
     * @return array<string, array<int, ValidationRule|Stringable|string>>
     */
    public function rules(): array
    {
        $userId = $this->user()?->getKey();

        return [
            'type' => ['required', 'string', Rule::in(['income', 'expense', 'transfer'])],
            'account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where('user_id', $userId)->where('is_active', true),
            ],
            'destination_account_id' => [
                'required_if:type,transfer',
                'nullable',
                'integer',
                'different:account_id',
                Rule::exists('accounts', 'id')->where('user_id', $userId)->where('is_active', true),
            ],
            'category_id' => [
                'required_unless:type,transfer',
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', $userId)->where('is_active', true),
            ],
            'amount' => ['required', 'regex:/^\d+(\.\d{1,4})?$/', 'not_in:0,0.0,0.00,0.000,0.0000'],
            'entry_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:100'],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ];
    }
}
