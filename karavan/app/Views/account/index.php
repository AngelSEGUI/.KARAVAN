<?php
/**
 * @var array $currentUser
 * @var array $myRides
 * @var array $myHousings
 * @var array $rideBookings
 * @var array $stayBookings
 */
?>
<section class="page-head">
    <div class="container page-head-row">
        <div class="person">
            <span class="avatar avatar-xl"><?= e(initials($currentUser['name'])) ?></span>
            <div>
                <span class="eyebrow">Mon espace</span>
                <h1><?= e($currentUser['name']) ?></h1>
                <p class="muted"><?= e($currentUser['city'] ?? '') ?></p>
            </div>
        </div>
    </div>
</section>

<div class="container account">
    <section>
        <h2 class="h3">Mes réservations</h2>
        <?php if (!$rideBookings && !$stayBookings): ?>
            <div class="empty empty-sm">
                <p>Aucune réservation pour l'instant.</p>
                <a href="<?= url('/festivals') ?>" class="btn btn-primary btn-sm">Trouver un festival</a>
            </div>
        <?php else: ?>
            <ul class="list-rows">
                <?php foreach ($rideBookings as $b): ?>
                    <li style="--h: <?= (int) $b['festival_hue'] ?>">
                        <a href="<?= url('/covoiturages/' . $b['ride_id']) ?>">
                            <span class="row-icon"><svg class="icon"><use href="#i-car"/></svg></span>
                            <span class="row-main"><strong><?= e($b['departure_city']) ?> → <?= e($b['festival_name']) ?></strong>
                                <small><?= e(date_fr($b['departure_at'], 'time')) ?> · avec <?= e($b['driver_name']) ?></small></span>
                            <span class="row-side"><?= plural((int) $b['seats'], 'place') ?><small><?= price($b['price'] * $b['seats']) ?></small></span>
                        </a>
                    </li>
                <?php endforeach; ?>
                <?php foreach ($stayBookings as $b): ?>
                    <li style="--h: <?= (int) $b['festival_hue'] ?>">
                        <a href="<?= url('/logements/' . $b['housing_id']) ?>">
                            <span class="row-icon"><svg class="icon"><use href="#i-home"/></svg></span>
                            <span class="row-main"><strong><?= e($b['title']) ?></strong>
                                <small><?= e($b['festival_name']) ?> · chez <?= e($b['host_name']) ?></small></span>
                            <span class="row-side"><?= plural((int) $b['nights'], 'nuit') ?><small><?= price($b['price_night'] * $b['nights']) ?></small></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section>
        <div class="section-head section-head-sm">
            <h2 class="h3">Mes trajets proposés</h2>
            <a href="<?= url('/covoiturages/nouveau') ?>" class="btn btn-ghost btn-sm"><svg class="icon"><use href="#i-plus"/></svg>Nouveau</a>
        </div>
        <?php if ($myRides): ?>
            <div class="grid grid-2">
                <?php foreach ($myRides as $ride): ?>
                    <?= partial('ride-card', ['ride' => $ride]) ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="muted">Vous n'avez pas encore proposé de trajet.</p>
        <?php endif; ?>
    </section>

    <section>
        <div class="section-head section-head-sm">
            <h2 class="h3">Mes logements proposés</h2>
            <a href="<?= url('/logements/nouveau') ?>" class="btn btn-ghost btn-sm"><svg class="icon"><use href="#i-plus"/></svg>Nouveau</a>
        </div>
        <?php if ($myHousings): ?>
            <div class="grid grid-3">
                <?php foreach ($myHousings as $housing): ?>
                    <?= partial('housing-card', ['housing' => $housing]) ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="muted">Vous n'avez pas encore proposé de logement.</p>
        <?php endif; ?>
    </section>
</div>
