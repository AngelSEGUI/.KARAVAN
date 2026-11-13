{{--
    Formulaire commun « Proposer un trajet » / « Modifier le trajet ».
    $ride->exists vaut false en création, true en modification.
--}}
@php
    $editing = $ride->exists;
    $booked = $editing ? $ride->bookedSeats() : 0;
    $festivalOptions = $festivals->mapWithKeys(fn ($f) => [
        $f->id => "{$f->name} — {$f->city} (" . $f->starts_on->translatedFormat('j M') . ')',
    ])->all();
@endphp
<x-layouts.app :title="$editing ? 'Modifier le trajet' : 'Proposer un covoiturage'">
    <div class="container container-narrow">
        <a href="{{ $editing ? route('rides.show', $ride) : route('rides.index') }}" class="back-link">
            <x-icon name="back" />{{ $editing ? 'Retour au trajet' : 'Covoiturages' }}
        </a>
        <div class="form-head">
            <span class="eyebrow">{{ $editing ? 'Modifier le trajet' : 'Proposer un trajet' }}</span>
            <h1>{{ $editing ? $ride->departure_city . ' → ' . $ride->festival->name : 'Une place libre ? Partagez-la.' }}</h1>
            <p class="muted">Indiquez un prix juste pour partager les frais d'essence et de péage.</p>
        </div>

        @if ($booked > 0)
            <p class="notice notice-warning" style="margin-bottom: 16px">
                {{ plural($booked, 'place est déjà réservée', 'places sont déjà réservées') }} : le festival ne peut plus être changé
                et vous ne pouvez pas proposer moins de {{ plural($booked, 'place') }}. Prévenez vos passagers si l'horaire change.
            </p>
        @endif

        <form method="POST" action="{{ $editing ? route('rides.update', $ride) : route('rides.store') }}" class="form card" novalidate>
            @csrf
            @if ($editing) @method('PUT') @endif

            <fieldset>
                <legend>Destination</legend>
                @if ($booked > 0)
                    {{-- Champ verrouillé : la valeur est envoyée par le champ caché --}}
                    <input type="hidden" name="festival_id" value="{{ $ride->festival_id }}">
                    <x-field name="festival_display" label="Festival" type="select" :options="$festivalOptions" :value="$ride->festival_id" disabled />
                @else
                    <x-field name="festival_id" label="Festival" type="select" :options="$festivalOptions"
                             :value="$ride->festival_id" placeholder="Choisir un festival…" required />
                @endif
            </fieldset>

            <fieldset>
                <legend>Départ</legend>
                <div class="form-row">
                    <x-field name="departure_city" label="Ville de départ" :value="$ride->departure_city" required maxlength="80" placeholder="Ex. Paris" />
                    <x-field name="meeting_point" label="Point de rendez-vous" :value="$ride->meeting_point" optional maxlength="160" placeholder="Ex. gare, métro…" />
                </div>
                <div class="form-row">
                    <x-field name="departs_at" label="Date et heure de départ" type="datetime-local" :value="$ride->departs_at?->format('Y-m-d\TH:i')" required />
                    <x-field name="returns_at" label="Retour" type="datetime-local" :value="$ride->returns_at?->format('Y-m-d\TH:i')" optional />
                </div>
            </fieldset>

            <fieldset>
                <legend>Véhicule et prix</legend>
                <div class="form-row form-row-3">
                    <x-field name="seats" label="Places proposées" type="number" :value="$ride->seats" :min="max(1, $booked)" :max="config('karavan.max_ride_seats')" required />
                    <x-field name="price" label="Prix par place (€)" type="number" :value="$ride->price" min="0" step="0.5" required placeholder="20" />
                    <x-field name="vehicle" label="Véhicule" :value="$ride->vehicle" optional maxlength="80" placeholder="Ex. Clio, van…" />
                </div>
                <x-field name="notes" label="Message aux passagers" type="textarea" rows="4" maxlength="1000" optional
                         :value="$ride->notes" placeholder="Bagages, pauses, ambiance musicale…" />
            </fieldset>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">{{ $editing ? 'Enregistrer les modifications' : 'Publier le trajet' }}</button>
                @if ($editing)<a href="{{ route('rides.show', $ride) }}" class="btn btn-ghost">Annuler</a>@endif
            </div>
        </form>

        @if ($editing)
            <section class="card danger-zone" style="margin-top: 24px">
                <div>
                    <h2 class="h4">Supprimer ce trajet</h2>
                    <p class="muted small">{{ $booked > 0 ? 'Les réservations de vos passagers seront annulées.' : "Personne n'a encore réservé." }} Action irréversible.</p>
                </div>
                <x-delete-form :action="route('rides.destroy', $ride)" label="Supprimer" class="btn btn-danger"
                               confirm="Supprimer définitivement ce trajet ?" />
            </section>
        @endif
    </div>
</x-layouts.app>
