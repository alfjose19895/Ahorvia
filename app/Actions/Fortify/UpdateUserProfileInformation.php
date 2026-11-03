<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        /** @var array<string, mixed> $currencies */
        $currencies = config('ahorvia.finance.supported_currencies', []);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'currency_code' => [
                'sometimes',
                'required',
                'string',
                'size:3',
                Rule::in(array_keys($currencies)),
            ],
            'timezone' => [
                'sometimes',
                'required',
                'string',
                'timezone:all',
            ],
        ])->validateWithBag('updateProfileInformation');

        $attributes = [
            'name' => trim($input['name']),
            'email' => mb_strtolower(trim($input['email'])),
            'currency_code' => strtoupper($input['currency_code'] ?? $user->currency_code),
            'timezone' => $input['timezone'] ?? $user->timezone,
        ];

        if ($attributes['email'] !== $user->email && $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $attributes);

            return;
        }

        $user->forceFill($attributes)->save();
    }

    /**
     * @param  array<string, string>  $attributes
     */
    protected function updateVerifiedUser(User $user, array $attributes): void
    {
        $user->forceFill([
            ...$attributes,
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
