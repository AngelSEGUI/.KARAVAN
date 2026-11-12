<?php
/**
 * @var array $festivals
 * @var string $selected
 */
$sel = $_SESSION['_old']['festival_id'] ?? $selected;
?>
<div class="container narrow">
    <a href="<?= url('/covoiturages') ?>" class="back-link back-dark"><svg class="icon"><use href="#i-back"/></svg>Covoiturages</a>
    <div class="form-head">
        <span class="eyebrow">Proposer un trajet</span>
        <h1>Une place libre&nbsp;? Partagez-la.</h1>
        <p class="muted">Indiquez un prix juste pour partager les frais d'essence et de péage.</p>
    </div>

    <form method="post" action="<?= url('/covoiturages') ?>" class="form card" novalidate>
        <?= csrf_field() ?>

        <fieldset>
            <legend>Destination</legend>
            <label class="field-label <?= error('festival_id') ? 'has-error' : '' ?>">Festival
                <select name="festival_id" required>
                    <option value="">Choisir un festival…</option>
                    <?php foreach ($festivals as $f): ?>
                        <option value="<?= (int) $f['id'] ?>" <?= (string) $f['id'] === (string) $sel ? 'selected' : '' ?>>
                            <?= e($f['name']) ?> — <?= e($f['city']) ?> (<?= e(date_fr($f['start_date'], 'short')) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($err = error('festival_id')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
            </label>
        </fieldset>

        <fieldset>
            <legend>Départ</legend>
            <div class="form-row">
                <label class="field-label <?= error('departure_city') ? 'has-error' : '' ?>">Ville de départ
                    <input type="text" name="departure_city" value="<?= old('departure_city') ?>" required maxlength="80" placeholder="Ex. Paris">
                    <?php if ($err = error('departure_city')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
                </label>
                <label class="field-label">Point de rendez-vous <span class="muted">(facultatif)</span>
                    <input type="text" name="meeting_point" value="<?= old('meeting_point') ?>" maxlength="160" placeholder="Ex. Gare, métro…">
                </label>
            </div>
            <div class="form-row">
                <label class="field-label <?= error('departure_at') ? 'has-error' : '' ?>">Date et heure de départ
                    <input type="datetime-local" name="departure_at" value="<?= old('departure_at') ?>" required>
                    <?php if ($err = error('departure_at')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
                </label>
                <label class="field-label <?= error('return_at') ? 'has-error' : '' ?>">Retour <span class="muted">(facultatif)</span>
                    <input type="datetime-local" name="return_at" value="<?= old('return_at') ?>">
                    <?php if ($err = error('return_at')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
                </label>
            </div>
        </fieldset>

        <fieldset>
            <legend>Véhicule et prix</legend>
            <div class="form-row form-row-3">
                <label class="field-label <?= error('seats') ? 'has-error' : '' ?>">Places proposées
                    <input type="number" name="seats" value="<?= old('seats', '3') ?>" min="1" max="8" required>
                    <?php if ($err = error('seats')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
                </label>
                <label class="field-label <?= error('price') ? 'has-error' : '' ?>">Prix par place (€)
                    <input type="number" name="price" value="<?= old('price') ?>" min="0" step="0.5" required placeholder="20">
                    <?php if ($err = error('price')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
                </label>
                <label class="field-label">Véhicule <span class="muted">(facultatif)</span>
                    <input type="text" name="vehicle" value="<?= old('vehicle') ?>" maxlength="80" placeholder="Ex. Clio, van…">
                </label>
            </div>
            <label class="field-label">Message aux passagers <span class="muted">(facultatif)</span>
                <textarea name="notes" rows="4" maxlength="1000" placeholder="Bagages, pauses, ambiance musicale…"><?= old('notes') ?></textarea>
            </label>
        </fieldset>

        <button type="submit" class="btn btn-primary btn-lg">Publier le trajet</button>
    </form>
</div>
