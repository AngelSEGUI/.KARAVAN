<?php

namespace App\Models;

use App\Models\Concerns\IsBooking;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Demande de séjour d'un voyageur dans un logement (statut : voir App\Enums\BookingStatus). */
#[Fillable(['user_id', 'guests', 'nights', 'message'])]
class HousingBooking extends Model
{
    use HasFactory, IsBooking;

    protected function casts(): array
    {
        return ['guests' => 'integer', 'nights' => 'integer'];
    }

    public function housing(): BelongsTo
    {
        return $this->belongsTo(Housing::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* ---------- Interface commune des réservations (utilisée par les notifications) ---------- */

    public function owner(): User
    {
        return $this->housing->host;
    }

    public function requester(): User
    {
        return $this->guest;
    }

    public function listingTitle(): string
    {
        return $this->housing->title;
    }

    public function listingUrl(): string
    {
        return route('housings.show', $this->housing_id);
    }

    /** "2 pers. · 3 nuits · Hellfest (17–20 juin 2027)" */
    public function summary(): string
    {
        return "{$this->guests} pers. · " . plural($this->nights, 'nuit')
            . " · {$this->housing->festival->name} ({$this->housing->festival->dateRange()})";
    }

    public function total(): float
    {
        return (float) $this->housing->price_per_night * $this->nights;
    }
}
