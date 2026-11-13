<?php

namespace App\Http\Controllers;

use App\Enums\HousingType;
use App\Models\Festival;
use App\Models\Housing;
use App\Models\Ride;
use App\Support\Theme;
use Illuminate\View\View;

/**
 * Guide de styles : affiche tous les composants de l'interface avec de vraies données,
 * et permet d'essayer chaque thème (?theme=nuit) sans toucher au .env.
 */
class StyleGuideController extends Controller
{
    public function __invoke(Theme $theme): View
    {
        abort_unless($theme->switcherEnabled() || auth()->user()?->isAdmin(), 404);

        return view('styleguide', [
            'themes' => $theme->available(),
            'current' => $theme->current(),
            'default' => $theme->default(),
            'canSwitch' => $theme->switcherEnabled(),
            'festival' => Festival::published()->withOfferCounts()->orderBy('starts_on')->first(),
            'ride' => Ride::with(['driver', 'festival'])->withBookedSeats()->first(),
            'housing' => Housing::with('festival')->first(),
            'types' => HousingType::options(),
        ]);
    }
}
