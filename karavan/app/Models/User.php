<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Notifications\BookingCancelled;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * Membre de la communauté. Un même membre peut être conducteur, passager, hôte et voyageur.
 */
#[Fillable(['name', 'email', 'password', 'city', 'bio', 'organization', 'organization_website', 'pro_request_message'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $attributes = [
        'role' => 'member',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'suspended_at' => 'datetime',
            'pro_requested_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    protected static function booted(): void
    {
        // Compte supprimé : ses annonces sont supprimées une par une (les demandeurs sont prévenus)
        // et les auteurs des annonces qu'il avait réservées sont informés de l'annulation.
        static::deleting(function (User $user) {
            $user->rides()->get()->each->delete();
            $user->housings()->get()->each->delete();

            $user->rideBookings()->active()->with(['ride.driver', 'ride.festival'])->get()
                ->concat($user->housingBookings()->active()->with(['housing.host', 'housing.festival'])->get())
                ->each(fn ($booking) => $booking->owner()->notify(new BookingCancelled($booking)));
        });
    }

    /* ---------- Relations ---------- */

    /** Trajets proposés en tant que conducteur. */
    public function rides(): HasMany
    {
        return $this->hasMany(Ride::class);
    }

    /** Logements proposés en tant qu'hôte. */
    public function housings(): HasMany
    {
        return $this->hasMany(Housing::class);
    }

    /** Places réservées en tant que passager. */
    public function rideBookings(): HasMany
    {
        return $this->hasMany(RideBooking::class);
    }

    /** Demandes de séjour en tant que voyageur. */
    public function housingBookings(): HasMany
    {
        return $this->hasMany(HousingBooking::class);
    }

    /* ---------- Demandes reçues ---------- */

    /** Demandes en attente sur mes trajets. */
    public function pendingRideRequests(): \Illuminate\Database\Eloquent\Builder
    {
        return RideBooking::pending()->whereHas('ride', fn (Builder $q) => $q->where('user_id', $this->id));
    }

    /** Demandes en attente sur mes logements. */
    public function pendingHousingRequests(): \Illuminate\Database\Eloquent\Builder
    {
        return HousingBooking::pending()->whereHas('housing', fn (Builder $q) => $q->where('user_id', $this->id));
    }

    /** Nombre de demandes à traiter (badge de l'en-tête). */
    public function pendingRequestsCount(): int
    {
        return $this->pendingRideRequests()->count() + $this->pendingHousingRequests()->count();
    }

    /* ---------- Statut ---------- */

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /** Organisateur validé (les admins ont aussi accès à l'espace organisateur). */
    public function isPro(): bool
    {
        return $this->role === UserRole::Pro;
    }

    public function canOrganize(): bool
    {
        return $this->isPro() || $this->isAdmin();
    }

    public function hasPendingProRequest(): bool
    {
        return $this->pro_requested_at !== null && ! $this->canOrganize();
    }

    /** Festivals organisés. */
    public function festivals(): HasMany
    {
        return $this->hasMany(Festival::class);
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    public function scopeActiveAdmins(Builder $query): void
    {
        $query->where('role', UserRole::Admin)->whereNull('suspended_at');
    }

    /** "Léa Martin" -> "LM" */
    protected function initials(): Attribute
    {
        return Attribute::get(function () {
            $parts = preg_split('/\s+/', trim($this->name)) ?: [];

            return Str::upper(Str::substr($parts[0] ?? '', 0, 1) . Str::substr($parts[1] ?? '', 0, 1));
        });
    }

    /** "Léa Martin" -> "Léa" */
    protected function firstName(): Attribute
    {
        return Attribute::get(fn () => Str::before(trim($this->name), ' '));
    }
}
