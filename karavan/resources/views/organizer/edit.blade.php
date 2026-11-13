{{--
    Éditeur d'un festival, en onglets : informations · apparence · medias · programmation.
    À la création, seul l'onglet « Informations » est disponible.
--}}
@php $editing = $festival->exists; @endphp
<x-layouts.app :title="$editing ? $festival->name . ' — édition' : 'Nouveau festival'">
    @push('styles')<link rel="stylesheet" href="{{ asset('css/organizer.css') }}">@endpush
    @push('scripts')<script src="{{ asset('js/organizer.js') }}" defer></script>@endpush

    <div class="container">
        <a href="{{ route('organizer.index') }}" class="back-link"><x-icon name="back" />Mes festivals</a>

        <header class="org-head">
            <div>
                <span class="eyebrow">Espace organisateur</span>
                <h1>{{ $editing ? $festival->name : 'Nouveau festival' }}</h1>
                @if ($editing)
                    <p class="org-status">
                        <span @class(['pill', 'pill-ok' => $festival->is_published, 'pill-warn' => ! $festival->is_published])>{{ $festival->is_published ? 'En ligne' : 'Brouillon' }}</span>
                        <span class="muted small">{{ $festival->is_published ? 'Visible de tous les festivaliers.' : 'Invisible du public tant que vous ne publiez pas.' }}</span>
                    </p>
                @endif
            </div>
            @if ($editing)
                <div class="form-actions">
                    <a href="{{ route('festivals.show', $festival) }}" class="btn btn-ghost" target="_blank"><x-icon name="external" />{{ $festival->is_published ? 'Voir la page' : 'Aperçu' }}</a>
                    <form method="POST" action="{{ route('organizer.festivals.publish', $festival->id) }}" class="inline-form"
                          @if ($festival->is_published) data-confirm="Repasser en brouillon ? La page ne sera plus visible du public." @endif>
                        @csrf @method('PATCH')
                        <button type="submit" @class(['btn', 'btn-primary' => ! $festival->is_published, 'btn-ghost' => $festival->is_published])>
                            {{ $festival->is_published ? 'Repasser en brouillon' : 'Publier le festival' }}
                        </button>
                    </form>
                </div>
            @endif
        </header>

        @if ($editing)
            <nav class="org-tabs" aria-label="Sections de l'éditeur">
                @foreach (\App\Http\Controllers\Organizer\FestivalController::TABS as $key => $label)
                    <a href="{{ route('organizer.festivals.edit', [$festival->id, $key]) }}" @class(['is-active' => $tab === $key]) @if ($tab === $key) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
        @endif

        @include('organizer.tabs.' . $tab)

        @if ($editing && $tab === 'informations')
            <section class="card danger-zone" style="margin-top: 32px">
                <div>
                    <h2 class="h4">Supprimer ce festival</h2>
                    <p class="muted small">Sa page, ses images, sa programmation, ainsi que {{ plural($festival->rides_count, 'trajet') }} et {{ plural($festival->housings_count, 'logement') }} proposés par la communauté seront supprimés. Les festivaliers concernés seront prévenus par email.</p>
                </div>
                <x-delete-form :action="route('organizer.festivals.destroy', $festival->id)" label="Supprimer" class="btn btn-danger"
                               :confirm="'Supprimer définitivement « ' . $festival->name . ' » ?'" />
            </section>
        @endif
    </div>
</x-layouts.app>
