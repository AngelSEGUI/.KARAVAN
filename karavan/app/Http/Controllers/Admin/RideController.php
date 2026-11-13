<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Modération des trajets. La suppression et la modification passent par les routes
 * publiques (rides.edit / rides.destroy) : la RidePolicy autorise les administrateurs.
 */
class RideController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['festival', 'from']);

        return view('admin.rides.index', [
            'rides' => Ride::with(['driver', 'festival'])->withBookedSeats()->filter($filters)
                ->paginate(20)->withQueryString(),
            'filters' => $filters,
            'festivals' => Festival::options(),
        ]);
    }
}
