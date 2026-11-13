<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Après une suppression, revient à la page indiquée par le champ caché "redirect"
     * (ex. la liste d'administration), sinon à la page de repli.
     * Seules les adresses internes ("/...") sont acceptées, pour éviter les redirections externes.
     */
    protected function redirectAfterDelete(Request $request, string $fallback): RedirectResponse
    {
        $target = (string) $request->input('redirect', '');

        if ($target !== '' && str_starts_with($target, '/') && ! str_starts_with($target, '//')) {
            return redirect($target);
        }

        return redirect($fallback);
    }
}
