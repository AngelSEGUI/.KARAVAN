<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Notifications\ListingCancelled;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trajet en covoiturage proposé par un membre (le conducteur) vers un festival.
 *
 * @property-read int|null $booked_seats  disponible après withBookedSeats()
 */
#[Fillable([
    'festival_id', 'departure_city', 'meeting_point', 'departs_at', 'returns_at',
    'seats', 'price', 'vehicle', 'notes',
])]
class Ride extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'departs_at' => 'datetime',
            'returns_at' => 'datetime',
            'seats' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        // Annonce supprimée (par son auteur, un admin, ou avec son festival / le compte de son auteur) :
        // chaque personne ayant une demande en cours reçoit un email d'annulation.
        static::deleting(function (Ride $ride) {
            $ride->activeBookings()->with(['passenger', 'ride.festival'])->get()
                ->each(fn ($booking) => $booking->requester()->notify(new ListingCancelled($booking)));
        });
    }

    /* ---------- Relations ---------- */

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function festival(): BelongsTo
    {
        return $this->belongsTo(Festival::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(RideBooking::class);
    }

    /* ---------- Requêtes ---------- */

    /** Demandes en attente ou acceptées : elles occupent des places. */
    public function activeBookings(): HasMany
    {
        return $this->bookings()->whereIn('status', BookingStatus::active());
    }

    /** Ajoute booked_seats (places occupées par les demandes actives). */
    public function scopeWithBookedSeats(Builder $query): void
    {
        $query->withSum('activeBookings as booked_seats', 'seats');
    }

    /** @param array{festival?: ?string, from?: ?string, seats?: ?string} $filters */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['festival'] ?? null, fn (Builder $q, $id) => $q->where('festival_id', $id))
            ->when($filters['from'] ?? null, fn (Builder $q, $city) => $q->where('departure_city', 'like', '%' . $city . '%'))
            ->when($filters['seats'] ?? null, fn (Builder $q, $n) => $q->whereRaw(
                "seats - coalesce((select sum(seats) from ride_bookings where ride_bookings.ride_id = rides.id and status in ('pending', 'accepted')), 0) >= ?",
                [(int) $n]
            ))
            ->orderBy('departs_at');
    }

    /* ---------- Règles métier ---------- */

    public function bookedSeats(): int
    {
        return (int) ($this->booked_seats ?? $this->activeBookings()->sum('seats'));
    }

    public function seatsLeft(): int
    {
        return max(0, $this->seats - $this->bookedSeats());
    }

    public function isFull(): bool
    {
        return $this->seatsLeft() === 0;
    }

    public function isDrivenBy(?User $user): bool
    {
        return $user !== null && $user->id === $this->user_id;
    }

    /** Réservation (quel que soit son statut) d'un membre sur ce trajet. */
    public function bookingOf(?User $user): ?RideBooking
    {
        return $user ? $this->bookings()->where('user_id', $user->id)->first() : null;
    }

    public function pendingCount(): int
    {
        return $this->bookings()->pending()->count();
    }

    public function hasBookings(): bool
    {
        return $this->bookedSeats() > 0;
    }
}
