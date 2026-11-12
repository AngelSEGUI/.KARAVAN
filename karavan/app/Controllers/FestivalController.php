<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Festival;
use App\Models\Housing;
use App\Models\Ride;

final class FestivalController extends Controller
{
    /** Catalogue filtrable des festivals. */
    public function index(): void
    {
        $filters = [
            'q'      => trim((string) ($_GET['q'] ?? '')),
            'genre'  => (string) ($_GET['genre'] ?? ''),
            'region' => (string) ($_GET['region'] ?? ''),
            'month'  => (string) ($_GET['month'] ?? ''),
            'sort'   => (string) ($_GET['sort'] ?? 'date'),
        ];

        $this->render('festivals/index', [
            'title'     => 'Découvrir les festivals — Karavan',
            'festivals' => Festival::search($filters),
            'filters'   => $filters,
            'genres'    => Festival::genres(),
            'regions'   => Festival::regions(),
        ]);
    }

    /** Fiche festival avec covoiturages et logements associés. */
    public function show(string $slug): void
    {
        $festival = Festival::findBySlug($slug);
        if ($festival === null) {
            $this->notFound();
            return;
        }

        $this->render('festivals/show', [
            'title'    => $festival['name'] . ' — Karavan',
            'festival' => $festival,
            'rides'    => Ride::forFestival((int) $festival['id']),
            'housings' => Housing::forFestival((int) $festival['id']),
        ]);
    }
}
