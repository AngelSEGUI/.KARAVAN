<?php
/**
 * Carte "billet" d'un covoiturage.
 * @var array $ride
 * @var bool $showFestival
 */
$showFestival ??= true;
$left = (int) $ride['seats_left'];
?>
<article class="ride-card" style="--h: <?= (int) $ride['festival_hue'] ?>">
    <a href="<?= url('/covoiturages/' . $ride['id']) ?>" class="card-link" aria-label="Voir le trajet depuis <?= e($ride['departure_city']) ?>"></a>
    <div class="ride-main">
        <div class="ride-when"><?= e(date_fr($ride['departure_at'], 'time')) ?></div>
        <div class="ride-route">
            <span class="ride-city"><?= e($ride['departure_city']) ?></span>
            <span class="ride-line" aria-hidden="true"><svg class="icon"><use href="#i-car"/></svg></span>
            <span class="ride-city ride-dest"><?= e($showFestival ? $ride['festival_name'] : $ride['festival_city']) ?></span>
        </div>
        <div class="ride-driver">
            <span class="avatar avatar-sm"><?= e(initials($ride['driver_name'])) ?></span>
            <span><?= e($ride['driver_name']) ?><?= $ride['vehicle'] ? ' · <span class="muted">' . e($ride['vehicle']) . '</span>' : '' ?></span>
        </div>
    </div>
    <div class="ride-stub">
        <strong class="ride-price"><?= price($ride['price']) ?></strong>
        <span class="muted">par place</span>
        <span class="seats <?= $left === 0 ? 'seats-full' : '' ?>">
            <?= $left === 0 ? 'Complet' : plural($left, 'place') ?>
        </span>
    </div>
</article>
