<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/** « Mon espace » : réservations du membre et annonces qu'il propose. */
class AccountController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('account.index', [
            'user' => $user,
            'rideBookings' => $user->rideBookings()->with(['ride.festival', 'ride.driver'])->get()
                ->sortBy(fn ($b) => $b->ride->departs_at),
            'stayBookings' => $user->housingBookings()->with(['housing.festival', 'housing.host'])->get()
                ->sortBy(fn ($b) => $b->housing->festival->starts_on),
            'rides' => $user->rides()->with(['driver', 'festival'])->withBookedSeats()->orderBy('departs_at')->get(),
            'housings' => $user->housings()->with('festival')->withCount('bookings')->latest()->get(),
        ]);
    }
}
