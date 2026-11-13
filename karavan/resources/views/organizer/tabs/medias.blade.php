{{--
    Onglet « Images » : bannière, icône, affiche (programmation) et photos du carrousel.
    Les boutons « Retirer » visent des formulaires placés hors du formulaire principal (attribut form=…),
    car un formulaire HTML ne peut pas en contenir un autre.
--}}
@php
    $limit = \App\Support\UploadLimit::label();
    $maxPhotos = config('festival_design.max_photos');
    $remaining = max(0, $maxPhotos - $festival->photos->count());
    $singles = [
        'banner' => ['Bannière', $festival->bannerUrl(), 'Grande image en haut de la page. Format paysage, 1920 × 800 px conseillé.', 'wide'],
        'icon' => ['Icône', $festival->iconUrl(), 'Logo carré du festival, au moins 256 × 256 px.', 'square'],
        'poster' => ['Affiche', $festival->posterUrl(), "L'affiche avec la programmation, en format portrait. Les festivaliers peuvent l'ouvrir en grand.", 'portrait'],
    ];
@endphp

<form method="POST" action="{{ route('organizer.festivals.media', $festival->id) }}" enctype="multipart/form-data" class="org-media" novalidate>
    @csrf
    <p class="muted small">Formats JPG, PNG ou WebP — {{ $limit }} maximum par image.</p>

    <div class="media-singles">
        @foreach ($singles as $field => [$label, $url, $hint, $ratio])
            <section @class(['card', 'org-panel', 'media-card', 'has-error' => $errors->has($field)])>
                <h2 class="h4">{{ $label }}</h2>
                <div class="media-drop media-{{ $ratio }}" data-image-preview>
                    @if ($url)
                        <img src="{{ $url }}" alt="{{ $label }} actuelle">
                    @else
                        <span class="media-placeholder"><x-icon name="image" />Aucune image</span>
                    @endif
                </div>
                <p class="muted small">{{ $hint }}</p>
                <div class="form-actions">
                    <label class="btn btn-ghost btn-sm file-btn">
                        <x-icon name="image" />{{ $url ? 'Remplacer' : 'Choisir une image' }}
                        <input type="file" name="{{ $field }}" accept="image/jpeg,image/png,image/webp" data-image-input>
                    </label>
                    @if ($url)
                        <button type="submit" form="remove-{{ $field }}" class="btn btn-ghost btn-sm btn-danger-text"><x-icon name="trash" />Retirer</button>
                    @endif
                </div>
                @error($field)<p class="error">{{ $message }}</p>@enderror
            </section>
        @endforeach
    </div>

    <section class="card org-panel">
        <div class="org-panel-head">
            <div>
                <h2 class="h4">Photos du carrousel</h2>
                <p class="muted small">Ambiance, scènes, public, site… {{ $festival->photos->count() }} / {{ $maxPhotos }} photos. Ajoutez une légende si vous le souhaitez.</p>
            </div>
            @if ($remaining > 0)
                <label class="btn btn-ghost btn-sm file-btn">
                    <x-icon name="plus" />Ajouter des photos
                    <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple data-image-input data-multiple-preview="#new-photos">
                </label>
            @endif
        </div>
        @error('photos')<p class="error">{{ $message }}</p>@enderror
        @foreach ($errors->get('photos.*') as $messages)<p class="error">{{ $messages[0] }}</p>@endforeach

        <ul class="photo-grid">
            @foreach ($festival->photos as $photo)
                <li class="photo-item">
                    <img src="{{ $photo->url() }}" alt="{{ $photo->caption }}" loading="lazy">
                    <input type="text" name="captions[{{ $photo->id }}]" value="{{ old('captions.' . $photo->id, $photo->caption) }}" maxlength="160" placeholder="Légende (facultatif)" aria-label="Légende de la photo {{ $loop->iteration }}">
                    <button type="submit" form="remove-photo-{{ $photo->id }}" class="icon-btn icon-btn-danger photo-remove" title="Retirer la photo" aria-label="Retirer la photo {{ $loop->iteration }}"><x-icon name="trash" /></button>
                </li>
            @endforeach
        </ul>
        <ul class="photo-grid photo-grid-new" id="new-photos" aria-live="polite"></ul>
        @if ($festival->photos->isEmpty())
            <p class="muted small" data-photos-empty>Pas encore de photo : le carrousel n'apparaît pas sur la page tant qu'il est vide.</p>
        @endif
    </section>

    <div class="form-actions org-sticky-actions">
        <button type="submit" class="btn btn-primary">Enregistrer les images</button>
        <a href="{{ route('festivals.show', $festival) }}" target="_blank" class="btn btn-ghost"><x-icon name="external" />Voir la page</a>
    </div>
</form>

{{-- Formulaires de suppression, ciblés par les boutons ci-dessus --}}
@foreach ($singles as $field => [$label, $url])
    @if ($url)
        <form id="remove-{{ $field }}" method="POST" action="{{ route('organizer.festivals.media.destroy', [$festival->id, $field]) }}" data-confirm="Retirer {{ mb_strtolower($label) }} ?" hidden>@csrf @method('DELETE')</form>
    @endif
@endforeach
@foreach ($festival->photos as $photo)
    <form id="remove-photo-{{ $photo->id }}" method="POST" action="{{ route('organizer.festivals.photos.destroy', [$festival->id, $photo->id]) }}" data-confirm="Retirer cette photo du carrousel ?" hidden>@csrf @method('DELETE')</form>
@endforeach
