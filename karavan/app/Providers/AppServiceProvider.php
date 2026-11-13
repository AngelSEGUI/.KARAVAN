<?php

namespace App\Providers;

use App\Support\Theme;
use Illuminate\Pagination\Paginator;
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
    }
}
