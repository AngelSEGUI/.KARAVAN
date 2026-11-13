{{--
    Guide de styles : tous les composants de l'interface sur une seule page, avec de vraies données.
    Sert à vérifier un thème d'un coup d'œil. Accessible hors production (KARAVAN_THEME_SWITCHER=true)
    ou pour les administrateurs.
--}}
@php
    $tokens = [
        'Fonds et textes' => [
            '--color-bg' => 'Fond de page', '--color-bg-alt' => 'Fond secondaire', '--color-surface' => 'Cartes, panneaux',
            '--color-input' => 'Champs', '--color-text' => 'Texte', '--color-text-soft' => 'Texte secondaire',
            '--color-muted' => 'Texte discret', '--color-line' => 'Bordures',
        ],
        'Marque' => [
            '--color-primary' => 'Couleur principale', '--color-primary-hover' => 'Principale au survol',
            '--color-primary-soft' => 'Principale très claire', '--color-accent' => 'Accent (dégradés)',
            '--color-highlight' => 'Mise en avant', '--color-inverse' => 'Blocs inversés',
        ],
        'États' => [
            '--color-success' => 'Succès', '--color-success-bg' => 'Fond succès', '--color-danger' => 'Danger',
            '--color-danger-bg' => 'Fond danger', '--color-warning' => 'Alerte', '--color-warning-bg' => 'Fond alerte',
        ],
    ];
@endphp
<x-layouts.app title="Guide de styles">
    <x-page-head eyebrow="Design" title="Guide de styles"
                 lead="Tous les composants de Karavan sur une page. Changez de thème pour comparer les ambiances : les fonctionnalités restent identiques." />

    <div class="container">
        {{-- Thèmes --}}
        <section class="sg-section">
            <h2><x-icon name="palette" class="icon-sm" />Thèmes</h2>
            @if ($canSwitch)
                <div class="sg-themes">
                    @foreach ($themes as $key => $label)
                        <a href="{{ route('styleguide', ['theme' => $key]) }}" @class(['sg-theme', 'is-current' => $current === $key])>
                            <span><strong>{{ \Illuminate\Support\Str::before($label, ' —') }}</strong><small class="muted block">{{ \Illuminate\Support\Str::after($label, '— ') }}</small></span>
                            @if ($current === $key)<x-icon name="check" />@endif
                        </a>
                    @endforeach
                </div>
                <p class="muted small" style="margin-top: 12px">
                    Le thème choisi ici s'applique à tout le site, pour vous seulement (mémorisé le temps de la session).
                    Thème par défaut pour tout le monde : <code>KARAVAN_THEME={{ $default }}</code> dans le fichier <code>.env</code>.
                    Fichier modifiable : <code>public/css/themes/{{ $current }}.css</code> — guide : <code>docs/THEMES.md</code>.
                </p>
            @else
                <p class="muted">Thème actif : <code>{{ $current }}</code>. Le changement de thème depuis le navigateur est désactivé (production ou <code>KARAVAN_THEME_SWITCHER=false</code>).</p>
            @endif
        </section>

        {{-- Couleurs --}}
        <section class="sg-section">
            <h2>Couleurs <span class="muted small">— variables du thème</span></h2>
            @foreach ($tokens as $group => $list)
                <h3 class="h4" style="margin-top: 20px">{{ $group }}</h3>
                <div class="sg-swatches">
                    @foreach ($list as $var => $desc)
                        <div class="sg-swatch">
                            <div class="sg-swatch-color" style="background: var({{ $var }})"></div>
                            <code>{{ $var }}</code>
                            <small>{{ $desc }}</small>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </section>

        {{-- Typographie --}}
        <section class="sg-section sg-type">
            <h2>Typographie <span class="muted small">— base.css · --font-display / --font-body</span></h2>
            <h1>Titre de page (h1)</h1>
            <h2>Titre de section (h2)</h2>
            <h3>Titre de carte (h3)</h3>
            <span class="eyebrow">Sur-titre (.eyebrow)</span>
            <p class="lead">Texte d'introduction (.lead) : découvrez les festivals qui vous ressemblent et partagez la route.</p>
            <p>Texte courant avec un <a href="#">lien</a>, du <strong>gras</strong> et du <span class="muted">texte discret (.muted)</span>.</p>
        </section>

        {{-- Boutons et étiquettes --}}
        <section class="sg-section">
            <h2>Boutons <span class="muted small">— components.css §1</span></h2>
            <div class="sg-row">
                <button class="btn btn-primary">Principal</button>
                <button class="btn btn-ghost">Secondaire</button>
                <button class="btn btn-danger"><x-icon name="trash" />Danger</button>
                <button class="btn btn-primary btn-sm">Petit</button>
                <button class="btn btn-primary btn-lg">Grand</button>
                <button class="icon-btn" title="Bouton icône"><x-icon name="edit" /></button>
                <a href="#" class="link-arrow">Lien fléché <x-icon name="arrow" /></a>
            </div>
            <h3 class="h4" style="margin-top: 24px">Étiquettes <span class="muted small">— §2</span></h3>
            <div class="sg-row">
                <span class="tag">Rock</span>
                <a href="#" class="chip">Électro</a>
                <span class="badge">À la une</span>
                <span class="pill">Membre</span>
                <span class="pill pill-ok">Validé</span>
                <span class="pill pill-warn">Suspendu</span>
                <span class="pill pill-admin">Admin</span>
                @auth<x-avatar :user="auth()->user()" size="sm" /><x-avatar :user="auth()->user()" /><x-avatar :user="auth()->user()" size="lg" />@endauth
            </div>
        </section>

        {{-- Cartes --}}
        <section class="sg-section">
            <h2>Cartes <span class="muted small">— &lt;x-festival-card&gt; · &lt;x-ride-card&gt; · &lt;x-housing-card&gt;</span></h2>
            <div class="sg-grid">
                @if ($festival)<x-festival-card :festival="$festival" />@endif
                @if ($housing)<x-housing-card :housing="$housing" />@endif
                <div class="stack">
                    @if ($ride)<x-ride-card :ride="$ride" />@endif
                    <div class="card">
                        <h3 class="h4">Carte simple (.card)</h3>
                        <p class="muted" style="margin: 0">Conteneur neutre pour regrouper du contenu.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Formulaires --}}
        <section class="sg-section">
            <h2>Formulaires <span class="muted small">— &lt;x-field&gt; · components.css §8</span></h2>
            <div class="profile-layout">
                <div class="form card">
                    <x-field name="sg_text" label="Champ texte" placeholder="Ex. Paris" />
                    <x-field name="sg_select" label="Liste déroulante" type="select" :options="$types" placeholder="Choisir…" />
                    <x-field name="sg_textarea" label="Zone de texte" type="textarea" rows="2" optional hint="Texte d'aide sous le champ." />
                    <label class="field-label has-error">
                        <span>Champ en erreur</span>
                        <input type="text" value="Valeur invalide">
                        <span class="error">Ce champ est obligatoire.</span>
                    </label>
                </div>
                <div class="form card">
                    <div class="radio-cards" style="grid-template-columns: repeat(3, 1fr)">
                        <label class="radio-card"><input type="radio" name="sg_r" checked><span><x-icon name="tent" />Tente</span></label>
                        <label class="radio-card"><input type="radio" name="sg_r"><span><x-icon name="van" />Van</span></label>
                        <label class="radio-card"><input type="radio" name="sg_r"><span><x-icon name="home" />Maison</span></label>
                    </div>
                    <label class="switch">
                        <input type="checkbox" checked><span class="switch-track"></span>
                        <span><strong>Interrupteur</strong><small class="muted block">.switch</small></span>
                    </label>
                    <label class="checkbox"><input type="checkbox" checked> Case à cocher</label>
                    <label class="field field-search"><x-icon name="search" /><input type="search" placeholder="Champ de recherche"></label>
                </div>
            </div>
        </section>

        {{-- Messages --}}
        <section class="sg-section">
            <h2>Messages <span class="muted small">— &lt;x-flash&gt; · .notice · &lt;x-empty&gt;</span></h2>
            <div class="flashes">
                <div class="flash flash-success"><span>Message de succès : votre trajet est en ligne !</span></div>
                <div class="flash flash-error"><span>Message d'erreur : ce trajet est complet.</span></div>
                <div class="flash flash-info"><span>Message d'information : vous êtes déconnecté.</span></div>
            </div>
            <p class="notice">Encadré neutre (.notice)</p>
            <p class="notice notice-success"><x-icon name="check" class="icon-sm" />Encadré de succès</p>
            <p class="notice notice-warning">Encadré d'alerte : des places sont déjà réservées.</p>
            <x-empty small style="margin-top: 12px">
                <p>État vide (petit) : aucun trajet pour l'instant.</p>
                <a href="#" class="btn btn-primary btn-sm">Action</a>
            </x-empty>
        </section>

        {{-- Navigation --}}
        <section class="sg-section">
            <h2>Navigation <span class="muted small">— onglets · pagination</span></h2>
            <div class="tab-list" role="tablist">
                <button type="button" role="tab" aria-selected="true">Onglet actif <span class="count">3</span></button>
                <button type="button" role="tab" aria-selected="false">Onglet <span class="count">1</span></button>
            </div>
            <nav class="pagination">
                <span class="is-disabled">‹ Précédent</span><span class="is-current">1</span><a href="#">2</a><a href="#">3</a><a href="#">Suivant ›</a>
            </nav>
        </section>
    </div>
</x-layouts.app>
