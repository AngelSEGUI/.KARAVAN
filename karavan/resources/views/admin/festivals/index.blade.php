<x-layouts.admin title="Festivals">
    <header class="admin-head">
        <div><span class="eyebrow">Catalogue</span><h1>Festivals <span class="head-count">{{ $festivals->total() }}</span></h1></div>
        <a href="{{ route('admin.festivals.create') }}" class="btn btn-primary"><x-icon name="plus" />Nouveau festival</a>
    </header>

    <form class="toolbar" method="GET" action="{{ route('admin.festivals.index') }}" data-autosubmit>
        <label class="field field-search">
            <span class="sr-only">Rechercher</span>
            <x-icon name="search" />
            <input type="search" name="q" value="{{ $q }}" placeholder="Nom, ville, artiste…">
        </label>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Festival</th><th>Dates</th><th>Offres</th><th>À la une</th><th class="col-actions"><span class="sr-only">Actions</span></th></tr>
            </thead>
            <tbody>
            @foreach ($festivals as $festival)
                <tr>
                    <td data-label="Festival">
                        <div class="cell-main">
                            <span class="swatch" style="--h: {{ $festival->hue }}"></span>
                            <div>
                                <a href="{{ route('admin.festivals.edit', $festival->id) }}" class="cell-title">{{ $festival->name }}</a>
                                <small class="muted">{{ $festival->city }} · {{ $festival->region }}</small>
                            </div>
                        </div>
                    </td>
                    <td data-label="Dates">{{ $festival->dateRange() }}</td>
                    <td data-label="Offres">
                        <span class="offer-counts">
                            <span title="Covoiturages"><x-icon name="car" class="icon-sm" />{{ $festival->rides_count }}</span>
                            <span title="Logements"><x-icon name="home" class="icon-sm" />{{ $festival->housings_count }}</span>
                        </span>
                    </td>
                    <td data-label="À la une">
                        <form method="POST" action="{{ route('admin.festivals.featured', $festival->id) }}" class="inline-form">
                            @csrf
                            @method('PATCH')
                            <button type="submit" @class(['star-toggle', 'is-on' => $festival->is_featured])
                                    aria-pressed="{{ $festival->is_featured ? 'true' : 'false' }}"
                                    title="{{ $festival->is_featured ? 'Retirer de la une' : 'Mettre à la une' }}">
                                <x-icon name="star" />
                            </button>
                        </form>
                    </td>
                    <td class="col-actions">
                        <a href="{{ route('festivals.show', $festival) }}" class="icon-btn" title="Voir sur le site"><x-icon name="external" /></a>
                        <a href="{{ route('admin.festivals.edit', $festival->id) }}" class="icon-btn" title="Modifier"><x-icon name="edit" /></a>
                        <x-delete-form :action="route('admin.festivals.destroy', $festival->id)"
                            :confirm="'Supprimer « ' . $festival->name . ' » ? Ses ' . ($festival->rides_count + $festival->housings_count) . ' offres et leurs réservations seront aussi supprimées.'" />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if ($festivals->isEmpty())<p class="table-empty">Aucun festival trouvé.</p>@endif
    </div>
    {{ $festivals->links() }}
</x-layouts.admin>
