<?php

namespace App\Enums;

/** Rôle d'un compte. Stocké en texte dans users.role. */
enum UserRole: string
{
    case Member = 'member';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Member => 'Membre',
            self::Admin => 'Admin',
        };
    }
}
