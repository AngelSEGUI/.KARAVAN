<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
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
#[Fillable(['name', 'email', 'password', 'city', 'bio'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
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
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
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

    /* ---------- Statut ---------- */

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
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
