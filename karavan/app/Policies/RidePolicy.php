<?php

namespace App\Policies;

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

    /** Réserver : pas son propre trajet, pas deux fois, et il doit rester de la place. */
    public function book(User $user, Ride $ride): Response
    {
        return match (true) {
            $ride->isDrivenBy($user) => Response::deny('Vous ne pouvez pas réserver votre propre trajet.'),
            $ride->isBookedBy($user) => Response::deny('Vous avez déjà réservé ce trajet.'),
            $ride->isFull() => Response::deny('Ce trajet est complet.'),
            default => Response::allow(),
        };
    }
}
