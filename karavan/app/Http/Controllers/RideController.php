<?php

namespace App\Http\Controllers;

use App\Http\Requests\RideRequest;
use App\Models\Festival;
use App\Models\Ride;
use App\Notifications\BookingRequested;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Covoiturages : liste, détail, publication, modification, suppression et réservation. */
class RideController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['festival', 'from', 'seats']);

        return view('rides.index', [
            'rides' => Ride::with(['driver', 'festival'])->withBookedSeats()->filter($filters)
                ->paginate(config('karavan.per_page'))->withQueryString(),
            'filters' => $filters,
            'festivals' => Festival::options(),
            'cities' => Ride::query()->distinct()->orderBy('departure_city')->pluck('departure_city'),
        ]);
    }

    public function show(Request $request, Ride $ride): View
    {
        $ride->load(['driver', 'festival', 'bookings.passenger'])->loadSum('activeBookings as booked_seats', 'seats');

        return view('rides.show', [
            'ride' => $ride,
            'canBook' => $request->user() ? Gate::inspect('book', $ride) : null,
        ]);
    }

    public function create(Request $request): View
    {
        return view('rides.form', [
            'ride' => new Ride(['festival_id' => $request->integer('festival') ?: null, 'seats' => 3]),
            'festivals' => Festival::options(),
        ]);
    }

    public function store(RideRequest $request): RedirectResponse
    {
        $ride = $request->user()->rides()->create($request->validated());

        return redirect()->route('rides.show', $ride)
            ->with('success', 'Votre trajet est en ligne ! Les festivaliers peuvent maintenant réserver.');
    }

    public function edit(Ride $ride): View
    {
        $this->authorize('update', $ride);
        $ride->loadSum('activeBookings as booked_seats', 'seats');

        return view('rides.form', [
            'ride' => $ride,
            'festivals' => Festival::options(),
        ]);
    }

    public function update(RideRequest $request, Ride $ride): RedirectResponse
    {
        $ride->update($request->validated());

        return redirect()->route('rides.show', $ride)->with('success', 'Trajet mis à jour.');
    }

    public function destroy(Request $request, Ride $ride): RedirectResponse
    {
        $this->authorize('delete', $ride);
        $label = "{$ride->departure_city} → {$ride->festival->name}";
        $ride->delete(); // les réservations sont supprimées en cascade

        return $this->redirectAfterDelete($request, route('account'))
            ->with('success', "Le trajet {$label} a été supprimé.");
    }

    public function book(Request $request, Ride $ride): RedirectResponse
    {
        $ride->loadSum('activeBookings as booked_seats', 'seats');
        $decision = Gate::inspect('book', $ride);

        if ($decision->denied()) {
            return back()->with('error', $decision->message());
        }

        $data = $request->validate([
            'seats' => ['required', 'integer', 'min:1', 'max:' . $ride->seatsLeft()],
            'message' => ['nullable', 'string', 'max:500'],
        ], [
            'seats.max' => 'Il ne reste que :max place(s) sur ce trajet.',
        ]);

        // Après une annulation, la même ligne est réutilisée (une seule demande par membre et par trajet)
        $booking = $ride->bookingOf($request->user());
        $booking ? $booking->reopen($data) : $booking = $ride->bookings()->create($data + ['user_id' => $request->user()->id]);

        $ride->driver->notify(new BookingRequested($booking->load(['ride.festival', 'passenger'])));

        return redirect()->route('rides.show', $ride)
            ->with('success', "Demande envoyée à {$ride->driver->first_name} ! Vous recevrez un email dès qu'il ou elle aura répondu.");
    }
}
