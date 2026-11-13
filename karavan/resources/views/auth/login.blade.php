<x-layouts.app title="Connexion">
    <div class="auth">
        <div class="auth-card card">
            <span class="logo-mark logo-mark-lg" aria-hidden="true"><svg><use href="#i-van"/></svg></span>
            <h1>Bon retour parmi nous</h1>
            <p class="muted">Connectez-vous pour réserver ou proposer un trajet.</p>

            <form method="POST" action="{{ route('login') }}" class="form" novalidate>
                @csrf
                <x-field name="email" label="Email" type="email" required autocomplete="email" autofocus />
                <x-field name="password" label="Mot de passe" type="password" required autocomplete="current-password" />
                <label class="checkbox"><input type="checkbox" name="remember" value="1"> Rester connecté</label>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Se connecter</button>
            </form>

            <p class="auth-switch">Pas encore membre&nbsp;? <a href="{{ route('register') }}">Rejoindre la Karavan</a></p>
            @if (app()->isLocal())
                <p class="demo-hint">Comptes de démo : <code>lea@karavan.test</code> ou <code>admin@karavan.test</code> / <code>karavan</code></p>
            @endif
        </div>
    </div>
</x-layouts.app>
