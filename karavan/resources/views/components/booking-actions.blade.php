{{--
    Boutons d'action sur une demande, selon qui regarde :
      - l'auteur de l'annonce voit « Accepter / Refuser » tant que la demande est en attente ;
      - le demandeur voit « Annuler » tant que la demande est en attente ou acceptée.
    Les droits sont vérifiés côté serveur (App\Policies\BookingPolicy).
--}}
@props(['booking', 'block' => false])
@php $user = auth()->user(); @endphp
@if ($user?->can('respond', $booking))
    <div {{ $attributes->class(['booking-actions', 'booking-actions-block' => $block]) }}>
        <form method="POST" action="{{ $booking->actionUrl('accepter') }}" class="inline-form">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" />Accepter</button>
        </form>
        <form method="POST" action="{{ $booking->actionUrl('refuser') }}" class="inline-form"
              data-confirm="Refuser la demande de {{ $booking->requester()->name }} ? Un email l'en informera.">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-ghost btn-sm"><x-icon name="x" />Refuser</button>
        </form>
    </div>
@elseif ($user?->can('cancel', $booking))
    <div {{ $attributes->class(['booking-actions', 'booking-actions-block' => $block]) }}>
        <form method="POST" action="{{ $booking->actionUrl('annuler') }}" class="inline-form"
              data-confirm="Annuler votre demande ? {{ $booking->owner()->first_name }} sera prévenu(e) par email.">
            @csrf @method('PATCH')
            <button type="submit" @class(['btn btn-ghost btn-sm', 'btn-block' => $block])><x-icon name="x" />Annuler ma demande</button>
        </form>
    </div>
@endif
