<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\Housing;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/** Qui peut faire quoi sur un logement. */
class HousingPolicy
{
    /** L'hôte modifie son logement ; un admin peut aussi intervenir. */
    public function update(User $user, Housing $housing): bool
    {
        return $housing->isHostedBy($user) || $user->isAdmin();
    }

    public function delete(User $user, Housing $housing): bool
    {
        return $this->update($user, $housing);
    }

    /** Demander un séjour : pas chez soi, une seule demande active, pas de nouvelle demande après un refus. */
    public function book(User $user, Housing $housing): Response
    {
        $existing = $housing->bookingOf($user);

        return match (true) {
            $housing->isHostedBy($user) => Response::deny('Vous ne pouvez pas réserver votre propre logement.'),
            $existing?->status === BookingStatus::Pending => Response::deny("Votre demande est en attente de réponse de l'hôte."),
            $existing?->status === BookingStatus::Accepted => Response::deny("{$housing->host->first_name} a accepté votre séjour."),
            $existing?->status === BookingStatus::Declined => Response::deny("{$housing->host->first_name} a refusé votre demande pour ce logement."),
            default => Response::allow(),
        };
    }
}
