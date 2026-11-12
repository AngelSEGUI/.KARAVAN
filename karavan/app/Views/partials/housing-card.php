<?php
/**
 * @var array $housing
 * @var bool $showFestival
 */
use App\Models\Housing;

$showFestival ??= true;
$icon = in_array($housing['type'], ['tente', 'van'], true) ? ($housing['type'] === 'van' ? 'i-van' : 'i-tent') : 'i-home';
?>
<article class="housing-card" style="--h: <?= (int) $housing['festival_hue'] ?>">
    <a href="<?= url('/logements/' . $housing['id']) ?>" class="card-link" aria-label="<?= e($housing['title']) ?>"></a>
    <div class="housing-visual">
        <svg class="icon"><use href="#<?= $icon ?>"/></svg>
        <span class="distance"><?= e(str_replace('.', ',', (string) (float) $housing['distance_km'])) ?> km</span>
    </div>
    <div class="housing-body">
        <span class="eyebrow"><?= e(Housing::typeLabel($housing['type'])) ?></span>
        <h3><?= e($housing['title']) ?></h3>
        <p class="muted">
            <svg class="icon icon-sm"><use href="#i-pin"/></svg><?= e($housing['city']) ?>
            <?php if ($showFestival): ?> · près de <strong><?= e($housing['festival_name']) ?></strong><?php endif; ?>
        </p>
        <div class="housing-foot">
            <span class="muted"><svg class="icon icon-sm"><use href="#i-users"/></svg><?= plural((int) $housing['capacity'], 'pers.', 'pers.') ?></span>
            <span><strong><?= price($housing['price_night']) ?></strong> <span class="muted">/ nuit</span></span>
        </div>
    </div>
</article>
