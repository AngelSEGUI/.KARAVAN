{{--
    Formulaire commun « Proposer un logement » / « Modifier le logement ».
    $housing->exists vaut false en création, true en modification.
--}}
@php
    $editing = $housing->exists;
    $requests = $editing ? $housing->bookings()->count() : 0;
    $largestGroup = $editing ? $housing->largestGroup() : 0;
    $festivalOptions = $festivals->mapWithKeys(fn ($f) => [$f->id => "{$f->name} — {$f->city}"])->all();
    $selectedType = old('type', $housing->type?->value);
@endphp
<x-layouts.app :title="$editing ? 'Modifier le logement' : 'Proposer un logement'">
    <div class="container container-narrow">
        <a href="{{ $editing ? route('housings.show', $housing) : route('housings.index') }}" class="back-link">
            <x-icon name="back" />{{ $editing ? 'Retour au logement' : 'Logements' }}
        </a>
        <div class="form-head">
            <span class="eyebrow">{{ $editing ? 'Modifier le logement' : 'Proposer un logement' }}</span>
            <h1>{{ $editing ? $housing->title : 'Accueillez des festivaliers' }}</h1>
            <p class="muted">Un coin de jardin suffit souvent à rendre un festival inoubliable.</p>
        </div>

        @if ($requests > 0)
            <p class="notice notice-warning" style="margin-bottom: 16px">
                Vous avez déjà reçu {{ plural($requests, 'demande') }} : le festival ne peut plus être changé
                @if ($largestGroup > 1) et la capacité ne peut pas descendre sous {{ $largestGroup }} personnes @endif.
            </p>
        @endif

        <form method="POST" action="{{ $editing ? route('housings.update', $housing) : route('housings.store') }}" class="form card" novalidate>
            @csrf
            @if ($editing) @method('PUT') @endif

            <fieldset>
                <legend>Type de logement</legend>
                <div class="radio-cards">
                    @foreach ($types as $type)
                        <label class="radio-card">
                            <input type="radio" name="type" value="{{ $type->value }}" @checked($selectedType === $type->value)>
                            <span><x-icon :name="$type->icon()" />{{ $type->label() }}</span>
                        </label>
                    @endforeach
                </div>
                @error('type')<span class="error">{{ $message }}</span>@enderror
            </fieldset>

            <fieldset>
                <legend>Emplacement</legend>
                @if ($requests > 0)
                    <input type="hidden" name="festival_id" value="{{ $housing->festival_id }}">
                    <x-field name="festival_display" label="Festival à proximité" type="select" :options="$festivalOptions" :value="$housing->festival_id" disabled />
                @else
                    <x-field name="festival_id" label="Festival à proximité" type="select" :options="$festivalOptions"
                             :value="$housing->festival_id" placeholder="Choisir un festival…" required />
                @endif
                <div class="form-row">
                    <x-field name="city" label="Commune" :value="$housing->city" required maxlength="80" />
                    <x-field name="distance_km" label="Distance du site (km)" type="number" :value="$housing->distance_km" min="0" step="0.1" required />
                </div>
            </fieldset>

            <fieldset>
                <legend>Description</legend>
                <x-field name="title" label="Titre de l'annonce" :value="$housing->title" required maxlength="140" placeholder="Ex. Jardin ombragé à 10 min à pied" />
                <div class="form-row">
                    <x-field name="capacity" label="Capacité (personnes)" type="number" :value="$housing->capacity"
                             :min="max(1, $largestGroup)" :max="config('karavan.max_housing_capacity')" required />
                    <x-field name="price_per_night" label="Prix par nuit (€)" type="number" :value="$housing->price_per_night" min="0" step="1" required />
                </div>
                <x-field name="description" label="Présentation" type="textarea" rows="4" maxlength="2000" optional :value="$housing->description" />
                <x-field name="amenities" label="Équipements" hint="Séparés par des virgules" maxlength="255" optional
                         :value="implode(', ', $housing->amenities ?? [])" placeholder="Douche, Parking, Wifi" />
            </fieldset>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">{{ $editing ? 'Enregistrer les modifications' : 'Publier le logement' }}</button>
                @if ($editing)<a href="{{ route('housings.show', $housing) }}" class="btn btn-ghost">Annuler</a>@endif
            </div>
        </form>

        @if ($editing)
            <section class="card danger-zone" style="margin-top: 24px">
                <div>
                    <h2 class="h4">Supprimer ce logement</h2>
                    <p class="muted small">{{ $requests > 0 ? 'Les demandes reçues seront annulées.' : "Aucune demande pour l'instant." }} Action irréversible.</p>
                </div>
                <x-delete-form :action="route('housings.destroy', $housing)" label="Supprimer" class="btn btn-danger"
                               confirm="Supprimer définitivement ce logement ?" />
            </section>
        @endif
    </div>
</x-layouts.app>
