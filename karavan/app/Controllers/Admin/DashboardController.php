<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Activity;
use App\Models\Festival;
use App\Models\User;

final class DashboardController extends AdminController
{
    public function index(): void
    {
        $this->render('dashboard', [
            'title'      => 'Tableau de bord — Admin Karavan',
            'stats'      => Festival::stats() + ['bookings' => Activity::bookingsCount()],
            'activity'   => Activity::recent(10),
            'gaps'       => Festival::withoutOffers(),
            'newMembers' => User::latest(5),
        ]);
    }
}
