<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Déconnecte immédiatement un membre suspendu par un administrateur,
 * même s'il avait une session ouverte. Appliqué à toutes les pages web.
 */
class LogoutSuspendedUsers
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isSuspended()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', "Votre compte est suspendu. Contactez l'équipe Karavan.");
        }

        return $next($request);
    }
}
