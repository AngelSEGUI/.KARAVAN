<?php

namespace App\Models;

use App\Support\FestivalDesign;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'slug', 'name', 'city', 'region', 'venue', 'starts_on', 'ends_on', 'genres', 'lineup',
    'tagline', 'description', 'attendance', 'price_from', 'hue', 'is_featured',
    'website', 'ticket_url',
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
            'is_published' => 'boolean',
            'design' => 'array',
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

        // Suppression : on supprime les annonces une par une pour prévenir les personnes concernées,
        // puis toutes les images du festival.
        static::deleting(function (Festival $festival) {
            $festival->rides()->get()->each->delete();
            $festival->housings()->get()->each->delete();
        });
        static::deleted(function (Festival $festival) {
            Storage::disk('uploads')->deleteDirectory("festivals/{$festival->id}");
        });
    }

    /* ---------- Relations ---------- */

    /** Organisateur (profil pro). Null pour un festival créé par l'équipe Karavan. */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(FestivalSection::class)->orderBy('position');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(FestivalPhoto::class)->orderBy('position');
    }

    public function performances(): HasMany
    {
        return $this->hasMany(Performance::class)->orderBy('starts_at');
    }

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

    /** Festivals visibles du public (les brouillons sont exclus). */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
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
                    ->orWhere('lineup', 'like', $like)
                    ->orWhereHas('performances', fn (Builder $p) => $p->where('artist', 'like', $like)));
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
        return static::published()->pluck('genres')->flatten()->unique()->sort()->values();
    }

    /** @return Collection<int, string> */
    public static function allRegions(): Collection
    {
        return static::published()->distinct()->orderBy('region')->pluck('region');
    }

    /** Liste légère pour les menus déroulants. */
    public static function options(): Collection
    {
        return static::published()->orderBy('starts_on')->get(['id', 'name', 'city', 'starts_on']);
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

    /** @return list<\Carbon\CarbonInterface> les journées du festival */
    public function days(): array
    {
        return collect(\Carbon\CarbonPeriod::create($this->starts_on, $this->ends_on))->all();
    }

    /* ---------- Apparence et images ---------- */

    public function design(): FestivalDesign
    {
        return new FestivalDesign($this->design);
    }

    public function bannerUrl(): ?string
    {
        return $this->banner_path ? asset('uploads/' . $this->banner_path) : null;
    }

    public function iconUrl(): ?string
    {
        return $this->icon_path ? asset('uploads/' . $this->icon_path) : null;
    }

    public function posterUrl(): ?string
    {
        return $this->poster_path ? asset('uploads/' . $this->poster_path) : null;
    }

    /** Dossier des images de ce festival dans public/uploads/ */
    public function mediaDirectory(): string
    {
        return "festivals/{$this->id}";
    }

    public function isOrganizedBy(?User $user): bool
    {
        return $user !== null && $this->user_id === $user->id;
    }

    public function durationInDays(): int
    {
        return (int) $this->starts_on->diffInDays($this->ends_on) + 1;
    }
}
