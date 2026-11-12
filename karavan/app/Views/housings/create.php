<?php
/**
 * @var array $festivals
 * @var array $types
 * @var string $selected
 */
$sel = $_SESSION['_old']['festival_id'] ?? $selected;
$selType = $_SESSION['_old']['type'] ?? 'tente';
?>
<div class="container narrow">
    <a href="<?= url('/logements') ?>" class="back-link back-dark"><svg class="icon"><use href="#i-back"/></svg>Logements</a>
    <div class="form-head">
        <span class="eyebrow">Proposer un logement</span>
        <h1>Accueillez des festivaliers</h1>
        <p class="muted">Un coin de jardin suffit souvent à rendre un festival inoubliable.</p>
    </div>

    <form method="post" action="<?= url('/logements') ?>" class="form card" novalidate>
        <?= csrf_field() ?>

        <fieldset>
            <legend>Type de logement</legend>
            <div class="radio-cards">
                <?php foreach ($types as $key => $label): ?>
                    <label class="radio-card">
                        <input type="radio" name="type" value="<?= e($key) ?>" <?= $selType === $key ? 'checked' : '' ?>>
                        <span><svg class="icon"><use href="#<?= $key === 'van' ? 'i-van' : ($key === 'tente' ? 'i-tent' : 'i-home') ?>"/></svg><?= e($label) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <?php if ($err = error('type')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
        </fieldset>

        <fieldset>
            <legend>Emplacement</legend>
            <label class="field-label <?= error('festival_id') ? 'has-error' : '' ?>">Festival à proximité
                <select name="festival_id" required>
                    <option value="">Choisir un festival…</option>
                    <?php foreach ($festivals as $f): ?>
                        <option value="<?= (int) $f['id'] ?>" <?= (string) $f['id'] === (string) $sel ? 'selected' : '' ?>>
                            <?= e($f['name']) ?> — <?= e($f['city']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($err = error('festival_id')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
            </label>
            <div class="form-row">
                <label class="field-label <?= error('city') ? 'has-error' : '' ?>">Commune
                    <input type="text" name="city" value="<?= old('city') ?>" required maxlength="80">
                    <?php if ($err = error('city')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
                </label>
                <label class="field-label <?= error('distance_km') ? 'has-error' : '' ?>">Distance du site (km)
                    <input type="number" name="distance_km" value="<?= old('distance_km') ?>" min="0" step="0.1" required>
                    <?php if ($err = error('distance_km')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
                </label>
            </div>
        </fieldset>

        <fieldset>
            <legend>Description</legend>
            <label class="field-label <?= error('title') ? 'has-error' : '' ?>">Titre de l'annonce
                <input type="text" name="title" value="<?= old('title') ?>" required maxlength="140" placeholder="Ex. Jardin ombragé à 10 min à pied">
                <?php if ($err = error('title')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
            </label>
            <div class="form-row">
                <label class="field-label <?= error('capacity') ? 'has-error' : '' ?>">Capacité (personnes)
                    <input type="number" name="capacity" value="<?= old('capacity', '2') ?>" min="1" max="20" required>
                    <?php if ($err = error('capacity')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
                </label>
                <label class="field-label <?= error('price_night') ? 'has-error' : '' ?>">Prix par nuit (€)
                    <input type="number" name="price_night" value="<?= old('price_night') ?>" min="0" step="1" required>
                    <?php if ($err = error('price_night')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
                </label>
            </div>
            <label class="field-label">Présentation <span class="muted">(facultatif)</span>
                <textarea name="description" rows="4" maxlength="2000"><?= old('description') ?></textarea>
            </label>
            <label class="field-label">Équipements <span class="muted">(séparés par des virgules)</span>
                <input type="text" name="amenities" value="<?= old('amenities') ?>" maxlength="255" placeholder="Douche, Parking, Wifi">
            </label>
        </fieldset>

        <button type="submit" class="btn btn-primary btn-lg">Publier le logement</button>
    </form>
</div>
