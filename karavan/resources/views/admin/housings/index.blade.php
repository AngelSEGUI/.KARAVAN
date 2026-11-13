<x-layouts.admin title="Logements">
    <header class="admin-head">
        <div><span class="eyebrow">Modération</span><h1>Logements <span class="head-count">{{ $housings->total() }}</span></h1></div>
    </header>

    <form class="toolbar" method="GET" action="{{ route('admin.housings.index') }}" data-autosubmit>
        <label class="field">
            <span class="sr-only">Festival</span>
            <select name="festival">
                <option value="">Tous les festivals</option>
                @foreach ($festivals as $f)
                    <option value="{{ $f->id }}" @selected((string) ($filters['festival'] ?? '') === (string) $f->id)>{{ $f->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="field">
            <span class="sr-only">Type</span>
            <select name="type">
                <option value="">Tous les types</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Logement</th><th>Hôte</th><th>Festival</th><th>Capacité</th><th>Prix / nuit</th><th>Demandes</th><th class="col-actions"><span class="sr-only">Actions</span></th></tr>
            </thead>
            <tbody>
            @foreach ($housings as $housing)
                <tr>
                    <td data-label="Logement">
                        <div class="cell-main">
                            <span class="swatch" style="--h: {{ $housing->festival->hue }}"></span>
                            <div>
                                <a href="{{ route('housings.show', $housing) }}" class="cell-title">{{ $housing->title }}</a>
                                <small class="muted">{{ $housing->type->label() }} · {{ $housing->city }}, {{ km($housing->distance_km) }}</small>
                            </div>
                        </div>
                    </td>
                    <td data-label="Hôte">{{ $housing->host->name }}</td>
                    <td data-label="Festival">{{ $housing->festival->name }}</td>
                    <td data-label="Capacité">{{ $housing->capacity }} pers.</td>
                    <td data-label="Prix / nuit">{{ euros($housing->price_per_night) }}</td>
                    <td data-label="Demandes"><span @class(['pill', 'pill-ok' => $housing->bookings_count])>{{ $housing->bookings_count }}</span></td>
                    <td class="col-actions">
                        <a href="{{ route('housings.show', $housing) }}" class="icon-btn" title="Voir"><x-icon name="external" /></a>
                        <a href="{{ route('housings.edit', $housing) }}" class="icon-btn" title="Modifier"><x-icon name="edit" /></a>
                        <x-delete-form :action="route('housings.destroy', $housing)" :redirect="request()->getRequestUri()"
                            :confirm="'Supprimer « ' . $housing->title . ' » de ' . $housing->host->name . ($housing->bookings_count ? ' ? ' . $housing->bookings_count . ' demande(s) seront annulées.' : ' ?')" />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if ($housings->isEmpty())<p class="table-empty">Aucun logement trouvé.</p>@endif
    </div>
    {{ $housings->links() }}
</x-layouts.admin>
