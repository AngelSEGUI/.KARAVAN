<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Festival;
use App\Models\Housing;

/** Modération des logements. */
final class HousingController extends AdminController
{
    public function index(): void
    {
        $filters = [
            'festival' => (string) ($_GET['festival'] ?? ''),
            'type'     => (string) ($_GET['type'] ?? ''),
        ];

        $this->render('housings/index', [
            'title'     => 'Logements — Admin Karavan',
            'housings'  => Housing::adminList($filters),
            'filters'   => $filters,
            'festivals' => Festival::options(),
            'types'     => Housing::TYPES,
        ]);
    }

    public function destroy(string $id): void
    {
        $this->guard();
        $housing = Housing::find((int) $id);
        if ($housing === null) {
            $this->notFound();
            return;
        }
        Housing::delete((int) $housing['id']);
        flash('success', "Logement « {$housing['title']} » supprimé (et ses demandes).");
        $this->back();
    }
}
