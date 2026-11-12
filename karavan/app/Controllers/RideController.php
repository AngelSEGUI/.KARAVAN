<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Festival;
use App\Models\Ride;
use App\Models\RideBooking;

final class RideController extends Controller
{
    public function index(): void
    {
        $filters = [
            'festival' => (string) ($_GET['festival'] ?? ''),
            'from'     => trim((string) ($_GET['from'] ?? '')),
            'seats'    => (string) ($_GET['seats'] ?? ''),
        ];

        $this->render('rides/index', [
            'title'     => 'Covoiturages vers les festivals — Karavan',
            'rides'     => Ride::search($filters),
            'filters'   => $filters,
            'festivals' => Festival::options(),
            'cities'    => Ride::departureCities(),
        ]);
    }

    public function show(string $id): void
    {
        $ride = Ride::findDetailed((int) $id);
        if ($ride === null) {
            $this->notFound();
            return;
        }

        $user = \App\Core\Auth::user();

        $this->render('rides/show', [
            'title'      => "{$ride['departure_city']} → {$ride['festival_name']} — Karavan",
            'ride'       => $ride,
            'passengers' => Ride::passengers((int) $ride['id']),
            'isDriver'   => $user && (int) $user['id'] === (int) $ride['user_id'],
            'hasBooked'  => $user && RideBooking::exists((int) $ride['id'], (int) $user['id']),
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $this->render('rides/create', [
            'title'     => 'Proposer un covoiturage — Karavan',
            'festivals' => Festival::options(),
            'selected'  => (string) ($_GET['festival'] ?? ''),
        ]);
        clear_input();
    }

    public function store(): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();

        $v = new Validator($_POST, [
            'festival_id'    => 'required|int',
            'departure_city' => 'required|max:80',
            'meeting_point'  => 'max:160',
            'departure_at'   => 'required|date',
            'return_at'      => 'date|after:departure_at',
            'seats'          => 'required|int|gte:1',
            'price'          => 'required|numeric|gte:0',
            'vehicle'        => 'max:80',
            'notes'          => 'max:1000',
        ], [
            'festival_id'    => 'Le festival',
            'departure_city' => 'La ville de départ',
            'meeting_point'  => 'Le point de rendez-vous',
            'departure_at'   => 'La date de départ',
            'return_at'      => 'La date de retour',
            'seats'          => 'Le nombre de places',
            'price'          => 'Le prix',
            'vehicle'        => 'Le véhicule',
            'notes'          => 'Le message',
        ]);

        if (!$v->passes() || Festival::find((int) $_POST['festival_id']) === null) {
            with_input($_POST, $v->errors() ?: ['festival_id' => 'Choisissez un festival.']);
            $this->redirect('/covoiturages/nouveau');
        }

        $id = Ride::create([
            'user_id'        => $user['id'],
            'festival_id'    => (int) $_POST['festival_id'],
            'departure_city' => trim($_POST['departure_city']),
            'meeting_point'  => trim($_POST['meeting_point'] ?? '') ?: null,
            'departure_at'   => date('Y-m-d H:i', strtotime($_POST['departure_at'])),
            'return_at'      => !empty($_POST['return_at']) ? date('Y-m-d H:i', strtotime($_POST['return_at'])) : null,
            'seats'          => min(8, (int) $_POST['seats']),
            'price'          => round((float) $_POST['price'], 2),
            'vehicle'        => trim($_POST['vehicle'] ?? '') ?: null,
            'notes'          => trim($_POST['notes'] ?? '') ?: null,
        ]);

        clear_input();
        flash('success', 'Votre trajet est en ligne ! Les festivaliers peuvent maintenant réserver.');
        $this->redirect('/covoiturages/' . $id);
    }

    public function book(string $id): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();

        $ride = Ride::findDetailed((int) $id);
        if ($ride === null) {
            $this->notFound();
            return;
        }

        $seats = max(1, (int) ($_POST['seats'] ?? 1));

        if ((int) $ride['user_id'] === (int) $user['id']) {
            flash('error', 'Vous ne pouvez pas réserver votre propre trajet.');
        } elseif (RideBooking::exists((int) $ride['id'], (int) $user['id'])) {
            flash('info', 'Vous avez déjà réservé ce trajet.');
        } elseif ($seats > (int) $ride['seats_left']) {
            flash('error', 'Il ne reste pas assez de places sur ce trajet.');
        } else {
            RideBooking::create([
                'ride_id' => $ride['id'],
                'user_id' => $user['id'],
                'seats'   => $seats,
                'message' => trim((string) ($_POST['message'] ?? '')) ?: null,
            ]);
            flash('success', "C'est réservé ! {$ride['driver_name']} a reçu votre demande.");
        }

        $this->redirect('/covoiturages/' . $ride['id']);
    }
}
