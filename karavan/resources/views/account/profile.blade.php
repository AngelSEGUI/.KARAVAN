{{-- Modifier mon profil : informations, mot de passe, suppression du compte --}}
<x-layouts.app title="Modifier mon profil">
    <div class="container">
        <a href="{{ route('account') }}" class="back-link"><x-icon name="back" />Mon espace</a>
        <div class="form-head">
            <span class="eyebrow">Mon profil</span>
            <h1>Modifier mon profil</h1>
            <p class="muted">Votre nom, votre ville et votre présentation sont visibles par les membres avec qui vous voyagez.</p>
        </div>

        <div class="profile-layout">
            <section class="card">
                <h2 class="h4"><x-icon name="user" class="icon-sm" />Informations</h2>
                <form method="POST" action="{{ route('profile.update') }}" class="form" novalidate>
                    @csrf
                    @method('PUT')
                    <x-field name="name" label="Prénom et nom" :value="$user->name" required maxlength="80" autocomplete="name" />
                    <x-field name="email" label="Email" type="email" :value="$user->email" required autocomplete="email" />
                    <x-field name="city" label="Ville" :value="$user->city" optional maxlength="80" />
                    <x-field name="bio" label="Présentation" type="textarea" rows="4" maxlength="500" optional :value="$user->bio"
                             hint="Vos goûts musicaux, votre façon de voyager… 500 caractères maximum."
                             placeholder="Fan de rock, conducteur prudent, playlist garantie !" />
                    <div class="form-actions"><button type="submit" class="btn btn-primary">Enregistrer</button></div>
                </form>
            </section>

            <div class="stack">
                <section class="card">
                    <h2 class="h4"><x-icon name="lock" class="icon-sm" />Mot de passe</h2>
                    <form method="POST" action="{{ route('profile.password') }}" class="form" novalidate>
                        @csrf
                        @method('PUT')
                        <x-field name="current_password" label="Mot de passe actuel" type="password" bag="password" required autocomplete="current-password" />
                        <x-field name="password" label="Nouveau mot de passe" type="password" bag="password" required minlength="8" autocomplete="new-password" hint="8 caractères minimum" />
                        <x-field name="password_confirmation" label="Confirmation" type="password" bag="password" required autocomplete="new-password" />
                        <div class="form-actions"><button type="submit" class="btn btn-primary">Changer le mot de passe</button></div>
                    </form>
                </section>

                <section class="card danger-zone">
                    <div style="flex: 1 1 100%">
                        <h2 class="h4">Supprimer mon compte</h2>
                        <p class="muted small">Vos trajets, logements et réservations seront supprimés définitivement.</p>
                    </div>
                    <form method="POST" action="{{ route('profile.destroy') }}" class="form" style="flex: 1 1 100%"
                          data-confirm="Supprimer définitivement votre compte et toutes vos annonces ?">
                        @csrf
                        @method('DELETE')
                        <x-field name="password" label="Confirmez avec votre mot de passe" type="password" bag="deletion" required autocomplete="current-password" />
                        <div class="form-actions"><button type="submit" class="btn btn-danger"><x-icon name="trash" />Supprimer mon compte</button></div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</x-layouts.app>
