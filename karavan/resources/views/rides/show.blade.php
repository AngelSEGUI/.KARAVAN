{{--
    Détail d'un trajet.
    $canBook : null (visiteur) ou réponse de la RidePolicy (allowed() / message()).
--}}
@php
    $user = auth()->user();
    $left = $ride->seatsLeft();
    $isDriver = $ride->isDrivenBy($user);
    $canManage = $user?->can('update', $ride);
@endphp
<x-layouts.app :title="$ride->departure_city . ' → ' . $ride->festival->name">
    <div class="container">
        <a href="{{ route('rides.index') }}" class="back-link"><x-icon name="back" />Covoiturages</a>

        <div class="detail-layout">
            <div class="detail-main">
                <div class="ticket" style="--h: {{ $ride->festival->hue }}">
                    <span class="eyebrow">Covoiturage vers</span>
                    <a href="{{ route('festivals.show', $ride->festival) }}" class="ticket-festival">{{ $ride->festival->name }}</a>
                    <div class="timeline">
                        <div class="timeline-stop">
                            <span class="dot"></span>
                            <div>
                                <strong>{{ $ride->departure_city }}</strong>
                                <span>{{ ucfirst($ride->departs_at->translatedFormat('l j F · H\hi')) }}</span>
                                @if ($ride->meeting_point)<small>RDV : {{ $ride->meeting_point }}</small>@endif
                            </div>
                        </div>
                        <div class="timeline-stop">
                            <span class="dot dot-end"></span>
                            <div>
                                <strong>{{ $ride->festival->city }}</strong>
                                <span>{{ $ride->festival->name }}</span>
                            </div>
                        </div>
                    </div>
                    <p class="ticket-return">
                        @if ($ride->returns_at)
                            <x-icon name="back" class="icon-sm" />Retour prévu le {{ $ride->returns_at->translatedFormat('l j F · H\hi') }}
                        @else
                            Aller simple
                        @endif
                    </p>
                </div>

                <section class="card">
                    <h2 class="h4">Votre conducteur</h2>
                    <div class="person">
                        <x-avatar :user="$ride->driver" size="lg" />
                        <div>
                            <strong>{{ $ride->driver->name }}</strong>
                            <span class="muted">{{ $ride->driver->city }}@if ($ride->vehicle) · {{ $ride->vehicle }}@endif</span>
                        </div>
                    </div>
                    @if ($ride->driver->bio)<p class="muted" style="margin: 12px 0 0">{{ $ride->driver->bio }}</p>@endif
                    @if ($ride->notes)<blockquote>{!! nl2br(e($ride->notes)) !!}</blockquote>@endif
                </section>

                @if ($ride->bookings->isNotEmpty())
                    <section class="card">
                        <h2 class="h4">À bord ({{ $ride->bookedSeats() }})</h2>
                        <ul class="people-list">
                            @foreach ($ride->bookings as $booking)
                                <li>
                                    <x-avatar :user="$booking->passenger" size="sm" />
                                    {{ $booking->passenger->name }} <span class="muted">· {{ plural($booking->seats, 'place') }}</span>
                                    @if ($canManage && $booking->message)<small class="muted">« {{ $booking->message }} »</small>@endif
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>

            <aside class="booking-box">
                <div class="booking-price">{{ euros($ride->price) }} <span>/ place</span></div>
                <p @class(['seats-line', 'is-full' => $left === 0])>
                    {{ $left === 0 ? 'Complet' : plural($left, 'place restante', 'places restantes') }} sur {{ $ride->seats }}
                </p>

                @if ($canManage)
                    @if ($isDriver)<p class="notice">C'est votre trajet. Retrouvez vos passagers ci-contre et dans <a href="{{ route('account') }}">votre espace</a>.</p>@endif
                    <div class="owner-box">
                        <a href="{{ route('rides.edit', $ride) }}" class="btn btn-ghost btn-block"><x-icon name="edit" />Modifier le trajet</a>
                        <x-delete-form :action="route('rides.destroy', $ride)" label="Supprimer le trajet" class="btn btn-danger btn-block"
                            :confirm="'Supprimer ce trajet ?' . ($ride->hasBookings() ? ' Les ' . $ride->bookedSeats() . ' place(s) réservée(s) seront annulées.' : '')" />
                    </div>
                @elseif ($canBook === null)
                    <a href="{{ route('login') }}" class="btn btn-primary btn-block">Se connecter pour réserver</a>
                @elseif ($canBook->denied())
                    <p @class(['notice', 'notice-success' => $ride->isBookedBy($user)])>
                        @if ($ride->isBookedBy($user))<x-icon name="check" class="icon-sm" />Vous êtes inscrit sur ce trajet.
                        @else{{ $canBook->message() }} <a href="{{ route('rides.index', ['festival' => $ride->festival_id]) }}">Voir d'autres trajets</a>@endif
                    </p>
                @else
                    <form method="POST" action="{{ route('rides.book', $ride) }}" class="form" data-price="{{ $ride->price }}">
                        @csrf
                        <x-field name="seats" label="Nombre de places" type="select" data-qty
                                 :options="array_combine(range(1, $left), range(1, $left))" :value="1" />
                        <x-field name="message" type="textarea" rows="3" maxlength="500" optional
                                 :label="'Un mot pour ' . $ride->driver->first_name" placeholder="Bagages, musique, horaires…" />
                        <div class="total">Total <strong data-total>{{ euros($ride->price) }}</strong></div>
                        <button type="submit" class="btn btn-primary btn-block">Réserver</button>
                    </form>
                @endif
            </aside>
        </div>
    </div>
</x-layouts.app>
