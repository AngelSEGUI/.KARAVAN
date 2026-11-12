<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Validator;
use App\Models\Festival;

/** CRUD des festivals + mise en avant sur la page découverte. */
final class FestivalController extends AdminController
{
    private const RULES = [
        'name'        => 'required|max:120',
        'slug'        => 'max:120',
        'city'        => 'required|max:80',
        'region'      => 'required|max:80',
        'venue'       => 'max:160',
        'start_date'  => 'required|date',
        'end_date'    => 'required|date|after:start_date',
        'genres'      => 'required|max:160',
        'tagline'     => 'max:200',
        'description' => 'max:5000',
        'lineup'      => 'max:2000',
        'attendance'  => 'int|gte:0',
        'price_from'  => 'numeric|gte:0',
        'hue'         => 'int',
    ];

    private const LABELS = [
        'name'       => 'Le nom',
        'slug'       => "L'adresse",
        'city'       => 'La ville',
        'region'     => 'La région',
        'venue'      => 'Le lieu',
        'start_date' => 'La date de début',
        'end_date'   => 'La date de fin',
        'genres'     => 'Les styles',
        'tagline'    => "L'accroche",
        'attendance' => "L'affluence",
        'price_from' => 'Le prix',
        'hue'        => 'La couleur',
    ];

    public function index(): void
    {
        $this->render('festivals/index', [
            'title'     => 'Festivals — Admin Karavan',
            'festivals' => Festival::search(['q' => trim((string) ($_GET['q'] ?? ''))]),
            'q'         => trim((string) ($_GET['q'] ?? '')),
        ]);
    }

    public function create(): void
    {
        $this->render('festivals/form', [
            'title'    => 'Nouveau festival — Admin Karavan',
            'festival' => ['hue' => random_int(0, 359), 'featured' => 0],
            'regions'  => Festival::regions(),
            'genres'   => Festival::genres(),
        ]);
        clear_input();
    }

    public function store(): void
    {
        $this->guard();
        $data = $this->validated('/admin/festivals/nouveau');
        $id = Festival::create($data);
        clear_input();
        flash('success', "« {$data['name']} » a été ajouté.");
        $this->redirect('/admin/festivals/' . $id . '/modifier');
    }

    public function edit(string $id): void
    {
        $festival = Festival::find((int) $id) ?? $this->missing();
        // Listes plus lisibles dans les champs : "Rock, Pop" plutôt que "Rock,Pop"
        foreach (['genres', 'lineup'] as $k) {
            $festival[$k] = str_replace(',', ', ', (string) $festival[$k]);
        }

        $this->render('festivals/form', [
            'title'    => 'Modifier ' . $festival['name'] . ' — Admin Karavan',
            'festival' => $festival,
            'regions'  => Festival::regions(),
            'genres'   => Festival::genres(),
        ]);
        clear_input();
    }

    public function update(string $id): void
    {
        $this->guard();
        $festival = Festival::find((int) $id) ?? $this->missing();
        $data = $this->validated('/admin/festivals/' . $festival['id'] . '/modifier', (int) $festival['id']);
        Festival::update((int) $festival['id'], $data);
        clear_input();
        flash('success', 'Modifications enregistrées.');
        $this->redirect('/admin/festivals/' . $festival['id'] . '/modifier');
    }

    public function toggleFeatured(string $id): void
    {
        $this->guard();
        $festival = Festival::find((int) $id) ?? $this->missing();
        $featured = (int) $festival['featured'] === 1 ? 0 : 1;
        Festival::update((int) $festival['id'], ['featured' => $featured]);
        flash('success', $featured ? "« {$festival['name']} » est mis à la une." : "« {$festival['name']} » n'est plus à la une.");
        $this->back();
    }

    public function destroy(string $id): void
    {
        $this->guard();
        $festival = Festival::find((int) $id) ?? $this->missing();
        Festival::delete((int) $festival['id']);
        flash('success', "« {$festival['name']} » et ses offres associées ont été supprimés.");
        $this->redirect('/admin/festivals');
    }

    /** Valide le formulaire et renvoie les colonnes à enregistrer (ou redirige avec les erreurs). */
    private function validated(string $backTo, ?int $exceptId = null): array
    {
        $v = new Validator($_POST, self::RULES, self::LABELS);
        $errors = $v->passes() ? [] : $v->errors();

        $slug = Festival::slugify(trim((string) ($_POST['slug'] ?? '')) ?: (string) ($_POST['name'] ?? ''));
        if (!isset($errors['slug']) && Festival::slugExists($slug, $exceptId)) {
            $errors['slug'] = "L'adresse /festivals/$slug est déjà utilisée.";
        }

        if ($errors) {
            with_input($_POST, $errors);
            $this->redirect($backTo);
        }

        $list = fn (string $key) => implode(',', array_filter(array_map('trim', explode(',', (string) ($_POST[$key] ?? '')))));
        $text = fn (string $key) => trim((string) ($_POST[$key] ?? '')) ?: null;

        return [
            'name'        => trim($_POST['name']),
            'slug'        => $slug,
            'city'        => trim($_POST['city']),
            'region'      => trim($_POST['region']),
            'venue'       => $text('venue'),
            'start_date'  => date('Y-m-d', strtotime($_POST['start_date'])),
            'end_date'    => date('Y-m-d', strtotime($_POST['end_date'])),
            'genres'      => $list('genres'),
            'tagline'     => $text('tagline'),
            'description' => $text('description'),
            'lineup'      => $list('lineup') ?: null,
            'attendance'  => ($_POST['attendance'] ?? '') !== '' ? (int) $_POST['attendance'] : null,
            'price_from'  => ($_POST['price_from'] ?? '') !== '' ? round((float) $_POST['price_from'], 2) : null,
            'hue'         => ((int) ($_POST['hue'] ?? 20) % 360 + 360) % 360,
            'featured'    => isset($_POST['featured']) ? 1 : 0,
        ];
    }

    private function missing(): never
    {
        $this->notFound();
        exit;
    }
}
