<?php

namespace App\Enums;

/**
 * Rôle d'un compte. Stocké en texte dans users.role.
 *   member : festivalier (covoiturage, logements)
 *   pro    : organisateur, peut en plus créer et personnaliser des pages de festival
 *   admin  : équipe Karavan, accès à tout
 */
enum UserRole: string
{
    case Member = 'member';
    case Pro = 'pro';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Member => 'Membre',
            self::Pro => 'Organisateur',
            self::Admin => 'Admin',
        };
    }

    public function pillClass(): string
    {
        return match ($this) {
            self::Member => 'pill',
            self::Pro => 'pill pill-ok',
            self::Admin => 'pill pill-admin',
        };
    }
}
