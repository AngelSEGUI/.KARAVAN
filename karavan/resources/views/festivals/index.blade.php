<x-layouts.app title="Découvrir les festivals">
    <x-page-head eyebrow="Découverte" title="Trouvez votre prochain festival"
                 :lead="plural($festivals->total(), 'festival trouvé', 'festivals trouvés')" />

    <div class="container">
        <form class="filters" method="GET" action="{{ route('festivals.index') }}" data-autosubmit>
            <label class="field field-search">
                <span class="sr-only">Recherche</span>
                <x-icon name="search" />
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nom, ville, artiste…">
            </label>
            <label class="field">
                <span class="sr-only">Style</span>
                <select name="genre">
                    <option value="">Tous les styles</option>
                    @foreach ($genres as $genre)
                        <option value="{{ $genre }}" @selected(($filters['genre'] ?? '') === $genre)>{{ $genre }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">
                <span class="sr-only">Région</span>
                <select name="region">
                    <option value="">Toutes les régions</option>
                    @foreach ($regions as $region)
                        <option value="{{ $region }}" @selected(($filters['region'] ?? '') === $region)>{{ $region }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">
                <span class="sr-only">Mois</span>
                <select name="month">
                    <option value="">Tous les mois</option>
                    @foreach ([5, 6, 7, 8, 9] as $month)
                        <option value="{{ $month }}" @selected((int) ($filters['month'] ?? 0) === $month)>{{ ucfirst(\Illuminate\Support\Carbon::create(2000, $month, 1)->translatedFormat('F')) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">
                <span class="sr-only">Trier par</span>
                <select name="sort">
                    @foreach (['date' => 'Par date', 'popular' => 'Les plus fréquentés', 'offers' => "Le plus d'offres", 'name' => 'De A à Z'] as $value => $text)
                        <option value="{{ $value }}" @selected(($filters['sort'] ?? 'date') === $value)>{{ $text }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="btn btn-primary js-hide">Filtrer</button>
            @if (array_filter(\Illuminate\Support\Arr::except($filters, 'sort')))
                <a href="{{ route('festivals.index') }}" class="btn btn-ghost">Réinitialiser</a>
            @endif
        </form>

        @if ($festivals->isNotEmpty())
            <div class="grid grid-3 section-tight">
                @foreach ($festivals as $festival)
                    <x-festival-card :festival="$festival" />
                @endforeach
            </div>
            {{ $festivals->links() }}
        @else
            <x-empty title="Aucun festival sur cette route">
                <p>Essayez d'élargir vos filtres.</p>
                <a href="{{ route('festivals.index') }}" class="btn btn-primary">Voir tous les festivals</a>
            </x-empty>
        @endif
    </div>
</x-layouts.app>
