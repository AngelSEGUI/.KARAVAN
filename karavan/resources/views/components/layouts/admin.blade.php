{{-- Layout de l'administration : barre latérale + contenu. Charge en plus css/admin.css. --}}
@props(['title' => null])
@php
    $theme = active_theme();
    $nav = [
        'admin.dashboard' => ['Tableau de bord', 'grid', 'admin.dashboard'],
        'admin.festivals.index' => ['Festivals', 'ticket', 'admin.festivals.*'],
        'admin.rides.index' => ['Covoiturages', 'car', 'admin.rides.*'],
        'admin.housings.index' => ['Logements', 'home', 'admin.housings.*'],
        'admin.users.index' => ['Membres', 'users', 'admin.users.*'],
    ];
@endphp
<!doctype html>
<html lang="fr" data-theme="{{ $theme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title ?? 'Administration' }} — Admin Karavan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/themes/' . $theme . '.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%2317132b'/><circle cx='16' cy='17' r='7' fill='%23ff5a36'/></svg>">
</head>
<body class="admin-body">
@include('partials.icons')

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <a href="{{ route('admin.dashboard') }}" class="logo">
                <span class="logo-mark" aria-hidden="true"><svg><use href="#i-van"/></svg></span>karavan
            </a>
            <span class="admin-badge">Admin</span>
        </div>

        <nav class="admin-nav" aria-label="Administration">
            @foreach ($nav as $route => [$label, $icon, $pattern])
                <a href="{{ route($route) }}" @class(['is-active' => request()->routeIs($pattern)])><x-icon :name="$icon" />{{ $label }}</a>
            @endforeach
        </nav>

        <div class="admin-me">
            <a href="{{ route('home') }}" class="admin-site-link"><x-icon name="external" />Voir le site</a>
            <div class="admin-user">
                <x-avatar :user="auth()->user()" size="sm" />
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Déconnexion" aria-label="Déconnexion"><x-icon name="logout" /></button>
                </form>
            </div>
        </div>
    </aside>

    <main class="admin-main" id="main">
        <x-flash />
        {{ $slot }}
    </main>
</div>

<script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
