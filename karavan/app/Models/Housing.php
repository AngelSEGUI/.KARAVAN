<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\HousingType;
use App\Notifications\ListingCancelled;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Logement proposé par un membre (l'hôte) à proximité d'un festival. */
#[Fillable([
    'festival_id', 'title', 'type', 'city', 'distance_km', 'capacity',
    'price_per_night', 'description', 'amenities',
])]
class Housing extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => HousingType::class,
            'distance_km' => 'decimal:1',
            'capacity' => 'integer',
            'price_per_night' => 'decimal:2',
            'amenities' => 'array',
        ];
    }

    protected static function booted(): void
    {
        // Annonce supprimée (par son auteur, un admin, ou avec son festival / le compte de son auteur) :
        // chaque personne ayant une demande en cours reçoit un email d'annulation.
        static::deleting(function (Housing $housing) {
            $housing->activeBookings()->with(['guest', 'housing.festival'])->get()
                ->each(fn ($booking) => $booking->requester()->notify(new ListingCancelled($booking)));
        });
    }

    /* ---------- Relations ---------- */

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function festival(): BelongsTo
    {
        return $this->belongsTo(Festival::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(HousingBooking::class);
    }

    /* ---------- Requêtes ---------- */

    /** @param array{festival?: ?string, type?: ?string, guests?: ?string, max_price?: ?string, sort?: ?string} $filters */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['festival'] ?? null, fn (Builder $q, $id) => $q->where('festival_id', $id))
            ->when(HousingType::tryFrom((string) ($filters['type'] ?? '')), fn (Builder $q, HousingType $t) => $q->where('type', $t))
            ->when($filters['guests'] ?? null, fn (Builder $q, $n) => $q->where('capacity', '>=', (int) $n))
            ->when($filters['max_price'] ?? null, fn (Builder $q, $p) => $q->where('price_per_night', '<=', (float) $p));

        ($filters['sort'] ?? null) === 'price'
            ? $query->orderBy('price_per_night')
            : $query->orderBy('distance_km');
    }

    /* ---------- Règles métier ---------- */

    public function isHostedBy(?User $user): bool
    {
        return $user !== null && $user->id === $this->user_id;
    }

    /** Demandes en attente ou acceptées. */
    public function activeBookings(): HasMany
    {
        return $this->bookings()->whereIn('status', BookingStatus::active());
    }

    /** Demande (quel que soit son statut) d'un membre sur ce logement. */
    public function bookingOf(?User $user): ?HousingBooking
    {
        return $user ? $this->bookings()->where('user_id', $user->id)->first() : null;
    }

    /** Plus grand groupe parmi les demandes actives : la capacité ne peut pas descendre en dessous. */
    public function largestGroup(): int
    {
        return (int) $this->activeBookings()->max('guests');
    }

    /** Nombre de nuits proposé par défaut : la durée du festival. */
    public function defaultNights(): int
    {
        return $this->festival->durationInDays();
    }
}
