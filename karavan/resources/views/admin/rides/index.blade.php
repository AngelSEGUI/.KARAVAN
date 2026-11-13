<x-layouts.admin title="Covoiturages">
    <header class="admin-head">
        <div><span class="eyebrow">Modération</span><h1>Covoiturages <span class="head-count">{{ $rides->total() }}</span></h1></div>
    </header>

    <form class="toolbar" method="GET" action="{{ route('admin.rides.index') }}" data-autosubmit>
        <label class="field field-search">
            <span class="sr-only">Ville de départ</span>
            <x-icon name="search" />
            <input type="search" name="from" value="{{ $filters['from'] ?? '' }}" placeholder="Ville de départ">
        </label>
        <label class="field">
            <span class="sr-only">Festival</span>
            <select name="festival">
                <option value="">Tous les festivals</option>
                @foreach ($festivals as $f)
                    <option value="{{ $f->id }}" @selected((string) ($filters['festival'] ?? '') === (string) $f->id)>{{ $f->name }}</option>
                @endforeach
            </select>
        </label>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Trajet</th><th>Conducteur</th><th>Départ</th><th>Places</th><th>Prix</th><th class="col-actions"><span class="sr-only">Actions</span></th></tr>
            </thead>
            <tbody>
            @foreach ($rides as $ride)
                @php $booked = $ride->bookedSeats(); @endphp
                <tr>
                    <td data-label="Trajet">
                        <div class="cell-main">
                            <span class="swatch" style="--h: {{ $ride->festival->hue }}"></span>
                            <div>
                                <a href="{{ route('rides.show', $ride) }}" class="cell-title">{{ $ride->departure_city }} → {{ $ride->festival->name }}</a>
                                <small class="muted">{{ $ride->vehicle }}</small>
                            </div>
                        </div>
                    </td>
                    <td data-label="Conducteur">{{ $ride->driver->name }}</td>
                    <td data-label="Départ">{{ ucfirst($ride->departs_at->translatedFormat('D j M · H\hi')) }}</td>
                    <td data-label="Places">
                        <span class="meter" title="{{ $booked }} réservée(s) sur {{ $ride->seats }}"><span style="width: {{ (int) round($booked / max(1, $ride->seats) * 100) }}%"></span></span>
                        <small>{{ $booked }}/{{ $ride->seats }}</small>
                    </td>
                    <td data-label="Prix">{{ euros($ride->price) }}</td>
                    <td class="col-actions">
                        <a href="{{ route('rides.show', $ride) }}" class="icon-btn" title="Voir"><x-icon name="external" /></a>
                        <a href="{{ route('rides.edit', $ride) }}" class="icon-btn" title="Modifier"><x-icon name="edit" /></a>
                        <x-delete-form :action="route('rides.destroy', $ride)" :redirect="request()->getRequestUri()"
                            :confirm="'Supprimer le trajet ' . $ride->departure_city . ' → ' . $ride->festival->name . ' de ' . $ride->driver->name . ($booked ? ' ? ' . $booked . ' place(s) réservée(s) seront annulées.' : ' ?')" />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if ($rides->isEmpty())<p class="table-empty">Aucun trajet trouvé.</p>@endif
    </div>
    {{ $rides->links() }}
</x-layouts.admin>
