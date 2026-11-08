<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AccessStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationAndAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_guests_to_login_and_authenticated_users_to_dashboard(): void
    {
        $this->get('/')->assertRedirect('/login');

        $user = User::factory()->create();
        $this->actingAs($user)->get('/')->assertRedirect('/dashboard');
    }

    public function test_login_screen_renders_ahorvia_identity_and_security_headers(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('Bienvenido a Ahorvia')
            ->assertSee('Tu dinero bajo control.')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_user_can_register_and_receive_default_categories(): void
    {
        $response = $this->get('/register');
        $response->assertOk()
            ->assertSee('Comienza con Ahorvia')
            ->assertSee('Crear cuenta');

        $registerResponse = $this->post('/register', [
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@ahorvia.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'currency_code' => 'USD',
        ]);

        $registerResponse->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $newUser = User::where('email', 'nuevo@ahorvia.test')->first();
        $this->assertNotNull($newUser);
        $this->assertSame('USD', $newUser->currency_code);
        $this->assertSame(12, $newUser->categories()->count());
    }

    public function test_active_user_can_authenticate_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'titular@ahorvia.test',
            'password' => Hash::make('SecretPass123!'),
        ]);

        $response = $this->post('/login', [
            'email' => 'titular@ahorvia.test',
            'password' => 'SecretPass123!',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
        $this->assertNotNull($user->fresh()?->last_login_at);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_login_rate_limiting_blocks_after_five_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'seguridad@ahorvia.test',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'contraseña-incorrecta',
            ]);
        }

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'contraseña-incorrecta',
        ])->assertStatus(429);
    }

    public function test_suspended_user_cannot_authenticate_or_keep_active_session(): void
    {
        $suspendedUser = User::factory()->suspended()->create([
            'email' => 'suspendido@ahorvia.test',
            'password' => Hash::make('SecretPass123!'),
        ]);

        $this->from('/login')->post('/login', [
            'email' => 'suspendido@ahorvia.test',
            'password' => 'SecretPass123!',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();

        // Si un usuario autenticado es revocado durante su sesión, el middleware cierra su acceso.
        $activeUser = User::factory()->create();
        $activeUser->update(['access_status' => AccessStatus::Revoked, 'is_active' => false]);

        $this->actingAs($activeUser)
            ->get('/dashboard')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'recuperar@ahorvia.test']);

        $this->get('/forgot-password')->assertOk()->assertSee('Recuperar contraseña');

        $token = Password::createToken($user);

        $this->get("/reset-password/{$token}?email={$user->email}")
            ->assertOk()
            ->assertSee('Definir nueva contraseña');

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NuevaClaveSegura99!',
            'password_confirmation' => 'NuevaClaveSegura99!',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('NuevaClaveSegura99!', (string) $user->fresh()?->password));
    }
}
