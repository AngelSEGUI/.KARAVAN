<?php
/**
 * @var array $housings
 * @var array $filters
 * @var array $festivals
 * @var array $types
 */
use App\Models\Housing;
?>
<header class="admin-head">
    <div>
        <span class="eyebrow">Modération</span>
        <h1>Logements <span class="head-count"><?= count($housings) ?></span></h1>
    </div>
</header>

<form class="toolbar" method="get" action="<?= url('/admin/logements') ?>" data-autosubmit>
    <label class="field">
        <span class="sr-only">Festival</span>
        <select name="festival">
            <option value="">Tous les festivals</option>
            <?php foreach ($festivals as $f): ?>
                <option value="<?= (int) $f['id'] ?>" <?= (string) $f['id'] === $filters['festival'] ? 'selected' : '' ?>><?= e($f['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="field">
        <span class="sr-only">Type</span>
        <select name="type">
            <option value="">Tous les types</option>
            <?php foreach ($types as $k => $label): ?>
                <option value="<?= e($k) ?>" <?= $filters['type'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Logement</th>
                <th>Hôte</th>
                <th>Festival</th>
                <th>Capacité</th>
                <th>Prix / nuit</th>
                <th>Demandes</th>
                <th class="col-actions"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($housings as $h): ?>
            <tr>
                <td data-label="Logement">
                    <div class="cell-main">
                        <span class="swatch" style="--h: <?= (int) $h['festival_hue'] ?>"></span>
                        <div>
                            <a href="<?= url('/logements/' . $h['id']) ?>" class="cell-title"><?= e($h['title']) ?></a>
                            <small class="muted"><?= e(Housing::typeLabel($h['type'])) ?> · <?= e($h['city']) ?>, <?= e(str_replace('.', ',', (string) (float) $h['distance_km'])) ?> km</small>
                        </div>
                    </div>
                </td>
                <td data-label="Hôte"><?= e($h['host_name']) ?></td>
                <td data-label="Festival"><?= e($h['festival_name']) ?></td>
                <td data-label="Capacité"><?= (int) $h['capacity'] ?> pers.</td>
                <td data-label="Prix / nuit"><?= price($h['price_night']) ?></td>
                <td data-label="Demandes"><span class="pill <?= $h['requests_count'] ? 'pill-ok' : '' ?>"><?= (int) $h['requests_count'] ?></span></td>
                <td class="col-actions">
                    <a href="<?= url('/logements/' . $h['id']) ?>" class="icon-btn" title="Voir"><svg class="icon"><use href="#i-external"/></svg></a>
                    <?= partial('admin-delete', [
                        'action'  => '/admin/logements/' . $h['id'] . '/supprimer',
                        'confirm' => "Supprimer « {$h['title']} » de {$h['host_name']}"
                            . ($h['requests_count'] ? " ? {$h['requests_count']} demande(s) seront annulées." : ' ?'),
                    ]) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (!$housings): ?><p class="table-empty">Aucun logement trouvé.</p><?php endif; ?>
</div>
