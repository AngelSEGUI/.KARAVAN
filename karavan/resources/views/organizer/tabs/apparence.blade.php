{{--
    Onglet « Apparence » : palette, polices, teinte de secours et paragraphes de présentation.
    L'aperçu à droite se met à jour en direct (public/js/organizer.js).
    Palettes et polices disponibles : config/festival_design.php
--}}
@php
    $config = config('festival_design');
    $design = $festival->design();
    $palette = old('palette', $design->palette);
    $sections = old('sections', $festival->sections->map(fn ($s) => ['title' => $s->title, 'body' => $s->body])->all());
    $fontFamilies = collect(array_keys($config['fonts']['heading']))->merge(array_keys($config['fonts']['body']))->unique();
@endphp

@push('styles')
    {{-- Toutes les polices proposées, pour que l'aperçu soit fidèle --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?{{ $fontFamilies->map(fn ($f) => 'family=' . str_replace(' ', '+', $f) . ':wght@400;700')->implode('&') }}&display=swap">
@endpush

<form method="POST" action="{{ route('organizer.festivals.design', $festival->id) }}" class="org-design" data-design-form novalidate>
    @csrf @method('PUT')

    <div class="org-design-main">
        <section class="card org-panel">
            <h2 class="h4"><x-icon name="palette" />Palette de couleurs</h2>
            <p class="muted small">Elle habille toute la page : boutons, titres, calendrier. Les textes restent lisibles automatiquement.</p>
            <div class="palette-grid" role="radiogroup" aria-label="Palette">
                @foreach ($config['palettes'] as $key => $p)
                    <label class="palette-option">
                        <input type="radio" name="palette" value="{{ $key }}" @checked($palette === $key)
                               data-primary="{{ $p['primary'] }}" data-secondary="{{ $p['secondary'] }}" data-mode="{{ $p['mode'] }}">
                        <span class="palette-swatch" style="--sw-bg: {{ $config['modes'][$p['mode']]['bg'] }}; --sw-1: {{ $p['primary'] }}; --sw-2: {{ $p['secondary'] }}"><i></i><i></i></span>
                        <span class="palette-name">{{ $p['label'] }}<small>{{ $p['mode'] === 'dark' ? 'Fond sombre' : 'Fond clair' }}</small></span>
                    </label>
                @endforeach
                <label class="palette-option">
                    <input type="radio" name="palette" value="custom" @checked($palette === 'custom')>
                    <span class="palette-swatch palette-swatch-custom"><x-icon name="edit" /></span>
                    <span class="palette-name">Sur mesure<small>Vos couleurs</small></span>
                </label>
            </div>

            <div class="custom-colors" data-custom-colors @if ($palette !== 'custom') hidden @endif>
                <label class="field-label color-field">
                    <span>Couleur principale</span>
                    <input type="color" name="primary" value="{{ old('primary', $design->primary) }}">
                </label>
                <label class="field-label color-field">
                    <span>Couleur d'accent</span>
                    <input type="color" name="secondary" value="{{ old('secondary', $design->secondary) }}">
                </label>
                <x-field name="mode" label="Fond" type="select" :options="['light' => 'Clair', 'dark' => 'Sombre']" :value="$design->mode" />
            </div>
            @foreach (['palette', 'primary', 'secondary', 'mode'] as $f)
                @error($f)<p class="error">{{ $message }}</p>@enderror
            @endforeach
        </section>

        <section class="card org-panel">
            <h2 class="h4"><x-icon name="type" />Polices d'écriture</h2>
            <div class="form-row">
                <x-field name="heading_font" label="Titres" type="select" data-font-target="heading"
                         :options="array_combine(array_keys($config['fonts']['heading']), array_keys($config['fonts']['heading']))" :value="$design->headingFont" />
                <x-field name="body_font" label="Texte" type="select" data-font-target="body"
                         :options="array_combine(array_keys($config['fonts']['body']), array_keys($config['fonts']['body']))" :value="$design->bodyFont" />
            </div>
            <label class="field-label">
                <span>Teinte de secours <span class="muted">(visuel généré quand il n'y a pas de bannière)</span></span>
                <input type="range" name="hue" min="0" max="359" value="{{ old('hue', $festival->hue) }}" data-hue-input class="hue-range">
            </label>
        </section>

        <section class="card org-panel">
            <h2 class="h4"><x-icon name="list" />Paragraphes de présentation</h2>
            <p class="muted small">Histoire du festival, accès, infos pratiques, engagements… Ils s'affichent dans cet ordre sur la page. Jusqu'à {{ $config['max_sections'] }} blocs.</p>
            @error('sections')<p class="error">{{ $message }}</p>@enderror

            <div class="repeater" data-repeater data-max="{{ $config['max_sections'] }}">
                <ol class="repeater-list sections-list" data-repeater-list>
                    @foreach ($sections as $i => $section)
                        @include('organizer.partials.section-row', ['i' => $i, 'section' => $section])
                    @endforeach
                </ol>
                <template data-repeater-template>
                    @include('organizer.partials.section-row', ['i' => '__INDEX__', 'section' => []])
                </template>
                <button type="button" class="btn btn-ghost btn-sm" data-repeater-add><x-icon name="plus" />Ajouter un paragraphe</button>
            </div>
        </section>

        <div class="form-actions org-sticky-actions">
            <button type="submit" class="btn btn-primary">Enregistrer l'apparence</button>
            <a href="{{ route('festivals.show', $festival) }}" target="_blank" class="btn btn-ghost"><x-icon name="external" />Voir la page</a>
        </div>
    </div>

    {{-- Aperçu en direct --}}
    <aside class="org-preview-wrap">
        <div class="org-preview" data-design-preview data-modes='@json($config['modes'])' style="{{ $design->cssVariables() }}">
            <div class="org-preview-hero">
                @if ($festival->bannerUrl())
                    <img src="{{ $festival->bannerUrl() }}" alt="">
                @else
                    <x-cover :name="$festival->name" :hue="$festival->hue" />
                @endif
            </div>
            <div class="org-preview-body">
                <span class="org-preview-eyebrow">{{ $festival->city }} · {{ $festival->dateRange() }}</span>
                <strong class="org-preview-title">{{ $festival->name }}</strong>
                <p>{{ $festival->tagline ?: 'Votre accroche apparaîtra ici, dans la police du texte.' }}</p>
                <div class="org-preview-actions">
                    <span class="org-preview-btn">Billetterie</span>
                    <span class="org-preview-chip">Grande scène</span>
                </div>
                <div class="org-preview-slot"><b>21h30</b> Tête d'affiche</div>
            </div>
        </div>
        <p class="muted small">Aperçu indicatif — la page complète s'ouvre avec « Voir la page ».</p>
    </aside>
</form>
