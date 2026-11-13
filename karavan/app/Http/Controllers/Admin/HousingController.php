<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HousingType;
use App\Http\Controllers\Controller;
use App\Models\Festival;
use App\Models\Housing;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Modération des logements (suppression/modification via les routes publiques + HousingPolicy). */
class HousingController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['festival', 'type']) + ['sort' => 'price'];

        return view('admin.housings.index', [
            'housings' => Housing::with(['host', 'festival'])->withCount('bookings')->filter($filters)
                ->paginate(20)->withQueryString(),
            'filters' => $filters,
            'festivals' => Festival::options(),
            'types' => HousingType::options(),
        ]);
    }
}
