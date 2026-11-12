<?php
/**
 * Formulaire de création / modification d'un festival.
 * @var array $festival
 * @var array $regions
 * @var array $genres
 */
$isEdit = isset($festival['id']);
$val = fn (string $k, mixed $d = '') => old($k, $festival[$k] ?? $d);
$hue = (int) ($_SESSION['_old']['hue'] ?? $festival['hue'] ?? 20);
$featured = isset($_SESSION['_old']) ? isset($_SESSION['_old']['featured']) : (int) ($festival['featured'] ?? 0) === 1;
$field = function (string $name, string $label, string $type = 'text', array $attrs = []) use ($val): string {
    $err = error($name);
    $extra = '';
    foreach ($attrs as $k => $v) {
        $extra .= ' ' . $k . '="' . e($v) . '"';
    }
    return '<label class="field-label' . ($err ? ' has-error' : '') . '">' . $label
        . '<input type="' . $type . '" name="' . $name . '" value="' . $val($name) . '"' . $extra . '>'
        . ($err ? '<span class="error">' . e($err) . '</span>' : '') . '</label>';
};
?>
<header class="admin-head">
    <div>
        <a href="<?= url('/admin/festivals') ?>" class="back-link back-dark"><svg class="icon"><use href="#i-back"/></svg>Festivals</a>
        <h1><?= $isEdit ? e($festival['name']) : 'Nouveau festival' ?></h1>
    </div>
    <?php if ($isEdit): ?>
        <a href="<?= url('/festivals/' . $festival['slug']) ?>" class="btn btn-ghost"><svg class="icon"><use href="#i-external"/></svg>Voir la fiche</a>
    <?php endif; ?>
</header>

<form method="post" action="<?= url($isEdit ? '/admin/festivals/' . $festival['id'] : '/admin/festivals') ?>" class="admin-form" novalidate>
    <?= csrf_field() ?>

    <div class="admin-form-main">
        <section class="panel form">
            <h2 class="panel-title">Informations</h2>
            <?= $field('name', 'Nom du festival', 'text', ['required' => 'required', 'maxlength' => 120, 'data-cover-name' => '']) ?>
            <label class="field-label <?= error('slug') ? 'has-error' : '' ?>">Adresse de la fiche <span class="muted">(générée depuis le nom si vide)</span>
                <span class="input-prefix"><span>/festivals/</span><input type="text" name="slug" value="<?= $val('slug') ?>" maxlength="120" placeholder="mon-festival"></span>
                <?php if ($err = error('slug')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
            </label>
            <?= $field('tagline', 'Accroche', 'text', ['maxlength' => 200, 'placeholder' => 'Une phrase pour donner envie']) ?>
            <label class="field-label">Description
                <textarea name="description" rows="5" maxlength="5000"><?= $val('description') ?></textarea>
            </label>
        </section>

        <section class="panel form">
            <h2 class="panel-title">Lieu et dates</h2>
            <div class="form-row">
                <?= $field('city', 'Ville', 'text', ['required' => 'required', 'maxlength' => 80]) ?>
                <label class="field-label <?= error('region') ? 'has-error' : '' ?>">Région
                    <input type="text" name="region" value="<?= $val('region') ?>" list="regions" required maxlength="80">
                    <datalist id="regions"><?php foreach ($regions as $r): ?><option value="<?= e($r) ?>"><?php endforeach; ?></datalist>
                    <?php if ($err = error('region')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
                </label>
            </div>
            <?= $field('venue', 'Site / lieu-dit', 'text', ['maxlength' => 160]) ?>
            <div class="form-row">
                <?= $field('start_date', 'Début', 'date', ['required' => 'required']) ?>
                <?= $field('end_date', 'Fin', 'date', ['required' => 'required']) ?>
            </div>
        </section>

        <section class="panel form">
            <h2 class="panel-title">Programmation</h2>
            <label class="field-label <?= error('genres') ? 'has-error' : '' ?>">Styles <span class="muted">(séparés par des virgules)</span>
                <input type="text" name="genres" value="<?= $val('genres') ?>" required maxlength="160" placeholder="Rock, Électro">
                <?php if ($err = error('genres')): ?><span class="error"><?= e($err) ?></span><?php endif; ?>
                <span class="hint">Existants : <?= e(implode(', ', $genres)) ?></span>
            </label>
            <label class="field-label">Artistes <span class="muted">(séparés par des virgules, tête d'affiche en premier)</span>
                <textarea name="lineup" rows="3" maxlength="2000"><?= $val('lineup') ?></textarea>
            </label>
            <div class="form-row">
                <?= $field('attendance', 'Affluence attendue', 'number', ['min' => 0, 'step' => 1000]) ?>
                <?= $field('price_from', 'Billet dès (€)', 'number', ['min' => 0, 'step' => '0.01']) ?>
            </div>
        </section>
    </div>

    <aside class="admin-form-side">
        <section class="panel">
            <h2 class="panel-title">Couverture</h2>
            <div class="cover-preview" data-cover-preview>
                <?= partial('festival-cover', ['festival' => ['name' => $festival['name'] ?? 'Nom du festival', 'hue' => $hue]]) ?>
            </div>
            <label class="field-label">Couleur
                <input type="range" name="hue" min="0" max="359" value="<?= $hue ?>" class="hue-range" data-hue>
            </label>
        </section>

        <section class="panel">
            <label class="switch">
                <input type="checkbox" name="featured" value="1" <?= $featured ? 'checked' : '' ?>>
                <span class="switch-track"></span>
                <span><strong>À la une</strong><small class="muted block">Mis en avant sur la page découverte</small></span>
            </label>
        </section>

        <div class="side-actions">
            <button type="submit" class="btn btn-primary btn-block btn-lg"><?= $isEdit ? 'Enregistrer' : 'Créer le festival' ?></button>
        </div>
    </aside>
</form>

<?php if ($isEdit): ?>
    <section class="panel danger-zone">
        <div>
            <h2 class="panel-title">Supprimer ce festival</h2>
            <p class="muted small">Les covoiturages, logements et réservations associés seront supprimés. Action irréversible.</p>
        </div>
        <?= partial('admin-delete', [
            'action'  => '/admin/festivals/' . $festival['id'] . '/supprimer',
            'confirm' => "Supprimer définitivement « {$festival['name']} » et toutes ses offres ?",
            'label'   => 'Supprimer',
        ]) ?>
    </section>
<?php endif; ?>
