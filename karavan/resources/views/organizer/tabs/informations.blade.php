{{-- Onglet « Informations » : identité, dates, lieu, liens. Sert aussi au formulaire de création. --}}
@php $editing = $festival->exists; @endphp
<form method="POST" action="{{ $editing ? route('organizer.festivals.info', $festival->id) : route('organizer.festivals.store') }}" class="form card org-panel" novalidate>
    @csrf
    @if ($editing) @method('PUT') @endif

    @unless ($editing)
        <p class="notice">Votre festival sera créé <strong>en brouillon</strong> : personnalisez sa page (couleurs, images, programmation) puis publiez-le quand vous êtes prêt.</p>
    @endunless

    <fieldset>
        <legend>Identité</legend>
        <div class="form-row">
            <x-field name="name" label="Nom du festival" :value="$festival->name" required maxlength="120" />
            <x-field name="slug" label="Adresse de la page" :value="$festival->slug" optional maxlength="120"
                     hint="karavan.fr/festivals/… — générée depuis le nom si vide." />
        </div>
        <x-field name="tagline" label="Accroche" :value="$festival->tagline" optional maxlength="200" placeholder="Ex. Trois jours de musique face à la mer" />
        <x-field name="description" label="Présentation courte" type="textarea" rows="4" :value="$festival->description" optional maxlength="5000"
                 hint="Affichée en haut de la page. Vous pourrez ajouter des paragraphes détaillés dans l'onglet « Apparence »." />
        <div class="form-row">
            <x-field name="genres" label="Styles musicaux" :value="implode(', ', $festival->genres ?? [])" required list="genres-list"
                     hint="Séparés par des virgules. Ex. Rock, Électro" />
            <x-field name="lineup" label="Têtes d'affiche" :value="implode(', ', $festival->lineup ?? [])" optional
                     hint="Séparées par des virgules. Le détail se renseigne dans « Programmation »." />
        </div>
        <datalist id="genres-list">@foreach ($genres as $genre)<option value="{{ $genre }}">@endforeach</datalist>
    </fieldset>

    <fieldset>
        <legend>Dates et lieu</legend>
        <div class="form-row">
            <x-field name="starts_on" label="Premier jour" type="date" :value="$festival->starts_on?->toDateString()" required />
            <x-field name="ends_on" label="Dernier jour" type="date" :value="$festival->ends_on?->toDateString()" required />
        </div>
        <div class="form-row form-row-3">
            <x-field name="city" label="Ville" :value="$festival->city" required maxlength="80" />
            <x-field name="region" label="Région" :value="$festival->region" required maxlength="80" list="regions-list" />
            <x-field name="venue" label="Site" :value="$festival->venue" optional maxlength="160" placeholder="Ex. Plage du Sillon" />
        </div>
        <datalist id="regions-list">@foreach ($regions as $region)<option value="{{ $region }}">@endforeach</datalist>
    </fieldset>

    <fieldset>
        <legend>Billetterie et liens</legend>
        <div class="form-row">
            <x-field name="price_from" label="Pass journée dès (€)" type="number" min="0" step="0.5" :value="$festival->price_from" optional />
            <x-field name="attendance" label="Festivaliers attendus" type="number" min="0" step="100" :value="$festival->attendance" optional />
        </div>
        <div class="form-row">
            <x-field name="website" label="Site officiel" type="url" :value="$festival->website" optional placeholder="https://" />
            <x-field name="ticket_url" label="Billetterie" type="url" :value="$festival->ticket_url" optional placeholder="https://" />
        </div>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">{{ $editing ? 'Enregistrer' : 'Créer le brouillon' }}</button>
        @unless ($editing)<a href="{{ route('organizer.index') }}" class="btn btn-ghost">Annuler</a>@endunless
    </div>
</form>
