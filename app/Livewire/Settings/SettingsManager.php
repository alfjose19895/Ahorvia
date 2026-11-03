<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración y Seguridad — Ahorvia')]
class SettingsManager extends Component
{
    public string $name = '';

    public string $email = '';

    public string $currency_code = 'USD';

    public string $timezone = 'UTC';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $twoFactorPassword = '';

    public string $twoFactorCode = '';

    public bool $showingQrCode = false;

    public bool $showingConfirmation = false;

    public bool $showingRecoveryCodes = false;

    public ?string $profileStatus = null;

    public ?string $passwordStatus = null;

    public ?string $twoFactorStatus = null;

    public function mount(): void
    {
        $user = $this->currentUser();
        $this->authorize('view', $user);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->currency_code = $user->currency_code ?: 'USD';
        $this->timezone = $user->timezone ?: 'UTC';

        if ($user->two_factor_secret !== null && $user->two_factor_confirmed_at === null) {
            $this->showingQrCode = true;
            $this->showingConfirmation = true;
        }
    }

    public function updateProfile(UpdateUserProfileInformation $updater): void
    {
        $user = $this->currentUser();
        $this->authorize('update', $user);
        $this->profileStatus = null;

        try {
            $updater->update($user, [
                'name' => $this->name,
                'email' => $this->email,
                'currency_code' => $this->currency_code,
                'timezone' => $this->timezone,
            ]);
        } catch (ValidationException $exception) {
            $this->setValidationErrors($exception);

            return;
        }

        $this->profileStatus = 'Perfil y preferencias financieras actualizados correctamente.';
    }

    public function updatePassword(UpdateUserPassword $updater): void
    {
        $user = $this->currentUser();
        $this->authorize('update', $user);
        $this->passwordStatus = null;

        try {
            $updater->update($user, [
                'current_password' => $this->current_password,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ]);
        } catch (ValidationException $exception) {
            $this->setValidationErrors($exception);

            return;
        }

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->passwordStatus = 'Contraseña actualizada de forma segura.';
    }

    public function enableTwoFactor(EnableTwoFactorAuthentication $enable): void
    {
        $user = $this->currentUser();
        $this->authorize('update', $user);
        $this->resetErrorBag();
        $this->twoFactorStatus = null;

        if (! $this->verifyTwoFactorPassword($user)) {
            return;
        }

        $enable($user);
        $user->refresh();

        $this->twoFactorPassword = '';
        $this->showingQrCode = true;
        $this->showingConfirmation = true;
        $this->showingRecoveryCodes = false;
        $this->twoFactorStatus = 'Escanea el código QR con tu aplicación autenticadora e ingresa el código de 6 dígitos para confirmar.';
    }

    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirm): void
    {
        $user = $this->currentUser();
        $this->authorize('update', $user);
        $this->resetErrorBag();
        $this->twoFactorStatus = null;

        try {
            $confirm($user, trim($this->twoFactorCode));
        } catch (ValidationException $exception) {
            $this->addError('twoFactorCode', 'El código de verificación 2FA ingresado no es válido.');

            return;
        }

        $user->refresh();

        $this->twoFactorCode = '';
        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = true;
        $this->twoFactorStatus = 'Autenticación de dos factores (2FA) activada y confirmada. Guarda tus códigos de recuperación en un lugar seguro.';
    }

    public function showRecoveryCodes(): void
    {
        $user = $this->currentUser();
        $this->authorize('view', $user);
        $this->resetErrorBag();

        if (! $this->verifyTwoFactorPassword($user)) {
            return;
        }

        $this->twoFactorPassword = '';
        $this->showingRecoveryCodes = true;
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate): void
    {
        $user = $this->currentUser();
        $this->authorize('update', $user);
        $this->resetErrorBag();

        if (! $this->verifyTwoFactorPassword($user)) {
            return;
        }

        $generate($user);
        $user->refresh();

        $this->twoFactorPassword = '';
        $this->showingRecoveryCodes = true;
        $this->twoFactorStatus = 'Se han generado nuevos códigos de recuperación. Los anteriores quedaron invalidados.';
    }

    public function disableTwoFactor(DisableTwoFactorAuthentication $disable): void
    {
        $user = $this->currentUser();
        $this->authorize('update', $user);
        $this->resetErrorBag();

        if (! $this->verifyTwoFactorPassword($user)) {
            return;
        }

        $disable($user);
        $user->refresh();

        $this->twoFactorPassword = '';
        $this->twoFactorCode = '';
        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = false;
        $this->twoFactorStatus = 'La autenticación de dos factores (2FA) ha sido desactivada.';
    }

    public function render(): View
    {
        $user = $this->currentUser()->fresh() ?? $this->currentUser();

        /** @var array<string, array{name: string, symbol: string}> $currencies */
        $currencies = config('ahorvia.finance.supported_currencies', []);

        return view('livewire.settings.settings-manager', [
            'user' => $user,
            'currencies' => $currencies,
            'commonTimezones' => [
                'UTC' => 'UTC (Tiempo Universal Coordinado)',
                'America/Bogota' => 'América / Bogotá (UTC-5)',
                'America/Lima' => 'América / Lima (UTC-5)',
                'America/Mexico_City' => 'América / Ciudad de México (UTC-6)',
                'America/Santiago' => 'América / Santiago',
                'America/Argentina/Buenos_Aires' => 'América / Buenos Aires (UTC-3)',
                'Europe/Madrid' => 'Europa / Madrid',
                'America/New_York' => 'América / Nueva York',
            ],
        ]);
    }

    private function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    private function verifyTwoFactorPassword(User $user): bool
    {
        if ($this->twoFactorPassword === '' || ! Hash::check($this->twoFactorPassword, $user->password)) {
            $this->addError('twoFactorPassword', 'Confirma tu contraseña actual para modificar la configuración 2FA.');

            return false;
        }

        return true;
    }

    private function setValidationErrors(ValidationException $exception): void
    {
        $this->resetErrorBag();

        foreach ($exception->errors() as $field => $messages) {
            foreach ($messages as $message) {
                $this->addError($field, $message);
            }
        }
    }
}
