{{-- Carte d'un logement. Charger la relation festival pour éviter des requêtes. --}}
@props(['housing', 'showFestival' => true])
<article class="housing-card" style="--h: {{ $housing->festival->hue }}">
    <a href="{{ route('housings.show', $housing) }}" class="card-link" aria-label="{{ $housing->title }}"></a>
    <div class="housing-visual">
        <x-icon :name="$housing->type->icon()" />
        <span class="distance">{{ km($housing->distance_km) }}</span>
    </div>
    <div class="housing-body">
        <span class="eyebrow">{{ $housing->type->label() }}</span>
        <h3>{{ $housing->title }}</h3>
        <p class="muted">
            <x-icon name="pin" class="icon-sm" />{{ $housing->city }}
            @if ($showFestival) · près de <strong>{{ $housing->festival->name }}</strong>@endif
        </p>
        <div class="housing-foot">
            <span class="muted"><x-icon name="users" class="icon-sm" />{{ $housing->capacity }} pers.</span>
            <span><strong>{{ euros($housing->price_per_night) }}</strong> <span class="muted">/ nuit</span></span>
        </div>
    </div>
</article>
