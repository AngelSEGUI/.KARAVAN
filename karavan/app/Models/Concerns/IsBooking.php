<?php

namespace App\Models\Concerns;

use App\Enums\BookingStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Comportement commun aux réservations de trajets (RideBooking) et de logements (HousingBooking).
 * Chaque modèle fournit : owner() (conducteur/hôte), requester() (passager/voyageur),
 * listingTitle(), listingUrl() et summary().
 */
trait IsBooking
{
    public function initializeIsBooking(): void
    {
        $this->mergeCasts([
            'status' => BookingStatus::class,
            'responded_at' => 'datetime',
        ]);
        $this->attributes['status'] ??= BookingStatus::Pending->value;
    }

    /* ---------- Requêtes ---------- */

    /** Demandes en attente ou acceptées (elles occupent des places). */
    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', BookingStatus::active());
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', BookingStatus::Pending);
    }

    /* ---------- État ---------- */

    public function isPending(): bool
    {
        return $this->status === BookingStatus::Pending;
    }

    public function isAccepted(): bool
    {
        return $this->status === BookingStatus::Accepted;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isRequestedBy(?User $user): bool
    {
        return $user !== null && $user->id === $this->user_id;
    }

    /** URL d'une action : 'accepter', 'refuser' ou 'annuler' (route bookings.update). */
    public function actionUrl(string $action): string
    {
        $type = $this instanceof \App\Models\RideBooking ? 'trajets' : 'logements';

        return route('bookings.update', ['type' => $type, 'booking' => $this->id, 'action' => $action]);
    }

    /* ---------- Transitions ---------- */

    public function accept(): void
    {
        $this->moveTo(BookingStatus::Accepted);
    }

    public function decline(): void
    {
        $this->moveTo(BookingStatus::Declined);
    }

    public function cancel(): void
    {
        $this->moveTo(BookingStatus::Cancelled);
    }

    /** Nouvelle demande sur une ligne existante (après une annulation). */
    public function reopen(array $attributes): void
    {
        $this->fill($attributes);
        $this->status = BookingStatus::Pending;
        $this->responded_at = null;
        $this->save();
    }

    private function moveTo(BookingStatus $status): void
    {
        $this->status = $status;
        $this->responded_at = now();
        $this->save();
    }
}
