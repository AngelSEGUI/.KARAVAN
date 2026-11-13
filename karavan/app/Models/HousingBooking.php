<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Demande de séjour d'un voyageur dans un logement. */
#[Fillable(['user_id', 'guests', 'nights', 'message'])]
class HousingBooking extends Model
{
    use HasFactory;

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

    public function total(): float
    {
        return (float) $this->housing->price_per_night * $this->nights;
    }
}
