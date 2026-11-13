<x-layouts.admin title="Tableau de bord">
    <header class="admin-head">
        <div><span class="eyebrow">Administration</span><h1>Tableau de bord</h1></div>
        <a href="{{ route('admin.festivals.create') }}" class="btn btn-primary"><x-icon name="plus" />Nouveau festival</a>
    </header>

    <div class="kpis">
        <a href="{{ route('admin.festivals.index') }}" class="kpi"><x-icon name="ticket" /><strong>{{ $stats['festivals'] }}</strong><span>Festivals</span></a>
        <a href="{{ route('admin.users.index') }}" class="kpi"><x-icon name="users" /><strong>{{ $stats['members'] }}</strong><span>Membres</span></a>
        <a href="{{ route('admin.rides.index') }}" class="kpi"><x-icon name="car" /><strong>{{ $stats['rides'] }}</strong><span>Trajets</span></a>
        <a href="{{ route('admin.housings.index') }}" class="kpi"><x-icon name="home" /><strong>{{ $stats['housings'] }}</strong><span>Logements</span></a>
        <div class="kpi"><x-icon name="check" /><strong>{{ $stats['bookings'] }}</strong><span>Réservations</span></div>
    </div>

    <div class="admin-grid">
        <section class="panel">
            <h2 class="panel-title">Activité récente</h2>
            <ul class="feed">
                @foreach ($activity as $event)
                    <li>
                        <span class="feed-icon"><x-icon :name="$event['icon']" /></span>
                        <span class="feed-text">
                            <strong>{{ $event['actor'] }}</strong> {{ $event['verb'] }}
                            @if ($event['url'])
                                <a href="{{ $event['url'] }}">{{ $event['label'] }}</a>
                            @elseif ($event['label'])
                                <span class="muted">({{ $event['label'] }})</span>
                            @endif
                            <small>{{ $event['at']->diffForHumans() }}</small>
                        </span>
                    </li>
                @endforeach
            </ul>
        </section>

        <div class="admin-col">
            <section class="panel">
                <h2 class="panel-title"><x-icon name="alert" class="icon-warn" />Festivals à animer</h2>
                <p class="muted small">Sans covoiturage ou sans logement pour l'instant.</p>
                @if ($gaps->isNotEmpty())
                    <ul class="mini-list">
                        @foreach ($gaps as $festival)
                            <li>
                                <a href="{{ route('festivals.show', $festival) }}">{{ $festival->name }}</a>
                                <span class="mini-tags">
                                    <span @class(['pill', 'pill-warn' => $festival->rides_count === 0])>{{ plural($festival->rides_count, 'trajet') }}</span>
                                    <span @class(['pill', 'pill-warn' => $festival->housings_count === 0])>{{ plural($festival->housings_count, 'logement') }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>Tous les festivals ont des offres. 🎉</p>
                @endif
            </section>

            <section class="panel">
                <h2 class="panel-title">Nouveaux membres</h2>
                <ul class="mini-list">
                    @foreach ($newMembers as $member)
                        <li>
                            <span class="person-inline"><x-avatar :user="$member" size="sm" />{{ $member->name }}</span>
                            <span class="muted small">{{ $member->city }}</span>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('admin.users.index') }}" class="link-arrow small">Gérer les membres <x-icon name="arrow" /></a>
            </section>
        </div>
    </div>
</x-layouts.admin>
