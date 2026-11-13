{{-- Une demande reçue (trajet ou logement) : qui, quoi, message, statut et actions --}}
@props(['booking', 'showListing' => false])
@php $requester = $booking->requester(); @endphp
<article @class(['request', 'is-closed' => ! $booking->isActive()])>
    <div class="request-head">
        <x-avatar :user="$requester" />
        <div class="request-who">
            <strong>{{ $requester->name }}</strong>
            <small class="muted">
                @if ($showListing)<a href="{{ $booking->listingUrl() }}">{{ $booking->listingTitle() }}</a> · @endif
                {{ $booking->summary() }}
            </small>
        </div>
        <x-booking-status :booking="$booking" />
    </div>
    @if ($booking->message)<p class="request-message">« {{ $booking->message }} »</p>@endif
    @if ($requester->bio && $booking->isPending())<p class="request-bio muted small">{{ $requester->bio }}</p>@endif
    <x-booking-actions :booking="$booking" />
</article>
