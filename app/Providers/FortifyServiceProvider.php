<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        Fortify::loginView(static fn () => view('auth.login'));
        Fortify::requestPasswordResetLinkView(static fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(static fn (Request $request) => view('auth.reset-password', ['request' => $request]));
        Fortify::twoFactorChallengeView(static fn () => view('auth.two-factor-challenge'));
        Fortify::confirmPasswordView(static fn () => view('auth.confirm-password'));

        Fortify::authenticateUsing(static function (Request $request): ?User {
            $email = mb_strtolower(trim((string) $request->input(Fortify::username(), '')));
            $password = (string) $request->input('password', '');

            $user = User::query()->where('email', $email)->first();

            if ($user === null || ! Hash::check($password, $user->password)) {
                return null;
            }

            if ((bool) config('ahorvia.auth.enforce_active_access', true) && ! $user->hasActiveAccess()) {
                throw ValidationException::withMessages([
                    Fortify::username() => ['Tu acceso a Ahorvia no se encuentra activo. Contacta al administrador.'],
                ]);
            }

            return $user;
        });

        Event::listen(Login::class, static function (Login $event): void {
            if ($event->user instanceof User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
            }
        });

        RateLimiter::for('login', static function (Request $request): Limit {
            $maxAttempts = (int) config('ahorvia.auth.login_max_attempts', 5);
            $throttleKey = Str::transliterate(
                Str::lower((string) $request->input(Fortify::username())).'|'.$request->ip()
            );

            return Limit::perMinute($maxAttempts)->by($throttleKey);
        });

        RateLimiter::for('two-factor', static function (Request $request): Limit {
            $maxAttempts = (int) config('ahorvia.auth.two_factor_max_attempts', 5);

            return Limit::perMinute($maxAttempts)->by(
                (string) ($request->session()->get('login.id') ?? $request->ip())
            );
        });
    }
}
