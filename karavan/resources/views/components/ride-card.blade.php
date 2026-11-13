{{-- Carte « billet » d'un covoiturage. Charger driver + festival et withBookedSeats() pour éviter des requêtes. --}}
@props(['ride', 'showFestival' => true])
@php $left = $ride->seatsLeft(); @endphp
<article class="ride-card" style="--h: {{ $ride->festival->hue }}">
    <a href="{{ route('rides.show', $ride) }}" class="card-link" aria-label="Trajet depuis {{ $ride->departure_city }} vers {{ $ride->festival->name }}"></a>
    <div class="ride-main">
        <div class="ride-when">{{ ucfirst($ride->departs_at->translatedFormat('D j M · H\hi')) }}</div>
        <div class="ride-route">
            <span class="ride-city">{{ $ride->departure_city }}</span>
            <x-icon name="arrow" class="ride-arrow" />
            <span class="ride-city ride-dest">{{ $showFestival ? $ride->festival->name : $ride->festival->city }}</span>
        </div>
        <div class="ride-driver">
            <x-avatar :user="$ride->driver" size="sm" />
            <span>{{ $ride->driver->name }}@if ($ride->vehicle) · <span class="muted">{{ $ride->vehicle }}</span>@endif</span>
        </div>
    </div>
    <div class="ride-stub">
        <strong class="ride-price">{{ euros($ride->price) }}</strong>
        <span class="muted">par place</span>
        <span @class(['seats', 'seats-full' => $left === 0])>{{ $left === 0 ? 'Complet' : plural($left, 'place') }}</span>
    </div>
</article>
