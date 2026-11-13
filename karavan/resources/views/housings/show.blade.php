{{--
    Détail d'un logement.
    $canBook : null (visiteur) ou réponse de la HousingPolicy.
--}}
@php
    $user = auth()->user();
    $canManage = $user?->can('update', $housing);
    $nights = $housing->defaultNights();
    $maxNights = config('karavan.max_nights');
    $myBooking = $user ? $housing->bookings->firstWhere('user_id', $user->id) : null;
    $pending = $housing->bookings->filter->isPending()->count();
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
                    <section class="card" id="demandes">
                        <h2 class="h4">Demandes reçues ({{ $housing->bookings->count() }})</h2>
                        <div class="stack">
                            @foreach ($housing->bookings->sortBy(fn ($b) => [$b->isPending() ? 0 : 1, $b->created_at]) as $booking)
                                <x-booking-request :booking="$booking" />
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="booking-box">
                <div class="booking-price">{{ euros($housing->price_per_night) }} <span>/ nuit</span></div>

                @if ($canManage)
                    @if ($housing->isHostedBy($user))
                        <p @class(['notice', 'notice-warning' => $pending])>
                            C'est votre logement.
                            {{ $pending ? plural($pending, 'demande attend', 'demandes attendent') . ' votre réponse.' : 'Aucune demande en attente.' }}
                            @if ($pending)<a href="#demandes">Répondre</a>@endif
                        </p>
                    @endif
                    <div class="owner-box">
                        <a href="{{ route('housings.edit', $housing) }}" class="btn btn-ghost btn-block"><x-icon name="edit" />Modifier le logement</a>
                        <x-delete-form :action="route('housings.destroy', $housing)" label="Supprimer le logement" class="btn btn-danger btn-block"
                            :confirm="'Supprimer ce logement ?' . ($housing->activeBookings()->exists() ? ' Les demandes en cours seront annulées et les voyageurs prévenus par email.' : '')" />
                    </div>
                @elseif ($canBook === null)
                    <a href="{{ route('login') }}" class="btn btn-primary btn-block">Se connecter pour réserver</a>
                @elseif ($myBooking?->isActive() || $myBooking?->status === \App\Enums\BookingStatus::Declined)
                    <div class="my-booking">
                        <div class="my-booking-head">Votre demande <x-booking-status :booking="$myBooking" /></div>
                        <p class="muted small">{{ $myBooking->summary() }} · {{ euros($myBooking->total()) }}</p>
                        <p class="small">
                            @if ($myBooking->isPending())
                                {{ $housing->host->first_name }} va accepter ou refuser votre demande : vous recevrez un email.
                            @elseif ($myBooking->isAccepted())
                                <x-icon name="check" class="icon-sm" />Séjour confirmé, à bientôt !
                            @else
                                {{ $canBook->message() }} <a href="{{ route('housings.index', ['festival' => $housing->festival_id]) }}">Voir d'autres logements</a>
                            @endif
                        </p>
                        <x-booking-actions :booking="$myBooking" block />
                    </div>
                @elseif ($canBook->denied())
                    <p class="notice">{{ $canBook->message() }}</p>
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
                        <button type="submit" class="btn btn-primary btn-block">Envoyer ma demande</button>
                        <p class="muted small center" style="margin: 0">{{ $housing->host->first_name }} acceptera ou refusera votre demande. Vous serez prévenu par email.</p>
                    </form>
                @endif
            </aside>
        </div>
    </div>
</x-layouts.app>
