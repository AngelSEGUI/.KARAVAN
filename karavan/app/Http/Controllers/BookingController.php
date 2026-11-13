<?php

namespace App\Http\Controllers;

use App\Models\HousingBooking;
use App\Models\RideBooking;
use App\Notifications\BookingAccepted;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingDeclined;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Réponses aux demandes de réservation (trajets et logements) :
 *   accepter / refuser  → par le conducteur ou l'hôte
 *   annuler             → par le passager ou le voyageur
 * Chaque action envoie un email à l'autre personne.
 *
 * Route : PATCH /demandes/{trajets|logements}/{id}/{accepter|refuser|annuler}
 */
class BookingController extends Controller
{
    private const MODELS = [
        'trajets' => RideBooking::class,
        'logements' => HousingBooking::class,
    ];

    public function update(string $type, int $booking, string $action): RedirectResponse
    {
        /** @var RideBooking|HousingBooking $booking */
        $booking = self::MODELS[$type]::findOrFail($booking);

        $decision = Gate::inspect($action === 'annuler' ? 'cancel' : 'respond', $booking);
        if ($decision->denied()) {
            return back()->with('error', $decision->message());
        }

        return match ($action) {
            'accepter' => $this->accept($booking),
            'refuser' => $this->decline($booking),
            'annuler' => $this->cancel($booking),
        };
    }

    private function accept(RideBooking|HousingBooking $booking): RedirectResponse
    {
        $booking->accept();
        $booking->requester()->notify(new BookingAccepted($booking));

        return back()->with('success', "Demande de {$booking->requester()->name} acceptée. Un email de confirmation lui a été envoyé.");
    }

    private function decline(RideBooking|HousingBooking $booking): RedirectResponse
    {
        $booking->decline();
        $booking->requester()->notify(new BookingDeclined($booking));

        return back()->with('info', "Demande de {$booking->requester()->name} refusée. Un email l'en a informé(e).");
    }

    private function cancel(RideBooking|HousingBooking $booking): RedirectResponse
    {
        $booking->cancel();
        $booking->owner()->notify(new BookingCancelled($booking));

        return back()->with('info', "Votre demande pour « {$booking->listingTitle()} » est annulée. {$booking->owner()->first_name} a été prévenu(e).");
    }
}
