<x-layouts.app title="Mon espace">
    <section class="page-head">
        <div class="container page-head-row">
            <div class="person">
                <x-avatar :user="$user" size="xl" />
                <div>
                    <span class="eyebrow">Mon espace</span>
                    <h1 style="margin: 0">{{ $user->name }}</h1>
                    @if ($user->city)<p class="muted">{{ $user->city }}</p>@endif
                </div>
            </div>
            <a href="{{ route('profile.edit') }}" class="btn btn-ghost"><x-icon name="edit" />Modifier mon profil</a>
        </div>
    </section>

    <div class="container account">
        @unless ($user->hasVerifiedEmail())
            <div class="notice notice-warning verify-banner">
                <x-icon name="mail" />
                <span>Confirmez votre adresse <strong>{{ $user->email }}</strong> pour publier des annonces et réserver.</span>
                <a href="{{ route('verification.notice') }}" class="btn btn-ghost btn-sm">Confirmer</a>
            </div>
        @endunless

        {{-- Demandes reçues sur mes annonces --}}
        @if ($requests->isNotEmpty())
            <section id="demandes">
                <h2 class="h3"><x-icon name="inbox" class="icon-sm" />Demandes à traiter <span class="count">{{ $requests->count() }}</span></h2>
                <p class="muted">Acceptez ou refusez : le demandeur est prévenu par email.</p>
                <div class="grid grid-2">
                    @foreach ($requests as $booking)
                        <x-booking-request :booking="$booking" show-listing />
                    @endforeach
                </div>
            </section>
        @endif

        <section>
            <h2 class="h3">Mes réservations</h2>
            @if ($rideBookings->isEmpty() && $stayBookings->isEmpty())
                <x-empty small>
                    <p>Aucune réservation pour l'instant.</p>
                    <a href="{{ route('festivals.index') }}" class="btn btn-primary btn-sm">Trouver un festival</a>
                </x-empty>
            @else
                <ul class="list-rows">
                    @foreach ($rideBookings as $booking)
                        <li style="--h: {{ $booking->ride->festival->hue }}" @class(['is-closed' => ! $booking->isActive()])>
                            <div class="list-row">
                                <a href="{{ route('rides.show', $booking->ride) }}" class="list-row-link">
                                    <span class="row-icon"><x-icon name="car" /></span>
                                    <span class="row-main">
                                        <strong>{{ $booking->ride->departure_city }} → {{ $booking->ride->festival->name }}</strong>
                                        <small>{{ ucfirst($booking->ride->departs_at->translatedFormat('D j M · H\hi')) }} · avec {{ $booking->ride->driver->name }}</small>
                                    </span>
                                    <span class="row-side">{{ plural($booking->seats, 'place') }}<small>{{ euros($booking->total()) }}</small></span>
                                </a>
                                <div class="row-status">
                                    <x-booking-status :booking="$booking" />
                                    <x-booking-actions :booking="$booking" />
                                </div>
                            </div>
                        </li>
                    @endforeach
                    @foreach ($stayBookings as $booking)
                        <li style="--h: {{ $booking->housing->festival->hue }}" @class(['is-closed' => ! $booking->isActive()])>
                            <div class="list-row">
                                <a href="{{ route('housings.show', $booking->housing) }}" class="list-row-link">
                                    <span class="row-icon"><x-icon name="home" /></span>
                                    <span class="row-main">
                                        <strong>{{ $booking->housing->title }}</strong>
                                        <small>{{ $booking->housing->festival->name }} · chez {{ $booking->housing->host->name }}</small>
                                    </span>
                                    <span class="row-side">{{ plural($booking->nights, 'nuit') }}<small>{{ euros($booking->total()) }}</small></span>
                                </a>
                                <div class="row-status">
                                    <x-booking-status :booking="$booking" />
                                    <x-booking-actions :booking="$booking" />
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section>
            <div class="section-head">
                <h2 class="h3">Mes trajets proposés</h2>
                <a href="{{ route('rides.create') }}" class="btn btn-ghost btn-sm"><x-icon name="plus" />Nouveau trajet</a>
            </div>
            @if ($rides->isNotEmpty())
                <div class="grid grid-2">
                    @foreach ($rides as $ride)
                        @php
                            $info = $ride->pending_count
                                ? plural($ride->pending_count, 'demande en attente', 'demandes en attente')
                                : ($ride->hasBookings() ? plural($ride->bookedSeats(), 'place réservée', 'places réservées') : 'Aucune demande');
                        @endphp
                        <div class="with-actions">
                            <x-ride-card :ride="$ride" />
                            <x-owner-actions :edit="route('rides.edit', $ride)" :destroy="route('rides.destroy', $ride)" :info="$info"
                                :confirm="'Supprimer ce trajet ?' . ($ride->hasBookings() ? ' Les demandes en cours seront annulées et les passagers prévenus par email.' : '')" />
                        </div>
                    @endforeach
                </div>
            @else
                <p class="muted">Vous n'avez pas encore proposé de trajet.</p>
            @endif
        </section>

        <section>
            <div class="section-head">
                <h2 class="h3">Mes logements proposés</h2>
                <a href="{{ route('housings.create') }}" class="btn btn-ghost btn-sm"><x-icon name="plus" />Nouveau logement</a>
            </div>
            @if ($housings->isNotEmpty())
                <div class="grid grid-3">
                    @foreach ($housings as $housing)
                        @php
                            $info = $housing->pending_count
                                ? plural($housing->pending_count, 'demande en attente', 'demandes en attente')
                                : ($housing->accepted_count ? plural($housing->accepted_count, 'séjour accepté', 'séjours acceptés') : 'Aucune demande');
                        @endphp
                        <div class="with-actions">
                            <x-housing-card :housing="$housing" />
                            <x-owner-actions :edit="route('housings.edit', $housing)" :destroy="route('housings.destroy', $housing)" :info="$info"
                                :confirm="'Supprimer ce logement ?' . (($housing->pending_count + $housing->accepted_count) ? ' Les demandes en cours seront annulées et les voyageurs prévenus par email.' : '')" />
                        </div>
                    @endforeach
                </div>
            @else
                <p class="muted">Vous n'avez pas encore proposé de logement.</p>
            @endif
        </section>
    </div>
</x-layouts.app>
