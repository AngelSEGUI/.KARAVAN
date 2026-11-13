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
            // Demandes reçues sur mes annonces, à accepter ou refuser
            'requests' => $user->pendingRideRequests()->with(['passenger', 'ride.festival', 'ride.driver'])->oldest()->get()
                ->concat($user->pendingHousingRequests()->with(['guest', 'housing.festival', 'housing.host'])->oldest()->get())
                ->sortBy('created_at')->values(),
            'rideBookings' => $user->rideBookings()->with(['ride.festival', 'ride.driver'])->get()
                ->sortBy(fn ($b) => $b->ride->departs_at),
            'stayBookings' => $user->housingBookings()->with(['housing.festival', 'housing.host'])->get()
                ->sortBy(fn ($b) => $b->housing->festival->starts_on),
            'rides' => $user->rides()->with(['driver', 'festival'])->withBookedSeats()
                ->withCount(['bookings as pending_count' => fn ($q) => $q->pending()])->orderBy('departs_at')->get(),
            'housings' => $user->housings()->with('festival')
                ->withCount(['bookings as pending_count' => fn ($q) => $q->pending(), 'bookings as accepted_count' => fn ($q) => $q->where('status', 'accepted')])
                ->latest()->get(),
        ]);
    }
}
