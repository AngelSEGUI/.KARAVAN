<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FestivalRequest;
use App\Models\Festival;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Gestion du catalogue des festivals. */
class FestivalController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.festivals.index', [
            'festivals' => Festival::withOfferCounts()->filter($request->only('q'))
                ->paginate(20)->withQueryString(),
            'q' => $request->string('q')->toString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.festivals.form', [
            'festival' => new Festival(['hue' => random_int(0, 359), 'is_featured' => false]),
            'regions' => Festival::allRegions(),
            'genres' => Festival::allGenres(),
        ]);
    }

    public function store(FestivalRequest $request): RedirectResponse
    {
        $festival = Festival::create($request->festivalData());

        return redirect()->route('admin.festivals.edit', $festival->id)
            ->with('success', "« {$festival->name} » a été ajouté.");
    }

    public function edit(Festival $festival): View
    {
        return view('admin.festivals.form', [
            'festival' => $festival->loadCount(['rides', 'housings']),
            'regions' => Festival::allRegions(),
            'genres' => Festival::allGenres(),
        ]);
    }

    public function update(FestivalRequest $request, Festival $festival): RedirectResponse
    {
        $festival->update($request->festivalData());

        return redirect()->route('admin.festivals.edit', $festival->id)->with('success', 'Modifications enregistrées.');
    }

    public function toggleFeatured(Festival $festival): RedirectResponse
    {
        $festival->update(['is_featured' => ! $festival->is_featured]);

        return back()->with('success', $festival->is_featured
            ? "« {$festival->name} » est mis à la une."
            : "« {$festival->name} » n'est plus à la une.");
    }

    public function destroy(Festival $festival): RedirectResponse
    {
        $festival->delete(); // trajets, logements et réservations supprimés en cascade

        return redirect()->route('admin.festivals.index')
            ->with('success', "« {$festival->name} » et ses offres associées ont été supprimés.");
    }
}
