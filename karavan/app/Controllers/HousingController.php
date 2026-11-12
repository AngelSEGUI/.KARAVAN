<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\Festival;
use App\Models\Housing;
use App\Models\HousingBooking;

final class HousingController extends Controller
{
    public function index(): void
    {
        $filters = [
            'festival'  => (string) ($_GET['festival'] ?? ''),
            'type'      => (string) ($_GET['type'] ?? ''),
            'guests'    => (string) ($_GET['guests'] ?? ''),
            'max_price' => (string) ($_GET['max_price'] ?? ''),
            'sort'      => (string) ($_GET['sort'] ?? 'distance'),
        ];

        $this->render('housings/index', [
            'title'     => 'Logements près des festivals — Karavan',
            'housings'  => Housing::search($filters),
            'filters'   => $filters,
            'festivals' => Festival::options(),
            'types'     => Housing::TYPES,
        ]);
    }

    public function show(string $id): void
    {
        $housing = Housing::findDetailed((int) $id);
        if ($housing === null) {
            $this->notFound();
            return;
        }

        $user = Auth::user();

        $this->render('housings/show', [
            'title'     => $housing['title'] . ' — Karavan',
            'housing'   => $housing,
            'requests'  => Housing::requests((int) $housing['id']),
            'isHost'    => $user && (int) $user['id'] === (int) $housing['user_id'],
            'hasBooked' => $user && HousingBooking::exists((int) $housing['id'], (int) $user['id']),
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $this->render('housings/create', [
            'title'     => 'Proposer un logement — Karavan',
            'festivals' => Festival::options(),
            'types'     => Housing::TYPES,
            'selected'  => (string) ($_GET['festival'] ?? ''),
        ]);
        clear_input();
    }

    public function store(): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();

        $v = new Validator($_POST, [
            'festival_id' => 'required|int',
            'title'       => 'required|min:5|max:140',
            'type'        => 'required|in:' . implode(',', array_keys(Housing::TYPES)),
            'city'        => 'required|max:80',
            'distance_km' => 'required|numeric|gte:0',
            'capacity'    => 'required|int|gte:1',
            'price_night' => 'required|numeric|gte:0',
            'description' => 'max:2000',
            'amenities'   => 'max:255',
        ], [
            'festival_id' => 'Le festival',
            'title'       => 'Le titre',
            'type'        => 'Le type de logement',
            'city'        => 'La commune',
            'distance_km' => 'La distance',
            'capacity'    => 'La capacité',
            'price_night' => 'Le prix par nuit',
            'description' => 'La description',
            'amenities'   => 'Les équipements',
        ]);

        if (!$v->passes() || Festival::find((int) $_POST['festival_id']) === null) {
            with_input($_POST, $v->errors() ?: ['festival_id' => 'Choisissez un festival.']);
            $this->redirect('/logements/nouveau');
        }

        $amenities = array_filter(array_map('trim', explode(',', (string) ($_POST['amenities'] ?? ''))));

        $id = Housing::create([
            'user_id'     => $user['id'],
            'festival_id' => (int) $_POST['festival_id'],
            'title'       => trim($_POST['title']),
            'type'        => $_POST['type'],
            'city'        => trim($_POST['city']),
            'distance_km' => round((float) $_POST['distance_km'], 1),
            'capacity'    => min(20, (int) $_POST['capacity']),
            'price_night' => round((float) $_POST['price_night'], 2),
            'description' => trim($_POST['description'] ?? '') ?: null,
            'amenities'   => $amenities ? implode(',', $amenities) : null,
        ]);

        clear_input();
        flash('success', 'Votre logement est publié. Merci de faire vivre la communauté !');
        $this->redirect('/logements/' . $id);
    }

    public function book(string $id): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();

        $housing = Housing::findDetailed((int) $id);
        if ($housing === null) {
            $this->notFound();
            return;
        }

        $guests = max(1, (int) ($_POST['guests'] ?? 1));
        $nights = max(1, min(14, (int) ($_POST['nights'] ?? 1)));

        if ((int) $housing['user_id'] === (int) $user['id']) {
            flash('error', 'Vous ne pouvez pas réserver votre propre logement.');
        } elseif (HousingBooking::exists((int) $housing['id'], (int) $user['id'])) {
            flash('info', 'Vous avez déjà envoyé une demande pour ce logement.');
        } elseif ($guests > (int) $housing['capacity']) {
            flash('error', "Ce logement accueille au maximum {$housing['capacity']} personne(s).");
        } else {
            HousingBooking::create([
                'housing_id' => $housing['id'],
                'user_id'    => $user['id'],
                'guests'     => $guests,
                'nights'     => $nights,
                'message'    => trim((string) ($_POST['message'] ?? '')) ?: null,
            ]);
            flash('success', "Demande envoyée à {$housing['host_name']} !");
        }

        $this->redirect('/logements/' . $housing['id']);
    }
}
