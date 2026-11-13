<x-layouts.app>
    <section class="hero">
        <div class="container hero-inner">
            <div class="hero-copy">
                <span class="kicker"><x-icon name="music" class="icon-sm" />Saison des festivals</span>
                <h1>La route des festivals se fait <em>ensemble</em>.</h1>
                <p class="lead">Découvrez les festivals qui vous ressemblent, partagez la route avec d'autres festivaliers et dormez chez un membre de la communauté, à deux pas des scènes.</p>

                <form class="hero-search" action="{{ route('festivals.index') }}" method="GET" role="search">
                    <x-icon name="search" />
                    <input type="search" name="q" placeholder="Un festival, une ville, un artiste…" aria-label="Rechercher un festival">
                    <button class="btn btn-primary" type="submit">Explorer</button>
                </form>

                <div class="hero-shortcuts">
                    <a href="{{ route('rides.index') }}" class="shortcut">
                        <x-icon name="car" />
                        <span><strong>Trouver un trajet</strong><small>{{ plural($stats['rides'], 'covoiturage') }} en ligne</small></span>
                    </a>
                    <a href="{{ route('housings.index') }}" class="shortcut">
                        <x-icon name="home" />
                        <span><strong>Trouver un logement</strong><small>{{ plural($stats['housings'], 'offre') }} de la communauté</small></span>
                    </a>
                </div>
            </div>

            @if ($top = $featured->first())
                <a href="{{ route('festivals.show', $top) }}" class="hero-poster">
                    <x-cover :name="$top->name" :hue="$top->hue" :image="$top->bannerUrl()" size="hero" />
                    <div class="hero-poster-info">
                        <span class="badge">À la une</span>
                        <strong>{{ $top->name }}</strong>
                        <span>{{ $top->city }} · {{ $top->dateRange() }}</span>
                    </div>
                </a>
            @endif
        </div>
    </section>

    @if ($featured->isNotEmpty())
        <section class="section">
            <div class="container">
                <div class="section-head">
                    <div><span class="eyebrow">Mis en avant</span><h2>À l'affiche cette saison</h2></div>
                    <a href="{{ route('festivals.index') }}" class="link-arrow">Tous les festivals <x-icon name="arrow" /></a>
                </div>
                <div class="rail">
                    @foreach ($featured as $festival)
                        <x-festival-card :festival="$festival" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section section-tight">
        <div class="container">
            <h2 class="h3">Explorer par style</h2>
            <div class="chips">
                @foreach ($genres as $genre)
                    <a href="{{ route('festivals.index', ['genre' => $genre]) }}" class="chip">{{ $genre }}</a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section section-alt">
        <div class="container">
            <div class="section-head">
                <div><span class="eyebrow">Covoiturage</span><h2>Prochains départs</h2></div>
                <a href="{{ route('rides.index') }}" class="link-arrow">Tous les trajets <x-icon name="arrow" /></a>
            </div>
            <div class="grid grid-2">
                @foreach ($rides as $ride)
                    <x-ride-card :ride="$ride" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-head">
                <div><span class="eyebrow">Logements</span><h2>Dormir près des scènes</h2></div>
                <a href="{{ route('housings.index') }}" class="link-arrow">Tous les logements <x-icon name="arrow" /></a>
            </div>
            <div class="grid grid-3">
                @foreach ($housings as $housing)
                    <x-housing-card :housing="$housing" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="section section-dark">
        <div class="container">
            <h2 class="center">Comment ça marche ?</h2>
            <ol class="steps">
                <li><span class="step-num">1</span><h3>Choisissez votre festival</h3><p>Parcourez la page découverte, filtrez par style, région ou mois.</p></li>
                <li><span class="step-num">2</span><h3>Réservez votre place</h3><p>Rejoignez un covoiturage depuis votre ville et partagez les frais d'essence.</p></li>
                <li><span class="step-num">3</span><h3>Posez votre sac</h3><p>Un jardin pour la tente, une chambre ou un van : la communauté vous héberge.</p></li>
            </ol>
            <div class="stats">
                <div><strong>{{ $stats['festivals'] }}</strong><span>festivals</span></div>
                <div><strong>{{ $stats['rides'] }}</strong><span>trajets</span></div>
                <div><strong>{{ $stats['housings'] }}</strong><span>logements</span></div>
                <div><strong>{{ $stats['members'] }}</strong><span>membres</span></div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="cta-band">
                <div>
                    <h2>Une place libre dans la voiture&nbsp;? Un coin de jardin&nbsp;?</h2>
                    <p>Proposez-les à la communauté et partagez les frais.</p>
                </div>
                <div class="cta-actions">
                    <a href="{{ route('rides.create') }}" class="btn btn-light"><x-icon name="car" />Proposer un trajet</a>
                    <a href="{{ route('housings.create') }}" class="btn btn-outline-light"><x-icon name="home" />Proposer un logement</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
