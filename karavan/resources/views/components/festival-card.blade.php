{{-- Carte d'un festival (accueil, catalogue). Attend rides_count / housings_count (scope withOfferCounts). --}}
@props(['festival'])
<article class="festival-card">
    <a href="{{ route('festivals.show', $festival) }}" class="card-link" aria-label="{{ $festival->name }}"></a>
    <x-cover :name="$festival->name" :hue="$festival->hue" />
    <div class="festival-card-body">
        <div class="date-chip">
            <strong>{{ $festival->starts_on->day }}</strong>
            <span>{{ rtrim($festival->starts_on->translatedFormat('M'), '.') }}</span>
        </div>
        <div>
            <h3>{{ $festival->name }}</h3>
            <p class="muted"><x-icon name="pin" class="icon-sm" />{{ $festival->city }} · {{ $festival->dateRange() }}</p>
        </div>
    </div>
    <div class="festival-card-foot">
        <div class="tags">
            @foreach (array_slice($festival->genres, 0, 2) as $genre)
                <span class="tag">{{ $genre }}</span>
            @endforeach
        </div>
        <div class="offer-counts">
            <span title="Covoiturages"><x-icon name="car" class="icon-sm" />{{ $festival->rides_count ?? 0 }}</span>
            <span title="Logements"><x-icon name="home" class="icon-sm" />{{ $festival->housings_count ?? 0 }}</span>
        </div>
    </div>
</article>
