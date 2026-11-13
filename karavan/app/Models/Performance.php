<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Un créneau de la programmation : un artiste, une scène, un horaire, une capacité facultative. */
#[Fillable(['artist', 'stage', 'starts_at', 'ends_at', 'capacity'])]
class Performance extends Model
{
    use HasFactory;

    /** Avant cette heure, un concert compte pour la journée de la veille (ex. 01h30 → soirée du samedi). */
    public const DAY_STARTS_AT_HOUR = 6;

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'capacity' => 'integer',
        ];
    }

    public function festival(): BelongsTo
    {
        return $this->belongsTo(Festival::class);
    }

    /** Journée de festival à laquelle appartient le créneau (Y-m-d). */
    public function festivalDay(): string
    {
        return $this->starts_at->copy()->subHours(self::DAY_STARTS_AT_HOUR)->toDateString();
    }

    public function durationInMinutes(): int
    {
        return (int) $this->starts_at->diffInMinutes($this->ends_at);
    }

    /** "21h30 – 23h00" */
    public function timeRange(): string
    {
        return $this->starts_at->format('H\hi') . ' – ' . $this->ends_at->format('H\hi');
    }

    /** "1 h 30" */
    public function durationLabel(): string
    {
        $m = $this->durationInMinutes();

        return $m < 60 ? "{$m} min" : intdiv($m, 60) . ' h' . ($m % 60 ? ' ' . str_pad((string) ($m % 60), 2, '0', STR_PAD_LEFT) : '');
    }
}
