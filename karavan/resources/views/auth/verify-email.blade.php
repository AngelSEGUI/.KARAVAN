{{-- Page affichée tant que l'adresse email n'est pas confirmée --}}
<x-layouts.app title="Confirmez votre adresse email">
    <div class="auth">
        <div class="auth-card card">
            <span class="logo-mark logo-mark-lg" aria-hidden="true"><svg><use href="#i-mail"/></svg></span>
            <h1>Vérifiez votre boîte mail</h1>
            <p class="muted">
                Nous avons envoyé un lien de confirmation à <strong>{{ auth()->user()->email }}</strong>.
                Cliquez dessus pour pouvoir publier des annonces et réserver.
            </p>
            <p class="muted small">Rien reçu ? Regardez dans les courriers indésirables, ou renvoyez l'email.</p>

            <form method="POST" action="{{ route('verification.send') }}" class="form">
                @csrf
                <button type="submit" class="btn btn-primary btn-block">Renvoyer l'email de vérification</button>
            </form>

            @if ($devLink)
                <p class="notice notice-warning small" style="text-align: left">
                    <strong>Mode développement :</strong> les emails ne sont pas envoyés mais écrits dans
                    <code>storage/logs/laravel.log</code>. Raccourci :
                    <a href="{{ $devLink }}">confirmer l'adresse maintenant</a>.
                </p>
            @endif

            <p class="auth-switch small">
                Mauvaise adresse ? <a href="{{ route('profile.edit') }}">Modifier mon email</a>
                · <a href="{{ route('festivals.index') }}">Continuer à explorer</a>
            </p>
        </div>
    </div>
</x-layouts.app>
