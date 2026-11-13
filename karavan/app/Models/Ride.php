<?php

namespace App\Models;

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

    /** Ajoute booked_seats (somme des places réservées). */
    public function scopeWithBookedSeats(Builder $query): void
    {
        $query->withSum('bookings as booked_seats', 'seats');
    }

    /** @param array{festival?: ?string, from?: ?string, seats?: ?string} $filters */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['festival'] ?? null, fn (Builder $q, $id) => $q->where('festival_id', $id))
            ->when($filters['from'] ?? null, fn (Builder $q, $city) => $q->where('departure_city', 'like', '%' . $city . '%'))
            ->when($filters['seats'] ?? null, fn (Builder $q, $n) => $q->whereRaw(
                'seats - coalesce((select sum(seats) from ride_bookings where ride_bookings.ride_id = rides.id), 0) >= ?',
                [(int) $n]
            ))
            ->orderBy('departs_at');
    }

    /* ---------- Règles métier ---------- */

    public function bookedSeats(): int
    {
        return (int) ($this->booked_seats ?? $this->bookings()->sum('seats'));
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

    public function isBookedBy(?User $user): bool
    {
        return $user !== null && $this->bookings()->where('user_id', $user->id)->exists();
    }

    public function hasBookings(): bool
    {
        return $this->bookedSeats() > 0;
    }
}
