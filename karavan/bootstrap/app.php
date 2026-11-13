<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\LogoutSuspendedUsers;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Toutes les pages : un compte suspendu est déconnecté à sa prochaine requête
        $middleware->web(append: [LogoutSuspendedUsers::class]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);

        // Pages réservées aux invités (connexion, inscription) : un membre connecté va sur son espace
        $middleware->redirectUsersTo(fn () => route('account'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Envoi plus lourd que post_max_size (php.ini) : on revient au formulaire avec un message clair
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            if (! $request->expectsJson()) {
                return back()->with('error', "L'envoi est trop lourd pour le serveur. Envoyez moins d'images à la fois ou des fichiers plus légers.");
            }
        });
    })->create();
