<?php
/**
 * @var array $festivals
 * @var string $q
 */
?>
<header class="admin-head">
    <div>
        <span class="eyebrow">Catalogue</span>
        <h1>Festivals <span class="head-count"><?= count($festivals) ?></span></h1>
    </div>
    <a href="<?= url('/admin/festivals/nouveau') ?>" class="btn btn-primary"><svg class="icon"><use href="#i-plus"/></svg>Nouveau festival</a>
</header>

<form class="toolbar" method="get" action="<?= url('/admin/festivals') ?>" data-autosubmit>
    <label class="field field-search">
        <span class="sr-only">Rechercher</span>
        <svg class="icon"><use href="#i-search"/></svg>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Nom, ville, artiste…">
    </label>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Festival</th>
                <th>Dates</th>
                <th>Offres</th>
                <th>À la une</th>
                <th class="col-actions"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($festivals as $f): ?>
            <tr>
                <td data-label="Festival">
                    <div class="cell-main">
                        <span class="swatch" style="--h: <?= (int) $f['hue'] ?>"></span>
                        <div>
                            <a href="<?= url('/admin/festivals/' . $f['id'] . '/modifier') ?>" class="cell-title"><?= e($f['name']) ?></a>
                            <small class="muted"><?= e($f['city']) ?> · <?= e($f['region']) ?></small>
                        </div>
                    </div>
                </td>
                <td data-label="Dates"><?= e(date_range($f['start_date'], $f['end_date'])) ?></td>
                <td data-label="Offres">
                    <span class="offer-counts">
                        <span title="Covoiturages"><svg class="icon icon-sm"><use href="#i-car"/></svg><?= (int) $f['rides_count'] ?></span>
                        <span title="Logements"><svg class="icon icon-sm"><use href="#i-home"/></svg><?= (int) $f['housings_count'] ?></span>
                    </span>
                </td>
                <td data-label="À la une">
                    <form method="post" action="<?= url('/admin/festivals/' . $f['id'] . '/une') ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="star-toggle <?= (int) $f['featured'] ? 'is-on' : '' ?>"
                                aria-pressed="<?= (int) $f['featured'] ? 'true' : 'false' ?>"
                                title="<?= (int) $f['featured'] ? 'Retirer de la une' : 'Mettre à la une' ?>">
                            <svg class="icon"><use href="#i-star"/></svg>
                        </button>
                    </form>
                </td>
                <td class="col-actions">
                    <a href="<?= url('/festivals/' . $f['slug']) ?>" class="icon-btn" title="Voir sur le site"><svg class="icon"><use href="#i-external"/></svg></a>
                    <a href="<?= url('/admin/festivals/' . $f['id'] . '/modifier') ?>" class="icon-btn" title="Modifier"><svg class="icon"><use href="#i-edit"/></svg></a>
                    <?= partial('admin-delete', [
                        'action'  => '/admin/festivals/' . $f['id'] . '/supprimer',
                        'confirm' => "Supprimer « {$f['name']} » ? Ses " . ($f['rides_count'] + $f['housings_count']) . " offres et leurs réservations seront aussi supprimées.",
                    ]) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (!$festivals): ?><p class="table-empty">Aucun festival trouvé.</p><?php endif; ?>
</div>
