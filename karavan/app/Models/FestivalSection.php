<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Paragraphe libre de la page d'un festival (titre facultatif + texte). */
#[Fillable(['title', 'body', 'position'])]
class FestivalSection extends Model
{
    public function festival(): BelongsTo
    {
        return $this->belongsTo(Festival::class);
    }
}
