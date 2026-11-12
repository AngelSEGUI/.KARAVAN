<div class="auth">
    <div class="auth-card card">
        <span class="logo-mark logo-mark-lg" aria-hidden="true"><svg><use href="#i-van"/></svg></span>
        <h1>Rejoindre la communauté</h1>
        <p class="muted">Gratuit, et la première étape vers votre prochain festival.</p>

        <form method="post" action="<?= url('/inscription') ?>" class="form" novalidate>
            <?= csrf_field() ?>
            <label class="field-label <?= error('name') ? 'has-error' : '' ?>">Prénom et nom
                <input type="text" name="name" value="<?= old('name') ?>" required maxlength="80" autocomplete="name">
                <?php if ($err = error('name')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
            </label>
            <label class="field-label <?= error('email') ? 'has-error' : '' ?>">Email
                <input type="email" name="email" value="<?= old('email') ?>" required autocomplete="email">
                <?php if ($err = error('email')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
            </label>
            <label class="field-label">Ville <span class="muted">(facultatif)</span>
                <input type="text" name="city" value="<?= old('city') ?>" maxlength="80" autocomplete="address-level2">
            </label>
            <div class="form-row">
                <label class="field-label <?= error('password') ? 'has-error' : '' ?>">Mot de passe
                    <input type="password" name="password" required minlength="8" autocomplete="new-password">
                    <?php if ($err = error('password')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
                </label>
                <label class="field-label <?= error('password_confirm') ? 'has-error' : '' ?>">Confirmation
                    <input type="password" name="password_confirm" required autocomplete="new-password">
                    <?php if ($err = error('password_confirm')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
                </label>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Créer mon compte</button>
        </form>

        <p class="auth-switch">Déjà membre&nbsp;? <a href="<?= url('/connexion') ?>">Se connecter</a></p>
    </div>
</div>
