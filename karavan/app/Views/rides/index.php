<?php
/**
 * @var array $rides
 * @var array $filters
 * @var array $festivals
 * @var array $cities
 */
?>
<section class="page-head">
    <div class="container page-head-row">
        <div>
            <span class="eyebrow">Covoiturage</span>
            <h1>Partagez la route</h1>
            <p class="lead">Rejoignez des festivaliers qui partent de chez vous et divisez les frais.</p>
        </div>
        <a href="<?= url('/covoiturages/nouveau') ?>" class="btn btn-primary"><svg class="icon"><use href="#i-plus"/></svg>Proposer un trajet</a>
    </div>
</section>

<div class="container">
    <form class="filters" method="get" action="<?= url('/covoiturages') ?>" data-autosubmit>
        <label class="field field-search">
            <span class="sr-only">Ville de départ</span>
            <svg class="icon"><use href="#i-pin"/></svg>
            <input type="search" name="from" value="<?= e($filters['from']) ?>" placeholder="Ville de départ" list="cities">
            <datalist id="cities">
                <?php foreach ($cities as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?>
            </datalist>
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
        <label class="field">
            <span class="sr-only">Places</span>
            <select name="seats">
                <option value="">Places libres</option>
                <?php foreach ([1, 2, 3, 4] as $n): ?>
                    <option value="<?= $n ?>" <?= (int) $filters['seats'] === $n ? 'selected' : '' ?>><?= $n ?>+ place<?= $n > 1 ? 's' : '' ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit" class="btn btn-primary js-hide">Rechercher</button>
    </form>

    <p class="results-count"><?= plural(count($rides), 'trajet') ?></p>

    <?php if ($rides): ?>
        <div class="grid grid-2">
            <?php foreach ($rides as $ride): ?>
                <?= partial('ride-card', ['ride' => $ride]) ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty">
            <svg class="icon"><use href="#i-car"/></svg>
            <h2>Aucun trajet ne correspond</h2>
            <p>Personne ne part encore d'ici. Et si c'était vous&nbsp;?</p>
            <a href="<?= url('/covoiturages/nouveau') ?>" class="btn btn-primary">Proposer un trajet</a>
        </div>
    <?php endif; ?>
</div>
