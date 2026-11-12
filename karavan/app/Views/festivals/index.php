<?php
/**
 * @var array $festivals
 * @var array $filters
 * @var array $genres
 * @var array $regions
 */
$hasFilters = array_filter(array_diff_key($filters, ['sort' => 1])) !== [];
?>
<section class="page-head">
    <div class="container">
        <span class="eyebrow">Découverte</span>
        <h1>Trouvez votre prochain festival</h1>
        <p class="lead"><?= plural(count($festivals), 'festival trouvé', 'festivals trouvés') ?></p>
    </div>
</section>

<div class="container">
    <form class="filters" method="get" action="<?= url('/festivals') ?>" data-autosubmit>
        <label class="field field-search">
            <span class="sr-only">Recherche</span>
            <svg class="icon"><use href="#i-search"/></svg>
            <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Nom, ville, artiste…">
        </label>
        <label class="field">
            <span class="sr-only">Style</span>
            <select name="genre">
                <option value="">Tous les styles</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= e($g) ?>" <?= $filters['genre'] === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field">
            <span class="sr-only">Région</span>
            <select name="region">
                <option value="">Toutes les régions</option>
                <?php foreach ($regions as $r): ?>
                    <option value="<?= e($r) ?>" <?= $filters['region'] === $r ? 'selected' : '' ?>><?= e($r) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field">
            <span class="sr-only">Mois</span>
            <select name="month">
                <option value="">Tous les mois</option>
                <?php foreach ([5, 6, 7, 8, 9] as $m): ?>
                    <option value="<?= $m ?>" <?= (int) $filters['month'] === $m ? 'selected' : '' ?>><?= ucfirst(month_name($m)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field">
            <span class="sr-only">Trier par</span>
            <select name="sort">
                <option value="date" <?= $filters['sort'] === 'date' ? 'selected' : '' ?>>Par date</option>
                <option value="popular" <?= $filters['sort'] === 'popular' ? 'selected' : '' ?>>Les plus fréquentés</option>
                <option value="offers" <?= $filters['sort'] === 'offers' ? 'selected' : '' ?>>Le plus d'offres</option>
                <option value="name" <?= $filters['sort'] === 'name' ? 'selected' : '' ?>>De A à Z</option>
            </select>
        </label>
        <button type="submit" class="btn btn-primary js-hide">Filtrer</button>
        <?php if ($hasFilters): ?>
            <a href="<?= url('/festivals') ?>" class="btn btn-ghost">Réinitialiser</a>
        <?php endif; ?>
    </form>

    <?php if ($festivals): ?>
        <div class="grid grid-3 section-tight">
            <?php foreach ($festivals as $festival): ?>
                <?= partial('festival-card', ['festival' => $festival]) ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty">
            <svg class="icon"><use href="#i-compass"/></svg>
            <h2>Aucun festival sur cette route</h2>
            <p>Essayez d'élargir vos filtres.</p>
            <a href="<?= url('/festivals') ?>" class="btn btn-primary">Voir tous les festivals</a>
        </div>
    <?php endif; ?>
</div>
