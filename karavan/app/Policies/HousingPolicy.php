<?php

namespace App\Policies;

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

    /** Demander un séjour : pas chez soi, et une seule demande par logement. */
    public function book(User $user, Housing $housing): Response
    {
        return match (true) {
            $housing->isHostedBy($user) => Response::deny('Vous ne pouvez pas réserver votre propre logement.'),
            $housing->isRequestedBy($user) => Response::deny('Vous avez déjà envoyé une demande pour ce logement.'),
            default => Response::allow(),
        };
    }
}
