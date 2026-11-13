<?php

namespace App\Http\Controllers;

use App\Models\Festival;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FestivalController extends Controller
{
    /** Catalogue filtrable. */
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'genre', 'region', 'month', 'sort']);

        return view('festivals.index', [
            'festivals' => Festival::withOfferCounts()->filter($filters)
                ->paginate(config('karavan.per_page'))->withQueryString(),
            'filters' => $filters,
            'genres' => Festival::allGenres(),
            'regions' => Festival::allRegions(),
        ]);
    }

    /** Fiche festival avec ses covoiturages et logements. */
    public function show(Festival $festival): View
    {
        return view('festivals.show', [
            'festival' => $festival,
            'rides' => $festival->rides()->with(['driver', 'festival'])->withBookedSeats()->orderBy('departs_at')->get(),
            'housings' => $festival->housings()->with('festival')->orderBy('distance_km')->get(),
        ]);
    }
}
