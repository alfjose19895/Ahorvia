<?php

declare(strict_types=1);

namespace App\Http\Requests\Finance;

use App\Enums\SavingsMode;
use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class StoreSavingsGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->can('create', SavingsGoal::class);
    }

    /**
     * @return array<string, array<int, ValidationRule|Stringable|string>>
     */
    public function rules(): array
    {
        $userId = $this->user()?->getKey();

        return [
            'name' => ['required', 'string', 'max:100'],
            'mode' => ['required', Rule::enum(SavingsMode::class)],
            'target_amount' => ['required', 'regex:/^\d+(\.\d{1,4})?$/', 'not_in:0,0.0,0.00,0.000,0.0000'],
            'initial_amount' => ['nullable', 'regex:/^\d+(\.\d{1,4})?$/'],
            'duration_months' => ['nullable', 'integer', 'min:1', 'max:60'],
            'start_date' => ['nullable', 'date'],
            'target_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'linked_account_id' => [
                'nullable',
                'integer',
                Rule::exists('accounts', 'id')->where('user_id', $userId)->where('is_active', true),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
