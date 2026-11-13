{{--
    Onglet « Programmation » : une ligne par créneau (journée, horaires, artiste, scène, capacité).
    Toute la grille est enregistrée d'un coup. La capacité est facultative : vide, elle n'est pas affichée au public.
--}}
@php
    $dayOptions = collect($festival->days())->mapWithKeys(fn ($d) => [$d->toDateString() => ucfirst($d->translatedFormat('D j M'))])->all();
    $rows = old('performances', $festival->performances->sortBy('starts_at')->values()->map(fn ($p) => [
        'id' => $p->id, 'day' => $p->festivalDay(), 'start' => $p->starts_at->format('H:i'), 'end' => $p->ends_at->format('H:i'),
        'artist' => $p->artist, 'stage' => $p->stage, 'capacity' => $p->capacity,
    ])->all());
    $stages = $festival->performances->pluck('stage')->unique()->values();
    $rowErrors = collect($errors->getMessages())->filter(fn ($m, $k) => str_starts_with($k, 'performances.'))->flatten()->unique();
@endphp

<form method="POST" action="{{ route('organizer.festivals.programme', $festival->id) }}" class="card org-panel" novalidate>
    @csrf @method('PUT')
    <div class="org-panel-head">
        <div>
            <h2 class="h4"><x-icon name="calendar" />Programmation</h2>
            <p class="muted small">
                Les festivaliers la verront sous forme de calendrier, une colonne par scène.
                Un créneau après minuit se saisit sur la journée de la veille (ex. samedi, 01:30 → 03:00).
            </p>
        </div>
        <span class="pill">{{ $dayOptions ? count($dayOptions) . ' jours' : '' }}</span>
    </div>

    @if ($rowErrors->isNotEmpty() || $errors->has('performances'))
        <div class="notice notice-error" role="alert">
            <strong>Certaines lignes sont à corriger :</strong>
            <ul>
                @error('performances')<li>{{ $message }}</li>@enderror
                @foreach ($rowErrors->take(8) as $message)<li>{{ $message }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="repeater" data-repeater data-max="{{ config('festival_design.max_performances') }}" data-copy-last="day,stage">
        <div class="slot-head" aria-hidden="true">
            <span>Journée</span><span>Début</span><span>Fin</span><span>Artiste</span><span>Scène</span><span>Capacité</span><span></span>
        </div>
        <ol class="repeater-list slot-list" data-repeater-list>
            @foreach ($rows as $i => $row)
                @include('organizer.partials.slot-row', compact('i', 'row', 'dayOptions'))
            @endforeach
        </ol>
        <template data-repeater-template>
            @include('organizer.partials.slot-row', ['i' => '__INDEX__', 'row' => [], 'dayOptions' => $dayOptions])
        </template>
        <datalist id="stages-list">@foreach ($stages as $stage)<option value="{{ $stage }}">@endforeach</datalist>

        @if (empty($rows))
            <p class="muted small" data-repeater-empty>Aucun créneau pour l'instant. Ajoutez le premier !</p>
        @endif
        <div class="form-actions">
            <button type="button" class="btn btn-ghost btn-sm" data-repeater-add><x-icon name="plus" />Ajouter un créneau</button>
        </div>
    </div>

    <div class="form-actions org-sticky-actions">
        <button type="submit" class="btn btn-primary">Enregistrer la programmation</button>
        <a href="{{ route('festivals.show', $festival) }}#programmation" target="_blank" class="btn btn-ghost"><x-icon name="external" />Voir le calendrier</a>
    </div>
</form>
