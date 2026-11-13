<?php

namespace App\Http\Controllers;

use App\Enums\HousingType;
use App\Http\Requests\HousingRequest;
use App\Models\Festival;
use App\Models\Housing;
use App\Notifications\BookingRequested;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Logements : liste, détail, publication, modification, suppression et demandes. */
class HousingController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['festival', 'type', 'guests', 'max_price', 'sort']);

        return view('housings.index', [
            'housings' => Housing::with('festival')->filter($filters)
                ->paginate(config('karavan.per_page'))->withQueryString(),
            'filters' => $filters,
            'festivals' => Festival::options(),
            'types' => HousingType::options(),
        ]);
    }

    public function show(Request $request, Housing $housing): View
    {
        $housing->load(['host', 'festival', 'bookings.guest']);

        return view('housings.show', [
            'housing' => $housing,
            'canBook' => $request->user() ? Gate::inspect('book', $housing) : null,
        ]);
    }

    public function create(Request $request): View
    {
        return view('housings.form', [
            'housing' => new Housing([
                'festival_id' => $request->integer('festival') ?: null,
                'type' => HousingType::Tent,
                'capacity' => 2,
            ]),
            'festivals' => Festival::options(),
            'types' => HousingType::cases(),
        ]);
    }

    public function store(HousingRequest $request): RedirectResponse
    {
        $housing = $request->user()->housings()->create($request->housingData());

        return redirect()->route('housings.show', $housing)
            ->with('success', 'Votre logement est publié. Merci de faire vivre la communauté !');
    }

    public function edit(Housing $housing): View
    {
        $this->authorize('update', $housing);

        return view('housings.form', [
            'housing' => $housing->loadCount('bookings'),
            'festivals' => Festival::options(),
            'types' => HousingType::cases(),
        ]);
    }

    public function update(HousingRequest $request, Housing $housing): RedirectResponse
    {
        $housing->update($request->housingData());

        return redirect()->route('housings.show', $housing)->with('success', 'Logement mis à jour.');
    }

    public function destroy(Request $request, Housing $housing): RedirectResponse
    {
        $this->authorize('delete', $housing);
        $title = $housing->title;
        $housing->delete(); // les demandes sont supprimées en cascade

        return $this->redirectAfterDelete($request, route('account'))
            ->with('success', "Le logement « {$title} » a été supprimé.");
    }

    public function book(Request $request, Housing $housing): RedirectResponse
    {
        $decision = Gate::inspect('book', $housing);

        if ($decision->denied()) {
            return back()->with('error', $decision->message());
        }

        $data = $request->validate([
            'guests' => ['required', 'integer', 'min:1', 'max:' . $housing->capacity],
            'nights' => ['required', 'integer', 'min:1', 'max:' . config('karavan.max_nights')],
            'message' => ['nullable', 'string', 'max:500'],
        ], [
            'guests.max' => 'Ce logement accueille au maximum :max personne(s).',
        ]);

        // Après une annulation, la même ligne est réutilisée (une seule demande par membre et par logement)
        $booking = $housing->bookingOf($request->user());
        $booking ? $booking->reopen($data) : $booking = $housing->bookings()->create($data + ['user_id' => $request->user()->id]);

        $housing->host->notify(new BookingRequested($booking->load(['housing.festival', 'guest'])));

        return redirect()->route('housings.show', $housing)
            ->with('success', "Demande envoyée à {$housing->host->first_name} ! Vous recevrez un email dès qu'il ou elle aura répondu.");
    }
}
