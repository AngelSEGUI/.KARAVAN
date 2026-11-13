<x-layouts.app title="Rejoindre la communauté">
    <div class="auth">
        <div class="auth-card card">
            <span class="logo-mark logo-mark-lg" aria-hidden="true"><svg><use href="#i-van"/></svg></span>
            <h1>Rejoindre la communauté</h1>
            <p class="muted">Gratuit, et la première étape vers votre prochain festival.</p>

            <form method="POST" action="{{ route('register') }}" class="form" novalidate>
                @csrf
                <x-field name="name" label="Prénom et nom" required maxlength="80" autocomplete="name" />
                <x-field name="email" label="Email" type="email" required autocomplete="email" />
                <x-field name="city" label="Ville" optional maxlength="80" autocomplete="address-level2" />
                <div class="form-row">
                    <x-field name="password" label="Mot de passe" type="password" required minlength="8" autocomplete="new-password" hint="8 caractères minimum" />
                    <x-field name="password_confirmation" label="Confirmation" type="password" required autocomplete="new-password" />
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Créer mon compte</button>
            </form>

            <p class="auth-switch">Déjà membre&nbsp;? <a href="{{ route('login') }}">Se connecter</a></p>
        </div>
    </div>
</x-layouts.app>
