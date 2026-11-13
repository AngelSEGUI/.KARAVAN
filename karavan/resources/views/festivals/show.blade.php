{{--
    Page d'un festival. Son apparence (couleurs, polices) est choisie par l'organisateur :
    FestivalDesign::cssVariables() surcharge les variables du thème sur .festival-page.
    Styles : public/css/festival-page.css — Comportements : public/js/festival.js
--}}
<x-layouts.app :title="$festival->name" :description="$festival->tagline">
    @push('styles')
        <link rel="stylesheet" href="{{ $design->fontsUrl() }}">
        <link rel="stylesheet" href="{{ asset('css/festival-page.css') }}">
    @endpush
    @push('scripts')<script src="{{ asset('js/festival.js') }}" defer></script>@endpush

    <div @class(['festival-page', 'is-dark' => $design->mode === 'dark']) style="{{ $design->cssVariables() }}">

        @unless ($festival->is_published)
            <div class="draft-banner">
                <div class="container">
                    <x-icon name="lock" /><span><strong>Brouillon</strong> — cette page n'est visible que par vous{{ auth()->user()?->isAdmin() && ! $festival->isOrganizedBy(auth()->user()) ? ' et les administrateurs' : '' }}.</span>
                    @can('update', $festival)<a href="{{ route('organizer.festivals.edit', $festival->id) }}">Continuer l'édition</a>@endcan
                </div>
            </div>
        @endunless

        <section @class(['festival-hero', 'has-banner' => $festival->bannerUrl()]) style="--h: {{ $festival->hue }}">
            <x-cover :name="$festival->name" :hue="$festival->hue" :image="$festival->bannerUrl()" size="hero" />
            <div class="container festival-hero-inner">
                <a href="{{ route('festivals.index') }}" class="back-link back-link-overlay"><x-icon name="back" />Festivals</a>
                <div class="festival-title">
                    @if ($festival->iconUrl())
                        <img src="{{ $festival->iconUrl() }}" alt="" class="festival-icon" width="88" height="88">
                    @endif
                    <div>
                        <div class="tags">
                            @foreach ($festival->genres as $genre)
                                <a href="{{ route('festivals.index', ['genre' => $genre]) }}" class="tag tag-light">{{ $genre }}</a>
                            @endforeach
                        </div>
                        <h1>{{ $festival->name }}</h1>
                        @if ($festival->tagline)<p class="lead">{{ $festival->tagline }}</p>@endif
                    </div>
                </div>
            </div>
        </section>

        <div class="container festival-layout">
            <aside class="festival-facts">
                <dl>
                    <div>
                        <dt><x-icon name="calendar" />Dates</dt>
                        <dd>{{ $festival->dateRange() }}<small>{{ plural($festival->durationInDays(), 'jour') }}</small></dd>
                    </div>
                    <div>
                        <dt><x-icon name="pin" />Lieu</dt>
                        <dd>{{ $festival->venue ?: $festival->city }}<small>{{ $festival->city }}, {{ $festival->region }}</small></dd>
                    </div>
                    @if ($festival->attendance)
                        <div>
                            <dt><x-icon name="users" />Affluence</dt>
                            <dd>{{ number_format($festival->attendance, 0, ',', ' ') }}<small>festivaliers attendus</small></dd>
                        </div>
                    @endif
                    @if ($festival->price_from !== null)
                        <div>
                            <dt><x-icon name="ticket" />Billets</dt>
                            <dd>dès {{ euros($festival->price_from) }}<small>pass journée</small></dd>
                        </div>
                    @endif
                    @if ($festival->organizer?->isPro())
                        <div>
                            <dt><x-icon name="star" />Organisé par</dt>
                            <dd>{{ $festival->organizer->organization ?: $festival->organizer->name }}</dd>
                        </div>
                    @endif
                </dl>
                <div class="facts-actions">
                    @if ($festival->ticket_url)
                        <a href="{{ $festival->ticket_url }}" class="btn btn-primary btn-block" target="_blank" rel="noopener"><x-icon name="ticket" />Billetterie</a>
                    @endif
                    <a href="{{ route('rides.create', ['festival' => $festival->id]) }}" @class(['btn', 'btn-block', 'btn-primary' => ! $festival->ticket_url, 'btn-ghost' => $festival->ticket_url])><x-icon name="car" />Proposer un trajet</a>
                    <a href="{{ route('housings.create', ['festival' => $festival->id]) }}" class="btn btn-ghost btn-block"><x-icon name="home" />Proposer un logement</a>
                    @if ($festival->website)
                        <a href="{{ $festival->website }}" class="btn btn-ghost btn-block btn-sm" target="_blank" rel="noopener"><x-icon name="globe" />Site officiel</a>
                    @endif
                    @can('update', $festival)
                        <a href="{{ route('organizer.festivals.edit', $festival->id) }}" class="btn btn-ghost btn-block btn-sm"><x-icon name="edit" />Modifier la page</a>
                    @endcan
                </div>
            </aside>

            <div class="festival-content">
                {{-- Présentation et paragraphes de l'organisateur --}}
                <section class="festival-about">
                    <h2 class="h3">Le festival</h2>
                    @if ($festival->description)<p class="festival-intro">{!! nl2br(e($festival->description)) !!}</p>@endif
                    @foreach ($festival->sections as $section)
                        <article class="festival-section">
                            @if ($section->title)<h3 class="h4">{{ $section->title }}</h3>@endif
                            <p>{!! nl2br(e($section->body)) !!}</p>
                        </article>
                    @endforeach
                    @if ($festival->lineup)
                        <h3 class="h4">Têtes d'affiche</h3>
                        <ul class="lineup">
                            @foreach ($festival->lineup as $artist)<li>{{ $artist }}</li>@endforeach
                        </ul>
                    @endif
                </section>

                {{-- Carrousel de photos --}}
                @if ($festival->photos->isNotEmpty())
                    <section class="festival-gallery" aria-label="Photos du festival">
                        <div class="section-head">
                            <h2 class="h3">En images</h2>
                            @if ($festival->photos->count() > 1)
                                <div class="carousel-nav">
                                    <button type="button" class="icon-btn" data-carousel-prev aria-label="Photo précédente"><x-icon name="chevron-left" /></button>
                                    <button type="button" class="icon-btn" data-carousel-next aria-label="Photo suivante"><x-icon name="chevron-right" /></button>
                                </div>
                            @endif
                        </div>
                        <ul class="carousel" data-carousel tabindex="0">
                            @foreach ($festival->photos as $photo)
                                <li>
                                    <figure>
                                        <img src="{{ $photo->url() }}" alt="{{ $photo->caption ?: 'Photo ' . $loop->iteration . ' de ' . $festival->name }}" loading="lazy">
                                        @if ($photo->caption)<figcaption>{{ $photo->caption }}</figcaption>@endif
                                    </figure>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                {{-- Programmation : calendrier par journée, une colonne par scène --}}
                @if (! $timetable->isEmpty() || $festival->posterUrl())
                    <section class="festival-programme" id="programmation">
                        <div class="section-head">
                            <div>
                                <h2 class="h3">Programmation</h2>
                                @unless ($timetable->isEmpty())
                                    <p class="muted small tt-help"><span class="tt-help-hover">Survolez un créneau</span><span class="tt-help-touch">Touchez un créneau</span> pour voir le détail.</p>
                                @endunless
                            </div>
                            @unless ($timetable->isEmpty())
                                <div class="segmented tt-view" role="group" aria-label="Affichage">
                                    <button type="button" class="is-active" data-tt-view="grid" aria-pressed="true"><x-icon name="calendar" />Calendrier</button>
                                    <button type="button" data-tt-view="list" aria-pressed="false"><x-icon name="list" />Liste</button>
                                </div>
                            @endunless
                        </div>

                        @unless ($timetable->isEmpty())
                            <div class="timetable" data-timetable>
                                <div class="tt-days" role="tablist" aria-label="Journées">
                                    @foreach ($timetable->days as $day)
                                        <button type="button" role="tab" id="tt-tab-{{ $day['key'] }}" aria-controls="tt-day-{{ $day['key'] }}"
                                                aria-selected="{{ $loop->first ? 'true' : 'false' }}" @unless ($loop->first) tabindex="-1" @endunless>
                                            <span class="tt-day-long">{{ $day['label'] }}</span><span class="tt-day-short">{{ $day['short'] }}</span>
                                        </button>
                                    @endforeach
                                </div>

                                @foreach ($timetable->days as $day)
                                    <div role="tabpanel" id="tt-day-{{ $day['key'] }}" aria-labelledby="tt-tab-{{ $day['key'] }}" class="tt-panel" @unless ($loop->first) hidden @endunless>
                                        {{-- Vue calendrier --}}
                                        <div class="tt-scroll" tabindex="0" aria-label="Calendrier du {{ $day['label'] }}">
                                            <div class="tt-grid" style="--stages: {{ count($day['stages']) }}; --minutes: {{ $day['minutes'] }}">
                                                <div class="tt-corner"></div>
                                                @foreach ($day['stages'] as $stage => $slots)
                                                    <div class="tt-stage">{{ $stage }}</div>
                                                @endforeach

                                                <div class="tt-hours" aria-hidden="true">
                                                    @foreach ($day['hours'] as $hour)
                                                        <span style="--at: {{ $hour['offset'] }}">{{ $hour['label'] }}</span>
                                                    @endforeach
                                                </div>
                                                @foreach ($day['stages'] as $stage => $slots)
                                                    <div class="tt-col">
                                                        @foreach ($slots as $slot)
                                                            @php $p = $slot['performance']; @endphp
                                                            <button type="button" @class(['tt-slot', 'is-short' => $slot['length'] < 45])
                                                                    style="--start: {{ $slot['offset'] }}; --len: {{ $slot['length'] }}"
                                                                    data-artist="{{ $p->artist }}" data-stage="{{ $p->stage }}"
                                                                    data-day="{{ $day['label'] }}" data-time="{{ $p->timeRange() }}" data-duration="{{ $p->durationLabel() }}"
                                                                    @if ($p->capacity) data-capacity="{{ number_format($p->capacity, 0, ',', ' ') }}" @endif
                                                                    aria-label="{{ $p->artist }}, {{ $p->stage }}, {{ $p->timeRange() }}{{ $p->capacity ? ', capacité ' . $p->capacity . ' personnes' : '' }}">
                                                                <strong>{{ $p->artist }}</strong>
                                                                <span>{{ $p->timeRange() }}</span>
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Vue liste (aussi utile aux lecteurs d'écran) --}}
                                        <ol class="tt-list">
                                            @foreach (collect($day['stages'])->flatten(1)->sortBy(fn ($s) => $s['offset']) as $slot)
                                                @php $p = $slot['performance']; @endphp
                                                <li>
                                                    <time>{{ $p->timeRange() }}</time>
                                                    <strong>{{ $p->artist }}</strong>
                                                    <span class="tt-list-meta">
                                                        <span><x-icon name="pin" class="icon-sm" />{{ $p->stage }}</span>
                                                        <span><x-icon name="clock" class="icon-sm" />{{ $p->durationLabel() }}</span>
                                                        @if ($p->capacity)<span><x-icon name="users" class="icon-sm" />{{ number_format($p->capacity, 0, ',', ' ') }} places</span>@endif
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ol>
                                    </div>
                                @endforeach
                            </div>
                            <div class="tt-tip" role="tooltip" id="tt-tip" hidden></div>
                        @endunless

                        @if ($festival->posterUrl())
                            <figure class="festival-poster">
                                <a href="{{ $festival->posterUrl() }}" target="_blank" rel="noopener" title="Ouvrir l'affiche en grand">
                                    <img src="{{ $festival->posterUrl() }}" alt="Affiche de la programmation de {{ $festival->name }}" loading="lazy">
                                </a>
                                <figcaption>
                                    <strong>L'affiche officielle</strong>
                                    <a href="{{ $festival->posterUrl() }}" target="_blank" rel="noopener" class="link-arrow small">Voir en grand <x-icon name="external" /></a>
                                </figcaption>
                            </figure>
                        @endif
                    </section>
                @endif

                {{-- Covoiturages et logements de la communauté --}}
                <section class="festival-offers">
                    <h2 class="h3">Y aller avec la communauté</h2>
                    <div class="tabs" data-tabs>
                        <div class="tab-list" role="tablist">
                            <button type="button" role="tab" aria-selected="true" aria-controls="tab-rides" id="t-rides">
                                <x-icon name="car" />Covoiturages <span class="count">{{ $rides->count() }}</span>
                            </button>
                            <button type="button" role="tab" aria-selected="false" aria-controls="tab-housings" id="t-housings">
                                <x-icon name="home" />Logements <span class="count">{{ $housings->count() }}</span>
                            </button>
                        </div>

                        <div role="tabpanel" id="tab-rides" aria-labelledby="t-rides">
                            @forelse ($rides as $ride)
                                @if ($loop->first)<div class="stack">@endif
                                <x-ride-card :ride="$ride" :show-festival="false" />
                                @if ($loop->last)</div>@endif
                            @empty
                                <x-empty small>
                                    <p>Aucun trajet pour l'instant. Soyez le premier à proposer le vôtre&nbsp;!</p>
                                    <a href="{{ route('rides.create', ['festival' => $festival->id]) }}" class="btn btn-primary btn-sm">Proposer un trajet</a>
                                </x-empty>
                            @endforelse
                        </div>

                        <div role="tabpanel" id="tab-housings" aria-labelledby="t-housings" hidden>
                            @forelse ($housings as $housing)
                                @if ($loop->first)<div class="grid grid-2">@endif
                                <x-housing-card :housing="$housing" :show-festival="false" />
                                @if ($loop->last)</div>@endif
                            @empty
                                <x-empty small>
                                    <p>Aucun logement proposé près de ce festival pour l'instant.</p>
                                    <a href="{{ route('housings.create', ['festival' => $festival->id]) }}" class="btn btn-primary btn-sm">Proposer un logement</a>
                                </x-empty>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-layouts.app>
