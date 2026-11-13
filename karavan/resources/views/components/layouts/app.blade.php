{{--
    Layout du site public. Utilisation dans une page :
        <x-layouts.app title="Titre de l'onglet"> ... contenu ... </x-layouts.app>

    Feuilles de style, dans l'ordre :
      1. themes/{thème}.css  variables : couleurs, polices, arrondis (à modifier pour changer le look)
      2. base.css            remise à zéro, typographie
      3. layout.css          en-tête, menus, pied de page, grilles
      4. components.css      boutons, cartes, formulaires…
      5. pages.css           mises en page propres à certaines pages
--}}
@props(['title' => null, 'description' => null])
@php
    $theme = active_theme();
    $user = auth()->user();
    $tab = fn (string $pattern) => request()->routeIs($pattern) ? 'is-active' : '';
@endphp
<!doctype html>
<html lang="fr" data-theme="{{ $theme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title . ' — Karavan' : 'Karavan — La route des festivals, ensemble' }}</title>
    <meta name="description" content="{{ $description ?? 'Karavan : découvrez les festivals, trouvez un covoiturage et un logement grâce à la communauté.' }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/themes/' . $theme . '.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}">
    @stack('styles')
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%23ff5a36'/><circle cx='16' cy='17' r='7' fill='%23ffd23f'/></svg>">
</head>
<body>
@include('partials.icons')

<header class="site-header">
    <div class="container header-inner">
        <a href="{{ route('home') }}" class="logo" aria-label="Karavan, accueil">
            <span class="logo-mark" aria-hidden="true"><svg><use href="#i-van"/></svg></span>karavan
        </a>

        <nav class="main-nav" aria-label="Navigation principale">
            <a href="{{ route('festivals.index') }}" class="{{ $tab('festivals.*') }}">Festivals</a>
            <a href="{{ route('rides.index') }}" class="{{ $tab('rides.*') }}">Covoiturage</a>
            <a href="{{ route('housings.index') }}" class="{{ $tab('housings.*') }}">Logements</a>
        </nav>

        <div class="header-actions">
            @auth
                <details class="dropdown">
                    <summary class="btn btn-ghost btn-sm"><x-icon name="plus" /><span class="hide-sm">Proposer</span></summary>
                    <div class="dropdown-menu dropdown-right">
                        <a href="{{ route('rides.create') }}"><x-icon name="car" />Un trajet</a>
                        <a href="{{ route('housings.create') }}"><x-icon name="home" />Un logement</a>
                    </div>
                </details>
                <details class="dropdown">
                    <summary class="avatar">{{ $user->initials }}</summary>
                    <div class="dropdown-menu dropdown-right">
                        <span class="dropdown-label">{{ $user->name }}</span>
                        <a href="{{ route('account') }}"><x-icon name="user" />Mon espace</a>
                        <a href="{{ route('profile.edit') }}"><x-icon name="edit" />Modifier mon profil</a>
                        @if ($user->isAdmin())
                            <a href="{{ route('admin.dashboard') }}"><x-icon name="shield" />Administration</a>
                        @endif
                        <hr>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"><x-icon name="logout" />Déconnexion</button>
                        </form>
                    </div>
                </details>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Connexion</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm hide-sm">Rejoindre</a>
            @endauth
        </div>
    </div>
</header>

<x-flash class="container" />

<main id="main">
    {{ $slot }}
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <div>
            <a href="{{ route('home') }}" class="logo">
                <span class="logo-mark" aria-hidden="true"><svg><use href="#i-van"/></svg></span>karavan
            </a>
            <p>La route des festivals se fait mieux à plusieurs. Partagez la voiture, la tente et les souvenirs.</p>
        </div>
        <div>
            <h4>Explorer</h4>
            <a href="{{ route('festivals.index') }}">Tous les festivals</a>
            <a href="{{ route('rides.index') }}">Covoiturages</a>
            <a href="{{ route('housings.index') }}">Logements</a>
        </div>
        <div>
            <h4>Participer</h4>
            <a href="{{ route('rides.create') }}">Proposer un trajet</a>
            <a href="{{ route('housings.create') }}">Proposer un logement</a>
            @guest<a href="{{ route('register') }}">Rejoindre la communauté</a>@endguest
            @if (app(\App\Support\Theme::class)->switcherEnabled())
                <a href="{{ route('styleguide') }}">Guide de styles</a>
            @endif
        </div>
    </div>
    <div class="container footer-bottom">© {{ date('Y') }} Karavan · Projet communautaire</div>
</footer>

<nav class="tabbar" aria-label="Navigation mobile">
    <a href="{{ route('home') }}" class="{{ $tab('home') }}"><x-icon name="compass" />Découvrir</a>
    <a href="{{ route('festivals.index') }}" class="{{ $tab('festivals.*') }}"><x-icon name="ticket" />Festivals</a>
    <a href="{{ route('rides.index') }}" class="{{ $tab('rides.*') }}"><x-icon name="car" />Trajets</a>
    <a href="{{ route('housings.index') }}" class="{{ $tab('housings.*') }}"><x-icon name="home" />Logements</a>
    <a href="{{ $user ? route('account') : route('login') }}" class="{{ $tab('account') ?: $tab('profile.*') ?: $tab('login') }}"><x-icon name="user" />Profil</a>
</nav>

<script src="{{ asset('js/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>
