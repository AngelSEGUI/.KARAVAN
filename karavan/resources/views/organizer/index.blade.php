{{-- Espace organisateur : la liste de mes festivals --}}
<x-layouts.app title="Espace organisateur">
    @push('styles')<link rel="stylesheet" href="{{ asset('css/organizer.css') }}">@endpush

    <x-page-head eyebrow="Espace organisateur" title="Mes festivals"
                 :lead="auth()->user()->isAdmin() ? 'En tant qu\'admin, vous voyez tous les festivals.' : 'Créez la page de votre festival et personnalisez-la : couleurs, polices, photos, affiche et programmation.'">
        <a href="{{ route('organizer.festivals.create') }}" class="btn btn-primary"><x-icon name="plus" />Nouveau festival</a>
    </x-page-head>

    <div class="container">
        @if ($festivals->isEmpty())
            <x-empty icon="ticket" title="Aucun festival pour l'instant">
                <p>Commencez par les informations de base : vous pourrez ensuite habiller la page à votre image.</p>
                <a href="{{ route('organizer.festivals.create') }}" class="btn btn-primary">Créer mon festival</a>
            </x-empty>
        @else
            <div class="org-list">
                @foreach ($festivals as $festival)
                    <article class="org-card">
                        <div class="org-card-visual">
                            @if ($festival->bannerUrl())
                                <img src="{{ $festival->bannerUrl() }}" alt="" loading="lazy">
                            @else
                                <x-cover :name="$festival->name" :hue="$festival->hue" />
                            @endif
                            @if ($festival->iconUrl())<img src="{{ $festival->iconUrl() }}" alt="" class="org-card-icon">@endif
                        </div>
                        <div class="org-card-body">
                            <div class="org-card-head">
                                <h2 class="h4">{{ $festival->name }}</h2>
                                <span @class(['pill', 'pill-ok' => $festival->is_published, 'pill-warn' => ! $festival->is_published])>{{ $festival->is_published ? 'En ligne' : 'Brouillon' }}</span>
                            </div>
                            <p class="muted small">{{ $festival->city }} · {{ $festival->dateRange() }}</p>
                            <p class="org-card-stats small">
                                <span><x-icon name="music" class="icon-sm" />{{ plural($festival->performances_count, 'créneau', 'créneaux') }}</span>
                                <span><x-icon name="car" class="icon-sm" />{{ plural($festival->rides_count, 'trajet') }}</span>
                                <span><x-icon name="home" class="icon-sm" />{{ plural($festival->housings_count, 'logement') }}</span>
                            </p>
                            <div class="form-actions">
                                <a href="{{ route('organizer.festivals.edit', $festival->id) }}" class="btn btn-primary btn-sm"><x-icon name="edit" />Modifier</a>
                                <a href="{{ route('festivals.show', $festival) }}" class="btn btn-ghost btn-sm"><x-icon name="external" />{{ $festival->is_published ? 'Voir la page' : 'Aperçu' }}</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
