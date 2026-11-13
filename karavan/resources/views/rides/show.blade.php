{{--
    Détail d'un trajet.
    $canBook : null (visiteur) ou réponse de la RidePolicy (allowed() / message()).
--}}
@php
    $user = auth()->user();
    $left = $ride->seatsLeft();
    $isDriver = $ride->isDrivenBy($user);
    $canManage = $user?->can('update', $ride);
    $myBooking = $user ? $ride->bookings->firstWhere('user_id', $user->id) : null;
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

                @php $onBoard = $ride->bookings->filter->isAccepted(); @endphp
                @if ($onBoard->isNotEmpty())
                    <section class="card">
                        <h2 class="h4">À bord ({{ $onBoard->sum('seats') }})</h2>
                        <ul class="people-list">
                            @foreach ($onBoard as $booking)
                                <li><x-avatar :user="$booking->passenger" size="sm" />{{ $booking->passenger->name }} <span class="muted">· {{ plural($booking->seats, 'place') }}</span></li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($canManage && $ride->bookings->isNotEmpty())
                    <section class="card" id="demandes">
                        <h2 class="h4">Demandes reçues ({{ $ride->bookings->count() }})</h2>
                        <div class="stack">
                            @foreach ($ride->bookings->sortBy(fn ($b) => [$b->isPending() ? 0 : 1, $b->created_at]) as $booking)
                                <x-booking-request :booking="$booking" />
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="booking-box">
                <div class="booking-price">{{ euros($ride->price) }} <span>/ place</span></div>
                <p @class(['seats-line', 'is-full' => $left === 0])>
                    {{ $left === 0 ? 'Complet' : plural($left, 'place restante', 'places restantes') }} sur {{ $ride->seats }}
                </p>

                @if ($canManage)
                    @if ($isDriver)
                        @php $pending = $ride->bookings->filter->isPending()->count(); @endphp
                        <p @class(['notice', 'notice-warning' => $pending])>
                            C'est votre trajet.
                            {{ $pending ? plural($pending, 'demande attend', 'demandes attendent') . ' votre réponse.' : 'Aucune demande en attente.' }}
                            @if ($pending)<a href="#demandes">Répondre</a>@endif
                        </p>
                    @endif
                    <div class="owner-box">
                        <a href="{{ route('rides.edit', $ride) }}" class="btn btn-ghost btn-block"><x-icon name="edit" />Modifier le trajet</a>
                        <x-delete-form :action="route('rides.destroy', $ride)" label="Supprimer le trajet" class="btn btn-danger btn-block"
                            :confirm="'Supprimer ce trajet ?' . ($ride->hasBookings() ? ' Les demandes en cours seront annulées et les passagers prévenus par email.' : '')" />
                    </div>
                @elseif ($canBook === null)
                    <a href="{{ route('login') }}" class="btn btn-primary btn-block">Se connecter pour réserver</a>
                @elseif ($myBooking?->isActive() || $myBooking?->status === \App\Enums\BookingStatus::Declined)
                    {{-- Le membre a déjà une demande : on affiche son statut --}}
                    <div class="my-booking">
                        <div class="my-booking-head">Votre demande <x-booking-status :booking="$myBooking" /></div>
                        <p class="muted small">{{ $myBooking->summary() }} · {{ euros($myBooking->total()) }}</p>
                        <p class="small">
                            @if ($myBooking->isPending())
                                {{ $ride->driver->first_name }} va accepter ou refuser votre demande : vous recevrez un email.
                            @elseif ($myBooking->isAccepted())
                                <x-icon name="check" class="icon-sm" />C'est confirmé, bonne route !
                            @else
                                {{ $canBook->message() }} <a href="{{ route('rides.index', ['festival' => $ride->festival_id]) }}">Voir d'autres trajets</a>
                            @endif
                        </p>
                        <x-booking-actions :booking="$myBooking" block />
                    </div>
                @elseif ($canBook->denied())
                    <p class="notice">{{ $canBook->message() }} <a href="{{ route('rides.index', ['festival' => $ride->festival_id]) }}">Voir d'autres trajets</a></p>
                @else
                    <form method="POST" action="{{ route('rides.book', $ride) }}" class="form" data-price="{{ $ride->price }}">
                        @csrf
                        <x-field name="seats" label="Nombre de places" type="select" data-qty
                                 :options="array_combine(range(1, $left), range(1, $left))" :value="1" />
                        <x-field name="message" type="textarea" rows="3" maxlength="500" optional
                                 :label="'Un mot pour ' . $ride->driver->first_name" placeholder="Bagages, musique, horaires…" />
                        <div class="total">Total <strong data-total>{{ euros($ride->price) }}</strong></div>
                        <button type="submit" class="btn btn-primary btn-block">Envoyer ma demande</button>
                        <p class="muted small center" style="margin: 0">{{ $ride->driver->first_name }} acceptera ou refusera votre demande. Vous serez prévenu par email.</p>
                    </form>
                @endif
            </aside>
        </div>
    </div>
</x-layouts.app>
