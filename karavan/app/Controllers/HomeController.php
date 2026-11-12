<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Festival;
use App\Models\Housing;
use App\Models\Ride;

final class HomeController extends Controller
{
    /** Page d'accueil / découverte : festivals mis en avant, prochains départs, logements. */
    public function index(): void
    {
        $this->render('home/index', [
            'title'     => 'Karavan — La route des festivals, ensemble',
            'featured'  => Festival::featured(8),
            'upcoming'  => Festival::search(['sort' => 'date']),
            'rides'     => array_slice(Ride::search(), 0, 4),
            'housings'  => array_slice(Housing::search(), 0, 3),
            'genres'    => Festival::genres(),
            'stats'     => Festival::stats(),
        ]);
    }
}
