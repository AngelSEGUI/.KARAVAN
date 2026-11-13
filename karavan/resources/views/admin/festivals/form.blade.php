{{-- Création / modification d'un festival --}}
@php
    $editing = $festival->exists;
    $hue = (int) old('hue', $festival->hue);
@endphp
<x-layouts.admin :title="$editing ? $festival->name : 'Nouveau festival'">
    <header class="admin-head">
        <div>
            <a href="{{ route('admin.festivals.index') }}" class="back-link"><x-icon name="back" />Festivals</a>
            <h1>{{ $editing ? $festival->name : 'Nouveau festival' }}</h1>
        </div>
        @if ($editing)
            <a href="{{ route('festivals.show', $festival) }}" class="btn btn-ghost"><x-icon name="external" />Voir la fiche</a>
        @endif
    </header>

    <form method="POST" action="{{ $editing ? route('admin.festivals.update', $festival->id) : route('admin.festivals.store') }}" class="admin-form" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="admin-form-main">
            <section class="panel form">
                <h2 class="panel-title">Informations</h2>
                <x-field name="name" label="Nom du festival" :value="$festival->name" required maxlength="120" data-cover-name />
                <label @class(['field-label', 'has-error' => $errors->has('slug')])>
                    <span>Adresse de la fiche <span class="muted">(générée depuis le nom si vide)</span></span>
                    <span class="input-prefix"><span>/festivals/</span><input type="text" name="slug" value="{{ old('slug', $festival->slug) }}" maxlength="120" placeholder="mon-festival"></span>
                    @error('slug')<span class="error">{{ $message }}</span>@enderror
                </label>
                <x-field name="tagline" label="Accroche" :value="$festival->tagline" optional maxlength="200" placeholder="Une phrase pour donner envie" />
                <x-field name="description" label="Description" type="textarea" rows="5" maxlength="5000" optional :value="$festival->description" />
            </section>

            <section class="panel form">
                <h2 class="panel-title">Lieu et dates</h2>
                <div class="form-row">
                    <x-field name="city" label="Ville" :value="$festival->city" required maxlength="80" />
                    <x-field name="region" label="Région" :value="$festival->region" required maxlength="80" list="regions" />
                    <datalist id="regions">@foreach ($regions as $region)<option value="{{ $region }}">@endforeach</datalist>
                </div>
                <x-field name="venue" label="Site / lieu-dit" :value="$festival->venue" optional maxlength="160" />
                <div class="form-row">
                    <x-field name="starts_on" label="Début" type="date" :value="$festival->starts_on?->format('Y-m-d')" required />
                    <x-field name="ends_on" label="Fin" type="date" :value="$festival->ends_on?->format('Y-m-d')" required />
                </div>
            </section>

            <section class="panel form">
                <h2 class="panel-title">Programmation</h2>
                <x-field name="genres" label="Styles" :value="implode(', ', $festival->genres ?? [])" required maxlength="160"
                         placeholder="Rock, Électro" :hint="'Séparés par des virgules. Existants : ' . $genres->implode(', ')" />
                <x-field name="lineup" label="Artistes" type="textarea" rows="3" maxlength="2000" optional
                         :value="implode(', ', $festival->lineup ?? [])" hint="Séparés par des virgules, tête d'affiche en premier." />
                <div class="form-row">
                    <x-field name="attendance" label="Affluence attendue" type="number" :value="$festival->attendance" optional min="0" step="1000" />
                    <x-field name="price_from" label="Billet dès (€)" type="number" :value="$festival->price_from" optional min="0" step="0.01" />
                </div>
            </section>
        </div>

        <aside class="admin-form-side">
            <section class="panel">
                <h2 class="panel-title">Couverture</h2>
                <div class="cover-preview" data-cover-preview>
                    <x-cover :name="old('name', $festival->name) ?: 'Nom du festival'" :hue="$hue" />
                </div>
                <label class="field-label">Couleur
                    <input type="range" name="hue" min="0" max="359" value="{{ $hue }}" class="hue-range" data-hue>
                </label>
            </section>

            <section class="panel">
                <label class="switch">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $festival->is_featured))>
                    <span class="switch-track"></span>
                    <span><strong>À la une</strong><small class="muted block">Mis en avant sur la page découverte</small></span>
                </label>
            </section>

            <button type="submit" class="btn btn-primary btn-block btn-lg">{{ $editing ? 'Enregistrer' : 'Créer le festival' }}</button>
        </aside>
    </form>

    @if ($editing)
        <section class="panel danger-zone">
            <div>
                <h2 class="panel-title">Supprimer ce festival</h2>
                <p class="muted small">{{ plural($festival->rides_count, 'trajet') }} et {{ plural($festival->housings_count, 'logement') }} seront supprimés avec leurs réservations. Action irréversible.</p>
            </div>
            <x-delete-form :action="route('admin.festivals.destroy', $festival->id)" label="Supprimer" class="btn btn-danger"
                           :confirm="'Supprimer définitivement « ' . $festival->name . ' » et toutes ses offres ?'" />
        </section>
    @endif
</x-layouts.admin>
