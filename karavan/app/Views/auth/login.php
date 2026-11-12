<div class="auth">
    <div class="auth-card card">
        <span class="logo-mark logo-mark-lg" aria-hidden="true"><svg><use href="#i-van"/></svg></span>
        <h1>Bon retour parmi nous</h1>
        <p class="muted">Connectez-vous pour réserver ou proposer un trajet.</p>

        <form method="post" action="<?= url('/connexion') ?>" class="form" novalidate>
            <?= csrf_field() ?>
            <label class="field-label <?= error('email') ? 'has-error' : '' ?>">Email
                <input type="email" name="email" value="<?= old('email') ?>" required autocomplete="email">
                <?php if ($err = error('email')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
            </label>
            <label class="field-label">Mot de passe
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Se connecter</button>
        </form>

        <p class="auth-switch">Pas encore membre&nbsp;? <a href="<?= url('/inscription') ?>">Rejoindre la Karavan</a></p>
        <p class="demo-hint">Compte de démo : <code>lea@karavan.test</code> / <code>karavan</code></p>
    </div>
</div>
