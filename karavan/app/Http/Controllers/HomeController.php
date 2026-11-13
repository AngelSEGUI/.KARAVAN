<?php

namespace App\Http\Controllers;

use App\Models\Festival;
use App\Models\Housing;
use App\Models\Ride;
use App\Models\User;
use Illuminate\View\View;

/** Page d'accueil / découverte. */
class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'featured' => Festival::featured()->withOfferCounts()->orderBy('starts_on')->limit(8)->get(),
            'rides' => Ride::with(['driver', 'festival'])->withBookedSeats()->filter([])->limit(4)->get(),
            'housings' => Housing::with('festival')->filter([])->limit(3)->get(),
            'genres' => Festival::allGenres(),
            'stats' => [
                'festivals' => Festival::count(),
                'rides' => Ride::count(),
                'housings' => Housing::count(),
                'members' => User::count(),
            ],
        ]);
    }
}
