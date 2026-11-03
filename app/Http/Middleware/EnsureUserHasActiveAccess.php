<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasActiveAccess
{
    /**
     * Valida que el usuario autenticado tenga derechos de acceso vigentes.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) config('ahorvia.auth.enforce_active_access', true)) {
            return $next($request);
        }

        $user = $request->user();

        if ($user !== null && ! $user->hasActiveAccess()) {
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Tu acceso a Ahorvia no se encuentra activo. Contacta al administrador.',
                ]);
        }

        return $next($request);
    }
}
