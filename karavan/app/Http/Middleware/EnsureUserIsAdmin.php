<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Réserve une route aux administrateurs (alias « admin » dans bootstrap/app.php). */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isAdmin(), 403, "Cette zone est réservée à l'équipe d'administration de Karavan.");

        return $next($request);
    }
}
