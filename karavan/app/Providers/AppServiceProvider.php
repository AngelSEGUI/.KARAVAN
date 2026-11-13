<?php

namespace App\Providers;

use App\Support\Theme;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(Theme::class);
    }

    public function boot(): void
    {
        // Pagination aux couleurs du thème (resources/views/partials/pagination.blade.php)
        Paginator::defaultView('partials.pagination');
        Paginator::defaultSimpleView('partials.pagination');

        // Email de vérification de l'adresse, en français
        VerifyEmail::toMailUsing(fn (object $user, string $url) => (new MailMessage)
            ->subject('Confirmez votre adresse email')
            ->greeting('Bienvenue ' . $user->first_name . ' !')
            ->line('Merci de rejoindre la communauté Karavan. Cliquez sur le bouton ci-dessous pour confirmer votre adresse email.')
            ->action('Confirmer mon adresse', $url)
            ->line('Ce lien est valable ' . config('auth.verification.expire', 60) . ' minutes.')
            ->line("Si vous n'avez pas créé de compte, ignorez simplement cet email.")
            ->salutation(new HtmlString("À bientôt sur la route,<br>L'équipe Karavan")));
    }
}
