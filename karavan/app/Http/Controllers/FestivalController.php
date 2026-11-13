<?php

namespace App\Http\Controllers;

use App\Models\Festival;
use App\Support\Timetable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FestivalController extends Controller
{
    /** Catalogue filtrable. */
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'genre', 'region', 'month', 'sort']);

        return view('festivals.index', [
            'festivals' => Festival::published()->withOfferCounts()->filter($filters)
                ->paginate(config('karavan.per_page'))->withQueryString(),
            'filters' => $filters,
            'genres' => Festival::allGenres(),
            'regions' => Festival::allRegions(),
        ]);
    }

    /** Fiche festival avec ses covoiturages et logements. */
    public function show(Festival $festival): View
    {
        // Brouillon : visible seulement par son organisateur et les admins (404 pour les autres)
        abort_unless(Gate::allows('view', $festival), 404);

        $festival->load(['sections', 'photos', 'performances', 'organizer']);

        return view('festivals.show', [
            'festival' => $festival,
            'design' => $festival->design(),
            'timetable' => new Timetable($festival->performances),
            'rides' => $festival->rides()->with(['driver', 'festival'])->withBookedSeats()->orderBy('departs_at')->get(),
            'housings' => $festival->housings()->with('festival')->orderBy('distance_km')->get(),
        ]);
    }
}
