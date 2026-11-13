<?php

namespace App\Enums;

/**
 * Cycle de vie d'une réservation (trajet ou logement) :
 *
 *   en attente ──(propriétaire accepte)──▶ acceptée
 *       │  └────(propriétaire refuse)───▶ refusée
 *       └──────(demandeur annule)───────▶ annulée   (aussi possible depuis « acceptée »)
 *
 * Une demande en attente ou acceptée est « active » : elle occupe des places.
 */
enum BookingStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Accepted => 'Acceptée',
            self::Declined => 'Refusée',
            self::Cancelled => 'Annulée',
        };
    }

    /** Classe CSS de la pastille (components.css §2). */
    public function pillClass(): string
    {
        return match ($this) {
            self::Pending => 'pill pill-warn',
            self::Accepted => 'pill pill-ok',
            self::Declined => 'pill pill-danger',
            self::Cancelled => 'pill',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, self::active(), true);
    }

    /** @return list<self> */
    public static function active(): array
    {
        return [self::Pending, self::Accepted];
    }
}
