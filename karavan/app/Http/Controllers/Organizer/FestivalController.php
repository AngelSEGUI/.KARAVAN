<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\FestivalRequest;
use App\Models\Festival;
use App\Models\Performance;
use App\Support\FestivalDesign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Espace organisateur : création et personnalisation des pages de festival.
 * Onglets de l'éditeur : informations · apparence · medias · programmation.
 * Les images sont gérées par MediaController.
 */
class FestivalController extends Controller
{
    public const TABS = [
        'informations' => 'Informations',
        'apparence' => 'Apparence',
        'medias' => 'Images',
        'programmation' => 'Programmation',
    ];

    /** Mes festivals (tous les festivals pour un admin). */
    public function index(Request $request): View
    {
        $this->authorize('create', Festival::class);
        $user = $request->user();

        return view('organizer.index', [
            'festivals' => Festival::query()
                ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
                ->withOfferCounts()->withCount('performances')
                ->orderByDesc('is_published')->orderBy('starts_on')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Festival::class);

        return view('organizer.edit', [
            'festival' => new Festival(['hue' => random_int(0, 359)]),
            'tab' => 'informations',
            'regions' => Festival::allRegions(),
            'genres' => Festival::allGenres(),
        ]);
    }

    public function store(FestivalRequest $request): RedirectResponse
    {
        $festival = new Festival($request->festivalData());
        $festival->hue = random_int(0, 359);
        $festival->is_published = false;                     // brouillon tant que l'organisateur ne publie pas
        $festival->user_id = $request->user()->isPro() ? $request->user()->id : null;
        $festival->save();

        return redirect()->route('organizer.festivals.edit', [$festival->id, 'apparence'])
            ->with('success', "« {$festival->name} » est créé en brouillon. Personnalisez maintenant sa page.");
    }

    public function edit(Festival $festival, string $tab = 'informations'): View
    {
        $this->authorize('update', $festival);
        abort_unless(array_key_exists($tab, self::TABS), 404);

        return view('organizer.edit', [
            'festival' => $festival->load(['sections', 'photos', 'performances'])->loadCount(['rides', 'housings']),
            'tab' => $tab,
            'regions' => Festival::allRegions(),
            'genres' => Festival::allGenres(),
        ]);
    }

    /* ---------- Onglet « Informations » ---------- */

    public function updateInfo(FestivalRequest $request, Festival $festival): RedirectResponse
    {
        $festival->update($request->festivalData());

        return back()->with('success', 'Informations enregistrées.');
    }

    /* ---------- Onglet « Apparence » : palette, polices, paragraphes ---------- */

    public function updateDesign(Request $request, Festival $festival): RedirectResponse
    {
        $this->authorize('update', $festival);
        $config = config('festival_design');

        // Les blocs de texte entièrement vides sont ignorés
        $request->merge(['sections' => collect($request->input('sections', []))
            ->filter(fn ($s) => filled($s['title'] ?? null) || filled($s['body'] ?? null))->values()->all()]);

        $data = $request->validate([
            'palette' => ['required', Rule::in([...array_keys($config['palettes']), 'custom'])],
            'primary' => ['required_if:palette,custom', 'nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary' => ['required_if:palette,custom', 'nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'mode' => ['required_if:palette,custom', 'nullable', Rule::in(['light', 'dark'])],
            'heading_font' => ['required', Rule::in(array_keys($config['fonts']['heading']))],
            'body_font' => ['required', Rule::in(array_keys($config['fonts']['body']))],
            'hue' => ['required', 'integer', 'between:0,359'],
            'sections' => ['array', 'max:' . $config['max_sections']],
            'sections.*.title' => ['nullable', 'string', 'max:120'],
            'sections.*.body' => ['required', 'string', 'max:5000'],
        ], [
            'sections.*.body.required' => 'Chaque bloc doit contenir un texte.',
            'primary.regex' => 'Choisissez une couleur valide.',
            'secondary.regex' => 'Choisissez une couleur valide.',
        ], [
            'palette' => 'la palette', 'primary' => 'la couleur principale', 'secondary' => "la couleur d'accent",
            'heading_font' => 'la police des titres', 'body_font' => 'la police du texte',
        ]);

        DB::transaction(function () use ($festival, $data) {
            // forceFill : ces colonnes ne sont volontairement pas « fillable » (jamais remplies depuis un formulaire brut)
            $festival->forceFill([
                'design' => (new FestivalDesign($data))->toArray(),
                'hue' => $data['hue'],
            ])->save();
            $festival->sections()->delete();
            foreach (array_values($data['sections'] ?? []) as $i => $section) {
                $festival->sections()->create(['title' => $section['title'] ?? null, 'body' => $section['body'], 'position' => $i]);
            }
        });

        return back()->with('success', 'Apparence enregistrée.');
    }

    /* ---------- Onglet « Programmation » ---------- */

    /**
     * Enregistre toute la grille d'un coup : les lignes modifiées sont mises à jour,
     * les nouvelles créées, celles retirées supprimées.
     * Une journée de festival va de 6h à 6h : « samedi 01h30 » est la nuit de samedi à dimanche,
     * et une fin plus petite que le début signifie « après minuit » (ex. 23h30 → 01h00).
     */
    public function updateProgramme(Request $request, Festival $festival): RedirectResponse
    {
        $this->authorize('update', $festival);

        $rows = collect($request->input('performances', []))
            ->filter(fn ($r) => collect($r)->except(['id', 'day'])->filter(fn ($v) => filled($v))->isNotEmpty())
            ->values();
        $request->merge(['performances' => $rows->all()]);

        $days = collect($festival->days())->map->toDateString()->all();

        $request->validate([
            'performances' => ['array', 'max:' . config('festival_design.max_performances')],
            'performances.*.id' => ['nullable', 'integer'],
            'performances.*.day' => ['required', Rule::in($days)],
            'performances.*.start' => ['required', 'date_format:H:i'],
            'performances.*.end' => ['required', 'date_format:H:i', 'different:performances.*.start'],
            'performances.*.artist' => ['required', 'string', 'max:120'],
            'performances.*.stage' => ['required', 'string', 'max:80'],
            'performances.*.capacity' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ], [
            'performances.*.day.in' => 'Ligne :position : la journée doit faire partie des dates du festival.',
            'performances.*.end.different' => 'Ligne :position : la fin doit être différente du début.',
        ], [
            'performances.*.day' => 'ligne :position : la journée',
            'performances.*.start' => 'ligne :position : l\'heure de début',
            'performances.*.end' => 'ligne :position : l\'heure de fin',
            'performances.*.artist' => 'ligne :position : l\'artiste',
            'performances.*.stage' => 'ligne :position : la scène',
            'performances.*.capacity' => 'ligne :position : la capacité',
        ]);

        $keep = [];
        DB::transaction(function () use ($festival, $rows, &$keep) {
            foreach ($rows as $row) {
                // « Samedi, 01h30 » = la nuit de samedi à dimanche (une journée de festival va de 6h à 6h)
                $start = Carbon::parse("{$row['day']} {$row['start']}");
                if ($start->hour < Performance::DAY_STARTS_AT_HOUR) {
                    $start->addDay();
                }
                $end = $start->copy()->setTimeFromTimeString($row['end']);
                if ($end->lessThanOrEqualTo($start)) {
                    $end->addDay();
                }

                $attributes = [
                    'artist' => trim($row['artist']),
                    'stage' => trim($row['stage']),
                    'starts_at' => $start,
                    'ends_at' => $end,
                    'capacity' => filled($row['capacity'] ?? null) ? (int) $row['capacity'] : null,
                ];

                $performance = filled($row['id'] ?? null) ? $festival->performances()->find($row['id']) : null;
                $performance ? $performance->update($attributes) : $performance = $festival->performances()->create($attributes);
                $keep[] = $performance->id;
            }
            $festival->performances()->whereNotIn('id', $keep)->delete();
        });

        return back()->with('success', $rows->isEmpty()
            ? 'Programmation vidée.'
            : 'Programmation enregistrée : ' . plural($rows->count(), 'créneau', 'créneaux') . '.');
    }

    /* ---------- Publication et suppression ---------- */

    public function togglePublish(Festival $festival): RedirectResponse
    {
        $this->authorize('update', $festival);
        $festival->forceFill(['is_published' => ! $festival->is_published])->save();

        return back()->with('success', $festival->is_published
            ? "« {$festival->name} » est en ligne ! Les festivaliers peuvent proposer trajets et logements."
            : "« {$festival->name} » est repassé en brouillon : il n'est plus visible du public.");
    }

    public function destroy(Festival $festival): RedirectResponse
    {
        $this->authorize('delete', $festival);
        $festival->delete();

        return redirect()->route('organizer.index')->with('success', "« {$festival->name} » a été supprimé.");
    }
}
