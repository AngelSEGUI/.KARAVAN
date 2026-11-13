<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use App\Models\FestivalPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Onglet « Images » : bannière, icône, affiche et photos du carrousel.
 * Les fichiers sont rangés dans public/uploads/festivals/{id}/.
 */
class MediaController extends Controller
{
    /** Champ du formulaire => colonne en base */
    private const SINGLE = [
        'banner' => 'banner_path',
        'icon' => 'icon_path',
        'poster' => 'poster_path',
    ];

    public function store(Request $request, Festival $festival): RedirectResponse
    {
        $this->authorize('update', $festival);
        $maxKb = \App\Support\UploadLimit::kilobytes();
        $maxPhotos = config('festival_design.max_photos');
        $image = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . $maxKb];

        $request->validate([
            'banner' => $image,
            'icon' => [...$image, 'dimensions:min_width=64,min_height=64'],
            'poster' => $image,
            'photos' => ['array', 'max:' . max(0, $maxPhotos - $festival->photos()->count())],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:' . $maxKb],
            'captions' => ['array'],
            'captions.*' => ['nullable', 'string', 'max:160'],
        ], [
            'photos.max' => "Le carrousel est limité à {$maxPhotos} photos.",
            '*.uploaded' => "Le fichier n'a pas pu être envoyé : il dépasse probablement la taille autorisée par le serveur.",
            'photos.*.uploaded' => "Une photo n'a pas pu être envoyée : elle dépasse probablement la taille autorisée par le serveur.",
        ], [
            'banner' => 'la bannière', 'icon' => "l'icône", 'poster' => "l'affiche", 'photos.*' => 'chaque photo',
        ]);

        $disk = Storage::disk('uploads');
        $dir = $festival->mediaDirectory();
        $changes = 0;

        foreach (self::SINGLE as $field => $column) {
            if ($request->hasFile($field)) {
                $old = $festival->{$column};
                $festival->{$column} = $request->file($field)->store($dir, 'uploads');
                if ($old) {
                    $disk->delete($old);
                }
                $changes++;
            }
        }
        $festival->save();

        $position = (int) $festival->photos()->max('position');
        foreach ($request->file('photos', []) as $file) {
            $festival->photos()->create(['path' => $file->store("{$dir}/carrousel", 'uploads'), 'position' => ++$position]);
            $changes++;
        }

        foreach ($request->input('captions', []) as $id => $caption) {
            $festival->photos()->whereKey($id)->update(['caption' => filled($caption) ? trim($caption) : null]);
        }

        return back()->with('success', $changes ? 'Images enregistrées.' : 'Légendes enregistrées.');
    }

    /** Retirer la bannière, l'icône ou l'affiche. */
    public function destroy(Festival $festival, string $type): RedirectResponse
    {
        $this->authorize('update', $festival);
        $column = self::SINGLE[$type] ?? abort(404);

        if ($festival->{$column}) {
            Storage::disk('uploads')->delete($festival->{$column});
            $festival->forceFill([$column => null])->save();
        }

        return back()->with('success', 'Image retirée.');
    }

    public function destroyPhoto(Festival $festival, FestivalPhoto $photo): RedirectResponse
    {
        $this->authorize('update', $festival);
        abort_unless($photo->festival_id === $festival->id, 404);
        $photo->delete(); // le fichier est supprimé par le modèle

        return back()->with('success', 'Photo retirée du carrousel.');
    }
}
