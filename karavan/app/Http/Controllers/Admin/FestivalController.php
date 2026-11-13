<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Catalogue des festivals côté administration : liste, mise à la une, suppression.
 * La création et la modification passent par l'éditeur de l'espace organisateur
 * (Organizer\FestivalController), accessible aussi aux admins.
 */
class FestivalController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.festivals.index', [
            'festivals' => Festival::withOfferCounts()->with('organizer')->filter($request->only('q'))
                ->paginate(20)->withQueryString(),
            'q' => $request->string('q')->toString(),
        ]);
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
