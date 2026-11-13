{{--
    Détail d'un logement.
    $canBook : null (visiteur) ou réponse de la HousingPolicy.
--}}
@php
    $user = auth()->user();
    $canManage = $user?->can('update', $housing);
    $nights = $housing->defaultNights();
    $maxNights = config('karavan.max_nights');
@endphp
<x-layouts.app :title="$housing->title">
    <div class="container">
        <a href="{{ route('housings.index') }}" class="back-link"><x-icon name="back" />Logements</a>

        <div class="detail-layout">
            <div class="detail-main">
                <div class="housing-banner" style="--h: {{ $housing->festival->hue }}">
                    <x-icon :name="$housing->type->icon()" />
                    <div>
                        <span class="eyebrow">{{ $housing->type->label() }}</span>
                        <h1>{{ $housing->title }}</h1>
                        <p><x-icon name="pin" class="icon-sm" />{{ $housing->city }} · à {{ km($housing->distance_km) }} de
                            <a href="{{ route('festivals.show', $housing->festival) }}">{{ $housing->festival->name }}</a></p>
                    </div>
                </div>

                <section class="card">
                    <div class="facts-inline">
                        <span><x-icon name="users" />Jusqu'à {{ $housing->capacity }} pers.</span>
                        <span><x-icon name="calendar" />{{ $housing->festival->dateRange() }}</span>
                    </div>
                    @if ($housing->description)<p>{!! nl2br(e($housing->description)) !!}</p>@endif
                    @if ($housing->amenities)
                        <h2 class="h4">Équipements</h2>
                        <ul class="amenities">
                            @foreach ($housing->amenities as $amenity)
                                <li><x-icon name="check" class="icon-sm" />{{ $amenity }}</li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="card">
                    <h2 class="h4">Votre hôte</h2>
                    <div class="person">
                        <x-avatar :user="$housing->host" size="lg" />
                        <div>
                            <strong>{{ $housing->host->name }}</strong>
                            <span class="muted">{{ $housing->host->city ?: 'Membre de la communauté Karavan' }}</span>
                        </div>
                    </div>
                    @if ($housing->host->bio)<p class="muted" style="margin: 12px 0 0">{{ $housing->host->bio }}</p>@endif
                </section>

                @if ($canManage && $housing->bookings->isNotEmpty())
                    <section class="card">
                        <h2 class="h4">Demandes reçues ({{ $housing->bookings->count() }})</h2>
                        <ul class="people-list">
                            @foreach ($housing->bookings as $booking)
                                <li>
                                    <x-avatar :user="$booking->guest" size="sm" />
                                    {{ $booking->guest->name }}
                                    <span class="muted">· {{ $booking->guests }} pers., {{ plural($booking->nights, 'nuit') }}</span>
                                    @if ($booking->message)<small class="muted">« {{ $booking->message }} »</small>@endif
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>

            <aside class="booking-box">
                <div class="booking-price">{{ euros($housing->price_per_night) }} <span>/ nuit</span></div>

                @if ($canManage)
                    @if ($housing->isHostedBy($user))
                        <p class="notice">C'est votre logement. {{ $housing->bookings->isEmpty() ? "Aucune demande pour l'instant." : plural($housing->bookings->count(), 'demande reçue', 'demandes reçues') . '.' }}</p>
                    @endif
                    <div class="owner-box">
                        <a href="{{ route('housings.edit', $housing) }}" class="btn btn-ghost btn-block"><x-icon name="edit" />Modifier le logement</a>
                        <x-delete-form :action="route('housings.destroy', $housing)" label="Supprimer le logement" class="btn btn-danger btn-block"
                            :confirm="'Supprimer ce logement ?' . ($housing->bookings->isNotEmpty() ? ' Les ' . $housing->bookings->count() . ' demande(s) reçue(s) seront annulées.' : '')" />
                    </div>
                @elseif ($canBook === null)
                    <a href="{{ route('login') }}" class="btn btn-primary btn-block">Se connecter pour réserver</a>
                @elseif ($canBook->denied())
                    <p class="notice notice-success"><x-icon name="check" class="icon-sm" />{{ $canBook->message() }}</p>
                @else
                    <form method="POST" action="{{ route('housings.book', $housing) }}" class="form" data-price="{{ $housing->price_per_night }}">
                        @csrf
                        <div class="form-row">
                            <x-field name="guests" label="Voyageurs" type="select" :value="1"
                                     :options="array_combine(range(1, $housing->capacity), range(1, $housing->capacity))" />
                            <x-field name="nights" label="Nuits" type="select" data-qty :value="min($nights, $maxNights)"
                                     :options="array_combine(range(1, $maxNights), range(1, $maxNights))" />
                        </div>
                        <x-field name="message" label="Message à l'hôte" type="textarea" rows="3" maxlength="500" optional
                                 placeholder="Présentez-vous, heure d'arrivée…" />
                        <div class="total">Total <strong data-total>{{ euros($housing->price_per_night * $nights) }}</strong></div>
                        <button type="submit" class="btn btn-primary btn-block">Envoyer la demande</button>
                    </form>
                @endif
            </aside>
        </div>
    </div>
</x-layouts.app>
