<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Qui peut faire quoi sur un trajet. Découverte automatiquement par Laravel (App\Policies\{Modèle}Policy).
 * Utilisation : $this->authorize('update', $ride), @can('update', $ride), Gate::inspect('book', $ride)...
 */
class RidePolicy
{
    /** Le conducteur modifie son trajet ; un admin peut aussi intervenir. */
    public function update(User $user, Ride $ride): bool
    {
        return $ride->isDrivenBy($user) || $user->isAdmin();
    }

    public function delete(User $user, Ride $ride): bool
    {
        return $this->update($user, $ride);
    }

    /**
     * Demander une place : pas sur son propre trajet, une seule demande active,
     * pas de nouvelle demande après un refus, et il doit rester de la place.
     * Après une annulation par le passager, il peut redemander.
     */
    public function book(User $user, Ride $ride): Response
    {
        $existing = $ride->bookingOf($user);

        return match (true) {
            $ride->isDrivenBy($user) => Response::deny('Vous ne pouvez pas réserver votre propre trajet.'),
            $existing?->status === BookingStatus::Pending => Response::deny('Votre demande est en attente de réponse du conducteur.'),
            $existing?->status === BookingStatus::Accepted => Response::deny('Vous êtes déjà inscrit sur ce trajet.'),
            $existing?->status === BookingStatus::Declined => Response::deny("{$ride->driver->first_name} a refusé votre demande pour ce trajet."),
            $ride->isFull() => Response::deny('Ce trajet est complet.'),
            default => Response::allow(),
        };
    }
}
