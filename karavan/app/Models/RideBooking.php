<?php

namespace App\Models;

use App\Models\Concerns\IsBooking;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Demande de place(s) d'un passager dans un covoiturage (statut : voir App\Enums\BookingStatus). */
#[Fillable(['user_id', 'seats', 'message'])]
class RideBooking extends Model
{
    use HasFactory, IsBooking;

    protected function casts(): array
    {
        return ['seats' => 'integer'];
    }

    public function ride(): BelongsTo
    {
        return $this->belongsTo(Ride::class);
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* ---------- Interface commune des réservations (utilisée par les notifications) ---------- */

    public function owner(): User
    {
        return $this->ride->driver;
    }

    public function requester(): User
    {
        return $this->passenger;
    }

    public function listingTitle(): string
    {
        return "{$this->ride->departure_city} → {$this->ride->festival->name}";
    }

    public function listingUrl(): string
    {
        return route('rides.show', $this->ride_id);
    }

    /** "2 places · départ le samedi 17 juin à 08h00" */
    public function summary(): string
    {
        return plural($this->seats, 'place') . ' · départ le ' . $this->ride->departs_at->translatedFormat('l j F \à H\hi');
    }

    public function total(): float
    {
        return (float) $this->ride->price * $this->seats;
    }
}
