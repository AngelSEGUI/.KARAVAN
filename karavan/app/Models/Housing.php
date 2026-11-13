<?php

namespace App\Models;

use App\Enums\HousingType;
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

    public function isRequestedBy(?User $user): bool
    {
        return $user !== null && $this->bookings()->where('user_id', $user->id)->exists();
    }

    /** Plus grand groupe parmi les demandes : la capacité ne peut pas descendre en dessous. */
    public function largestGroup(): int
    {
        return (int) $this->bookings()->max('guests');
    }

    /** Nombre de nuits proposé par défaut : la durée du festival. */
    public function defaultNights(): int
    {
        return $this->festival->durationInDays();
    }
}
