<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

/**
 * Vérification de l'adresse email.
 * Un compte non vérifié peut se connecter et consulter le site, mais pas publier ni réserver
 * (middleware « verified » dans routes/web.php).
 */
class EmailVerificationController extends Controller
{
    /** Page « Vérifiez votre email ». */
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('account'));
        }

        return view('auth.verify-email', [
            // En développement, si les emails sont écrits dans le journal (MAIL_MAILER=log),
            // on affiche directement le lien pour ne pas avoir à fouiller storage/logs/laravel.log.
            'devLink' => app()->isLocal() && config('mail.default') === 'log'
                ? URL::temporarySignedRoute('verification.verify', now()->addMinutes(config('auth.verification.expire', 60)), [
                    'id' => $request->user()->getKey(),
                    'hash' => sha1($request->user()->getEmailForVerification()),
                ])
                : null,
        ]);
    }

    /** Lien cliqué dans l'email (adresse signée, valable 60 minutes). */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->intended(route('account'))
            ->with('success', 'Adresse email confirmée : vous pouvez maintenant publier et réserver. Bonne route !');
    }

    /** Renvoyer l'email de vérification. */
    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('account');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Un nouvel email de vérification vient de vous être envoyé.');
    }
}
