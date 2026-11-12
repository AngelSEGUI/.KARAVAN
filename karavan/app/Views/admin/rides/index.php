<?php
/**
 * @var array $rides
 * @var array $filters
 * @var array $festivals
 */
?>
<header class="admin-head">
    <div>
        <span class="eyebrow">Modération</span>
        <h1>Covoiturages <span class="head-count"><?= count($rides) ?></span></h1>
    </div>
</header>

<form class="toolbar" method="get" action="<?= url('/admin/covoiturages') ?>" data-autosubmit>
    <label class="field field-search">
        <span class="sr-only">Ville de départ</span>
        <svg class="icon"><use href="#i-search"/></svg>
        <input type="search" name="from" value="<?= e($filters['from']) ?>" placeholder="Ville de départ">
    </label>
    <label class="field">
        <span class="sr-only">Festival</span>
        <select name="festival">
            <option value="">Tous les festivals</option>
            <?php foreach ($festivals as $f): ?>
                <option value="<?= (int) $f['id'] ?>" <?= (string) $f['id'] === $filters['festival'] ? 'selected' : '' ?>><?= e($f['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Trajet</th>
                <th>Conducteur</th>
                <th>Départ</th>
                <th>Places</th>
                <th>Prix</th>
                <th class="col-actions"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rides as $r):
            $booked = (int) $r['seats'] - (int) $r['seats_left']; ?>
            <tr>
                <td data-label="Trajet">
                    <div class="cell-main">
                        <span class="swatch" style="--h: <?= (int) $r['festival_hue'] ?>"></span>
                        <div>
                            <a href="<?= url('/covoiturages/' . $r['id']) ?>" class="cell-title"><?= e($r['departure_city']) ?> → <?= e($r['festival_name']) ?></a>
                            <small class="muted"><?= e($r['vehicle'] ?? '') ?></small>
                        </div>
                    </div>
                </td>
                <td data-label="Conducteur"><?= e($r['driver_name']) ?></td>
                <td data-label="Départ"><?= e(date_fr($r['departure_at'], 'time')) ?></td>
                <td data-label="Places">
                    <span class="meter" title="<?= $booked ?> réservée(s) sur <?= (int) $r['seats'] ?>">
                        <span style="width: <?= (int) round($booked / max(1, (int) $r['seats']) * 100) ?>%"></span>
                    </span>
                    <small><?= $booked ?>/<?= (int) $r['seats'] ?></small>
                </td>
                <td data-label="Prix"><?= price($r['price']) ?></td>
                <td class="col-actions">
                    <a href="<?= url('/covoiturages/' . $r['id']) ?>" class="icon-btn" title="Voir"><svg class="icon"><use href="#i-external"/></svg></a>
                    <?= partial('admin-delete', [
                        'action'  => '/admin/covoiturages/' . $r['id'] . '/supprimer',
                        'confirm' => "Supprimer le trajet {$r['departure_city']} → {$r['festival_name']} de {$r['driver_name']}"
                            . ($booked ? " ? {$booked} place(s) réservée(s) seront annulées." : ' ?'),
                    ]) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (!$rides): ?><p class="table-empty">Aucun trajet trouvé.</p><?php endif; ?>
</div>
