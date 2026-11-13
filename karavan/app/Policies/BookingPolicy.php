<?php

namespace App\Policies;

use App\Models\HousingBooking;
use App\Models\RideBooking;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Règles communes aux réservations de trajets et de logements
 * (RideBookingPolicy et HousingBookingPolicy en héritent).
 */
abstract class BookingPolicy
{
    /** Accepter ou refuser : le conducteur / l'hôte, tant que la demande est en attente. */
    public function respond(User $user, RideBooking|HousingBooking $booking): Response
    {
        if ($booking->owner()->isNot($user)) {
            return Response::deny("Seul l'auteur de l'annonce peut répondre à cette demande.");
        }

        return $booking->isPending()
            ? Response::allow()
            : Response::deny('Cette demande a déjà reçu une réponse (' . mb_strtolower($booking->status->label()) . ').');
    }

    /** Annuler : le demandeur, tant que la demande est en attente ou acceptée. */
    public function cancel(User $user, RideBooking|HousingBooking $booking): Response
    {
        if (! $booking->isRequestedBy($user)) {
            return Response::deny("Seule la personne qui a fait la demande peut l'annuler.");
        }

        return $booking->isActive()
            ? Response::allow()
            : Response::deny('Cette demande ne peut plus être annulée.');
    }
}
