<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Place(s) réservée(s) par un passager dans un covoiturage. */
#[Fillable(['user_id', 'seats', 'message'])]
class RideBooking extends Model
{
    use HasFactory;

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

    public function total(): float
    {
        return (float) $this->ride->price * $this->seats;
    }
}
