<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** Photo du carrousel d'un festival (fichier dans public/uploads/). */
#[Fillable(['path', 'caption', 'position'])]
class FestivalPhoto extends Model
{
    protected static function booted(): void
    {
        // Le fichier est supprimé avec la photo
        static::deleted(fn (FestivalPhoto $photo) => Storage::disk('uploads')->delete($photo->path));
    }

    public function festival(): BelongsTo
    {
        return $this->belongsTo(Festival::class);
    }

    public function url(): string
    {
        return asset('uploads/' . $this->path);
    }
}
