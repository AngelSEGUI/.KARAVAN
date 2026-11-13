<x-layouts.app title="Logements près des festivals">
    <x-page-head eyebrow="Logements" title="Dormir près des scènes"
                 lead="Un jardin pour la tente, une chambre, un van : la communauté vous accueille.">
        <a href="{{ route('housings.create') }}" class="btn btn-primary"><x-icon name="plus" />Proposer un logement</a>
    </x-page-head>

    <div class="container">
        <form class="filters" method="GET" action="{{ route('housings.index') }}" data-autosubmit>
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
                <span class="sr-only">Type</span>
                <select name="type">
                    <option value="">Tous les types</option>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">
                <span class="sr-only">Voyageurs</span>
                <select name="guests">
                    <option value="">Voyageurs</option>
                    @foreach ([1, 2, 3, 4, 6] as $n)
                        <option value="{{ $n }}" @selected((int) ($filters['guests'] ?? 0) === $n)>{{ $n }}+ pers.</option>
                    @endforeach
                </select>
            </label>
            <label class="field">
                <span class="sr-only">Budget</span>
                <select name="max_price">
                    <option value="">Tous budgets</option>
                    @foreach ([20, 50, 100] as $p)
                        <option value="{{ $p }}" @selected((int) ($filters['max_price'] ?? 0) === $p)>≤ {{ $p }} € / nuit</option>
                    @endforeach
                </select>
            </label>
            <label class="field">
                <span class="sr-only">Trier</span>
                <select name="sort">
                    <option value="distance">Plus proches</option>
                    <option value="price" @selected(($filters['sort'] ?? '') === 'price')>Moins chers</option>
                </select>
            </label>
            <button type="submit" class="btn btn-primary js-hide">Rechercher</button>
        </form>

        <p class="results-count">{{ plural($housings->total(), 'logement') }}</p>

        @if ($housings->isNotEmpty())
            <div class="grid grid-3">
                @foreach ($housings as $housing)
                    <x-housing-card :housing="$housing" />
                @endforeach
            </div>
            {{ $housings->links() }}
        @else
            <x-empty icon="tent" title="Aucun logement ne correspond">
                <p>Élargissez vos critères ou proposez votre propre logement.</p>
                <a href="{{ route('housings.index') }}" class="btn btn-primary">Voir tous les logements</a>
            </x-empty>
        @endif
    </div>
</x-layouts.app>
