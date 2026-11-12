<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Housing;
use App\Models\HousingBooking;
use App\Models\Ride;
use App\Models\RideBooking;

final class AccountController extends Controller
{
    /** Tableau de bord du membre : ses offres et ses réservations. */
    public function index(): void
    {
        $user = $this->requireAuth();
        $id = (int) $user['id'];

        $this->render('account/index', [
            'title'         => 'Mon espace — Karavan',
            'myRides'       => Ride::byDriver($id),
            'myHousings'    => Housing::byHost($id),
            'rideBookings'  => RideBooking::byPassenger($id),
            'stayBookings'  => HousingBooking::byGuest($id),
        ]);
    }
}
