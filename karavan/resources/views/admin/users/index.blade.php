<x-layouts.admin title="Membres">
    <header class="admin-head">
        <div><span class="eyebrow">Communauté</span><h1>Membres <span class="head-count">{{ $users->total() }}</span></h1></div>
    </header>

    <form class="toolbar" method="GET" action="{{ route('admin.users.index') }}" data-autosubmit>
        <input type="hidden" name="filtre" value="{{ $filter }}">
        <label class="field field-search">
            <span class="sr-only">Rechercher</span>
            <x-icon name="search" />
            <input type="search" name="q" value="{{ $q }}" placeholder="Nom, email, ville…">
        </label>
        <div class="segmented" role="group" aria-label="Filtrer">
            @foreach (['' => 'Tous', 'organisateurs' => 'Organisateurs', 'admin' => 'Admins', 'suspendus' => 'Suspendus', 'demandes-pro' => 'Demandes pro'] as $key => $label)
                <a href="{{ route('admin.users.index', array_filter(['q' => $q, 'filtre' => $key])) }}" @class(['is-active' => $filter === $key])>{{ $label }}@if ($key === 'demandes-pro' && $proRequests) <span class="count">{{ $proRequests }}</span>@endif</a>
            @endforeach
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Membre</th><th>Inscription</th><th>Activité</th><th>Statut</th><th class="col-actions"><span class="sr-only">Actions</span></th></tr>
            </thead>
            <tbody>
            @foreach ($users as $member)
                @php $isMe = $member->is(auth()->user()); @endphp
                <tr @class(['is-muted' => $member->isSuspended()])>
                    <td data-label="Membre">
                        <div class="cell-main">
                            <x-avatar :user="$member" size="sm" />
                            <div>
                                <span class="cell-title">{{ $member->name }}@if ($isMe) <span class="muted">(vous)</span>@endif</span>
                                <small class="muted">{{ $member->email }}@if ($member->city) · {{ $member->city }}@endif</small>
                                @if ($member->organization)<small><x-icon name="ticket" class="icon-sm" />{{ $member->organization }}@if ($member->festivals_count) · {{ plural($member->festivals_count, 'festival') }}@endif</small>@endif
                            </div>
                        </div>
                    </td>
                    <td data-label="Inscription">{{ $member->created_at->translatedFormat('j M Y') }}</td>
                    <td data-label="Activité">
                        <span class="offer-counts">
                            <span title="Trajets proposés"><x-icon name="car" class="icon-sm" />{{ $member->rides_count }}</span>
                            <span title="Logements proposés"><x-icon name="home" class="icon-sm" />{{ $member->housings_count }}</span>
                            <span title="Réservations"><x-icon name="ticket" class="icon-sm" />{{ $member->ride_bookings_count + $member->housing_bookings_count }}</span>
                        </span>
                    </td>
                    <td data-label="Statut">
                        <span class="{{ $member->role->pillClass() }}">{{ $member->role->label() }}</span>
                        @if ($member->isSuspended())<span class="pill pill-warn">Suspendu</span>@endif
                        @if ($member->hasPendingProRequest())<span class="pill pill-warn">Demande pro</span>@endif
                    </td>
                    <td class="col-actions">
                        @unless ($isMe)
                            @if ($member->hasPendingProRequest())
                                <div class="pro-request">
                                    <small class="muted">« {{ \Illuminate\Support\Str::limit($member->pro_request_message ?: 'Pas de message', 90) }} »
                                        @if ($member->organization_website)· <a href="{{ $member->organization_website }}" target="_blank" rel="noopener">site</a>@endif</small>
                                    <form method="POST" action="{{ route('admin.users.pro-request', [$member, 'valider']) }}" class="inline-form">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" />Valider</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.pro-request', [$member, 'refuser']) }}" class="inline-form"
                                          data-confirm="Refuser la demande organisateur de {{ $member->name }} ?">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-ghost btn-sm">Refuser</button>
                                    </form>
                                </div>
                            @endif
                            <form method="POST" action="{{ route('admin.users.role', $member) }}" class="inline-form role-form">
                                @csrf
                                @method('PATCH')
                                <label class="sr-only" for="role-{{ $member->id }}">Rôle</label>
                                <select name="role" id="role-{{ $member->id }}" onchange="this.form.requestSubmit()">
                                    @foreach (\App\Enums\UserRole::cases() as $role)
                                        <option value="{{ $role->value }}" @selected($member->role === $role)>{{ $role->label() }}</option>
                                    @endforeach
                                </select>
                                <noscript><button type="submit" class="btn btn-ghost btn-sm">OK</button></noscript>
                            </form>
                            <form method="POST" action="{{ route('admin.users.suspension', $member) }}" class="inline-form"
                                  @unless ($member->isSuspended()) data-confirm="Suspendre {{ $member->name }} ? Il ne pourra plus se connecter." @endunless>
                                @csrf
                                @method('PATCH')
                                <button type="submit" @class(['icon-btn', 'is-warn' => $member->isSuspended()]) title="{{ $member->isSuspended() ? 'Réactiver' : 'Suspendre' }}"><x-icon name="ban" /></button>
                            </form>
                            <x-delete-form :action="route('admin.users.destroy', $member)"
                                :confirm="'Supprimer définitivement le compte de ' . $member->name . ' ? Ses trajets, logements et réservations seront supprimés.'" />
                        @endunless
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if ($users->isEmpty())<p class="table-empty">Aucun membre trouvé.</p>@endif
    </div>
    {{ $users->links() }}
</x-layouts.admin>
