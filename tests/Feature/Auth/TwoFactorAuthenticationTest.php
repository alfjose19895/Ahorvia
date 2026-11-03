<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Livewire\Settings\SettingsManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_enable_confirm_and_disable_two_factor_from_settings_component(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('ClaveSegura2026!'),
        ]);

        $component = Livewire::actingAs($user)
            ->test(SettingsManager::class)
            ->set('twoFactorPassword', 'ClaveSegura2026!')
            ->call('enableTwoFactor')
            ->assertSet('showingQrCode', true)
            ->assertSet('showingConfirmation', true);

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);

        $secret = (string) decrypt($user->two_factor_secret);
        $validCode = (new Google2FA)->getCurrentOtp($secret);

        $component
            ->set('twoFactorCode', $validCode)
            ->call('confirmTwoFactor')
            ->assertHasNoErrors()
            ->assertSet('showingRecoveryCodes', true);

        $user->refresh();
        $this->assertTrue($user->hasConfirmedTwoFactor());
        $this->assertCount(8, $user->recoveryCodes());

        $component
            ->set('twoFactorPassword', 'ClaveSegura2026!')
            ->call('disableTwoFactor')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertFalse($user->hasConfirmedTwoFactor());
        $this->assertNull($user->two_factor_secret);
    }

    public function test_user_with_confirmed_two_factor_is_challenged_on_login_and_can_verify_totp(): void
    {
        $google2fa = new Google2FA;
        $rawSecret = $google2fa->generateSecretKey();

        $user = User::factory()->create([
            'email' => '2fa@ahorvia.test',
            'password' => Hash::make('ClaveSegura2026!'),
        ]);

        $user->forceFill([
            'two_factor_secret' => encrypt($rawSecret),
            'two_factor_recovery_codes' => encrypt(json_encode(['RECOV-CODE-1', 'RECOV-CODE-2'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $loginResponse = $this->post('/login', [
            'email' => '2fa@ahorvia.test',
            'password' => 'ClaveSegura2026!',
        ]);

        $loginResponse->assertRedirect('/two-factor-challenge');
        $this->assertGuest();

        $this->get('/two-factor-challenge')->assertOk()->assertSee('Seguridad de Dos Factores (2FA)');

        $challengeResponse = $this->post('/two-factor-challenge', [
            'code' => $google2fa->getCurrentOtp($rawSecret),
        ]);

        $challengeResponse->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
