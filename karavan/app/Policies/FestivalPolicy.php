<?php

namespace App\Policies;

use App\Models\Festival;
use App\Models\User;

/**
 * Qui peut faire quoi sur un festival.
 *   - Les organisateurs (profil pro) créent des festivals et gèrent les leurs.
 *   - Les admins gèrent tous les festivals et décident de la mise « à la une ».
 *   - Un brouillon n'est visible que de son organisateur et des admins.
 */
class FestivalPolicy
{
    public function view(?User $user, Festival $festival): bool
    {
        return $festival->is_published || $festival->isOrganizedBy($user) || (bool) $user?->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->canOrganize();
    }

    public function update(User $user, Festival $festival): bool
    {
        return $user->isAdmin() || ($user->isPro() && $festival->isOrganizedBy($user));
    }

    public function delete(User $user, Festival $festival): bool
    {
        return $this->update($user, $festival);
    }

    public function feature(User $user): bool
    {
        return $user->isAdmin();
    }
}
