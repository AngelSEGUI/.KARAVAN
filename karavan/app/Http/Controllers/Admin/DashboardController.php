<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use App\Models\Housing;
use App\Models\HousingBooking;
use App\Models\Ride;
use App\Models\RideBooking;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'festivals' => Festival::count(),
                'members' => User::count(),
                'rides' => Ride::count(),
                'housings' => Housing::count(),
                'bookings' => RideBooking::count() + HousingBooking::count(),
            ],
            'activity' => $this->recentActivity(10),
            'gaps' => Festival::withOfferCounts()->orderBy('starts_on')->get()
                ->filter(fn ($f) => $f->rides_count === 0 || $f->housings_count === 0),
            'newMembers' => User::latest()->limit(5)->get(),
        ]);
    }

    /**
     * Flux d'activité : les derniers événements de chaque type, fusionnés et triés par date.
     *
     * @return Collection<int, array{icon: string, actor: string, verb: string, label: string, url: ?string, at: \Carbon\CarbonInterface}>
     */
    private function recentActivity(int $limit): Collection
    {
        $rides = Ride::with(['driver', 'festival'])->latest()->limit($limit)->get()->map(fn ($r) => [
            'icon' => 'car', 'actor' => $r->driver->name, 'verb' => 'a proposé un trajet',
            'label' => "{$r->departure_city} → {$r->festival->name}", 'url' => route('rides.show', $r), 'at' => $r->created_at,
        ]);
        $housings = Housing::with('host')->latest()->limit($limit)->get()->map(fn ($h) => [
            'icon' => 'home', 'actor' => $h->host->name, 'verb' => 'a proposé un logement',
            'label' => $h->title, 'url' => route('housings.show', $h), 'at' => $h->created_at,
        ]);
        $rideBookings = RideBooking::with(['passenger', 'ride.festival'])->latest()->limit($limit)->get()->map(fn ($b) => [
            'icon' => 'ticket', 'actor' => $b->passenger->name, 'verb' => 'a réservé un trajet',
            'label' => "{$b->ride->departure_city} → {$b->ride->festival->name}", 'url' => route('rides.show', $b->ride_id), 'at' => $b->created_at,
        ]);
        $stays = HousingBooking::with(['guest', 'housing'])->latest()->limit($limit)->get()->map(fn ($b) => [
            'icon' => 'tent', 'actor' => $b->guest->name, 'verb' => 'a demandé un logement',
            'label' => $b->housing->title, 'url' => route('housings.show', $b->housing_id), 'at' => $b->created_at,
        ]);
        $users = User::latest()->limit($limit)->get()->map(fn ($u) => [
            'icon' => 'user', 'actor' => $u->name, 'verb' => 'a rejoint la communauté',
            'label' => (string) $u->city, 'url' => null, 'at' => $u->created_at,
        ]);

        return collect()->concat($rides)->concat($housings)->concat($rideBookings)->concat($stays)->concat($users)
            ->sortByDesc('at')->take($limit)->values();
    }
}
