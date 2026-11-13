<x-layouts.app :title="$festival->name" :description="$festival->tagline">
    <section class="festival-hero" style="--h: {{ $festival->hue }}">
        <x-cover :name="$festival->name" :hue="$festival->hue" size="hero" />
        <div class="container festival-hero-inner">
            <a href="{{ route('festivals.index') }}" class="back-link back-link-overlay"><x-icon name="back" />Festivals</a>
            <div class="tags">
                @foreach ($festival->genres as $genre)
                    <a href="{{ route('festivals.index', ['genre' => $genre]) }}" class="tag tag-light">{{ $genre }}</a>
                @endforeach
            </div>
            <h1>{{ $festival->name }}</h1>
            @if ($festival->tagline)<p class="lead">{{ $festival->tagline }}</p>@endif
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
            </dl>
            <div class="facts-actions">
                <a href="{{ route('rides.create', ['festival' => $festival->id]) }}" class="btn btn-primary btn-block"><x-icon name="car" />Proposer un trajet</a>
                <a href="{{ route('housings.create', ['festival' => $festival->id]) }}" class="btn btn-ghost btn-block"><x-icon name="home" />Proposer un logement</a>
                @if (auth()->user()?->isAdmin())
                    <a href="{{ route('admin.festivals.edit', $festival->id) }}" class="btn btn-ghost btn-block btn-sm"><x-icon name="edit" />Modifier (admin)</a>
                @endif
            </div>
        </aside>

        <div class="festival-content">
            <section>
                <h2 class="h3">Le festival</h2>
                @if ($festival->description)<p>{!! nl2br(e($festival->description)) !!}</p>@endif
                @if ($festival->lineup)
                    <h3 class="h4">Programmation</h3>
                    <ul class="lineup">
                        @foreach ($festival->lineup as $artist)
                            <li>{{ $artist }}</li>
                        @endforeach
                    </ul>
                    <p class="muted small">Programmation donnée à titre indicatif.</p>
                @endif
            </section>

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
        </div>
    </div>
</x-layouts.app>
