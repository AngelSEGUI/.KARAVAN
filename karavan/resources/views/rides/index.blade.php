<x-layouts.app title="Covoiturages vers les festivals">
    <x-page-head eyebrow="Covoiturage" title="Partagez la route"
                 lead="Rejoignez des festivaliers qui partent de chez vous et divisez les frais.">
        <a href="{{ route('rides.create') }}" class="btn btn-primary"><x-icon name="plus" />Proposer un trajet</a>
    </x-page-head>

    <div class="container">
        <form class="filters" method="GET" action="{{ route('rides.index') }}" data-autosubmit>
            <label class="field field-search">
                <span class="sr-only">Ville de départ</span>
                <x-icon name="pin" />
                <input type="search" name="from" value="{{ $filters['from'] ?? '' }}" placeholder="Ville de départ" list="cities">
                <datalist id="cities">
                    @foreach ($cities as $city)<option value="{{ $city }}">@endforeach
                </datalist>
            </label>
            <label class="field">
                <span class="sr-only">Festival</span>
                <select name="festival">
                    <option value="">Tous les festivals</option>
                    @foreach ($festivals as $f)
                        <option value="{{ $f->id }}" @selected((string) ($filters['festival'] ?? '') === (string) $f->id)>{{ $f->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">
                <span class="sr-only">Places</span>
                <select name="seats">
                    <option value="">Places libres</option>
                    @foreach ([1, 2, 3, 4] as $n)
                        <option value="{{ $n }}" @selected((int) ($filters['seats'] ?? 0) === $n)>{{ $n }}+ place{{ $n > 1 ? 's' : '' }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="btn btn-primary js-hide">Rechercher</button>
        </form>

        <p class="results-count">{{ plural($rides->total(), 'trajet') }}</p>

        @if ($rides->isNotEmpty())
            <div class="grid grid-2">
                @foreach ($rides as $ride)
                    <x-ride-card :ride="$ride" />
                @endforeach
            </div>
            {{ $rides->links() }}
        @else
            <x-empty icon="car" title="Aucun trajet ne correspond">
                <p>Personne ne part encore d'ici. Et si c'était vous&nbsp;?</p>
                <a href="{{ route('rides.create') }}" class="btn btn-primary">Proposer un trajet</a>
            </x-empty>
        @endif
    </div>
</x-layouts.app>
