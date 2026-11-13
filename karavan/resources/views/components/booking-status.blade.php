{{-- Pastille de statut d'une réservation : En attente / Acceptée / Refusée / Annulée --}}
@props(['booking'])
<span {{ $attributes->merge(['class' => $booking->status->pillClass()]) }}>{{ $booking->status->label() }}</span>
