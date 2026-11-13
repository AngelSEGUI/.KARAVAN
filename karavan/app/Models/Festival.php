<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

#[Fillable([
    'slug', 'name', 'city', 'region', 'venue', 'starts_on', 'ends_on', 'genres', 'lineup',
    'tagline', 'description', 'attendance', 'price_from', 'hue', 'is_featured',
])]
class Festival extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'genres' => 'array',
            'lineup' => 'array',
            'attendance' => 'integer',
            'price_from' => 'decimal:2',
            'hue' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    /** Les URL utilisent le slug : /festivals/hellfest */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        // Slug généré depuis le nom s'il n'est pas fourni
        static::saving(function (Festival $festival) {
            if (blank($festival->slug)) {
                $festival->slug = Str::slug($festival->name);
            }
        });
    }

    /* ---------- Relations ---------- */

    public function rides(): HasMany
    {
        return $this->hasMany(Ride::class);
    }

    public function housings(): HasMany
    {
        return $this->hasMany(Housing::class);
    }

    /* ---------- Requêtes réutilisables ---------- */

    /** Ajoute rides_count et housings_count. */
    public function scopeWithOfferCounts(Builder $query): void
    {
        $query->withCount(['rides', 'housings']);
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * Filtres de la page découverte.
     *
     * @param  array{q?: ?string, genre?: ?string, region?: ?string, month?: ?string, sort?: ?string}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['q'] ?? null, function (Builder $q, string $term) {
                $like = '%' . $term . '%';
                $q->where(fn (Builder $w) => $w->where('name', 'like', $like)
                    ->orWhere('city', 'like', $like)
                    ->orWhere('lineup', 'like', $like));
            })
            // genres est stocké en JSON : on cherche la valeur entre guillemets (SQLite et MySQL)
            ->when($filters['genre'] ?? null, fn (Builder $q, string $g) => $q->where('genres', 'like', '%"' . $g . '"%'))
            ->when($filters['region'] ?? null, fn (Builder $q, string $r) => $q->where('region', $r))
            ->when($filters['month'] ?? null, fn (Builder $q, string $m) => $q->whereMonth('starts_on', (int) $m));

        match ($filters['sort'] ?? 'date') {
            'name' => $query->orderBy('name'),
            'popular' => $query->orderByDesc('attendance'),
            'offers' => $query->orderByRaw('(rides_count + housings_count) desc'),
            default => $query->orderBy('starts_on'),
        };
    }

    /* ---------- Listes pour les filtres ---------- */

    /** @return Collection<int, string> */
    public static function allGenres(): Collection
    {
        return static::query()->pluck('genres')->flatten()->unique()->sort()->values();
    }

    /** @return Collection<int, string> */
    public static function allRegions(): Collection
    {
        return static::query()->distinct()->orderBy('region')->pluck('region');
    }

    /** Liste légère pour les menus déroulants. */
    public static function options(): Collection
    {
        return static::query()->orderBy('starts_on')->get(['id', 'name', 'city', 'starts_on']);
    }

    /* ---------- Affichage ---------- */

    /** "17–20 juin 2027" ou "30 juin – 3 juil. 2027" */
    public function dateRange(): string
    {
        $start = $this->starts_on;
        $end = $this->ends_on;

        if ($start->isSameMonth($end)) {
            return $start->day . '–' . $end->translatedFormat('j F Y');
        }

        return $start->translatedFormat('j M') . ' – ' . $end->translatedFormat('j M Y');
    }

    public function durationInDays(): int
    {
        return (int) $this->starts_on->diffInDays($this->ends_on) + 1;
    }
}
