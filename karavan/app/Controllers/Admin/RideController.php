<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Festival;
use App\Models\Ride;

/** Modération des covoiturages. */
final class RideController extends AdminController
{
    public function index(): void
    {
        $filters = [
            'festival' => (string) ($_GET['festival'] ?? ''),
            'from'     => trim((string) ($_GET['from'] ?? '')),
        ];

        $this->render('rides/index', [
            'title'     => 'Covoiturages — Admin Karavan',
            'rides'     => Ride::search($filters),
            'filters'   => $filters,
            'festivals' => Festival::options(),
        ]);
    }

    public function destroy(string $id): void
    {
        $this->guard();
        $ride = Ride::findDetailed((int) $id);
        if ($ride === null) {
            $this->notFound();
            return;
        }
        Ride::delete((int) $ride['id']);
        flash('success', "Trajet {$ride['departure_city']} → {$ride['festival_name']} supprimé (et ses réservations).");
        $this->back();
    }
}
