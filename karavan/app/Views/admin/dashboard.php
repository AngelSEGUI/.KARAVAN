<?php
/**
 * @var array $stats
 * @var array $activity
 * @var array $gaps
 * @var array $newMembers
 */
$kinds = [
    'ride'            => ['i-car', 'a proposé un trajet', '/covoiturages/'],
    'housing'         => ['i-home', 'a proposé un logement', '/logements/'],
    'ride_booking'    => ['i-ticket', 'a réservé un trajet', '/covoiturages/'],
    'housing_booking' => ['i-tent', 'a demandé un logement', '/logements/'],
    'user'            => ['i-user', 'a rejoint la communauté', null],
];
?>
<header class="admin-head">
    <div>
        <span class="eyebrow">Administration</span>
        <h1>Tableau de bord</h1>
    </div>
    <a href="<?= url('/admin/festivals/nouveau') ?>" class="btn btn-primary"><svg class="icon"><use href="#i-plus"/></svg>Nouveau festival</a>
</header>

<div class="kpis">
    <a href="<?= url('/admin/festivals') ?>" class="kpi"><svg class="icon"><use href="#i-ticket"/></svg><strong><?= (int) $stats['festivals'] ?></strong><span>Festivals</span></a>
    <a href="<?= url('/admin/membres') ?>" class="kpi"><svg class="icon"><use href="#i-users"/></svg><strong><?= (int) $stats['members'] ?></strong><span>Membres</span></a>
    <a href="<?= url('/admin/covoiturages') ?>" class="kpi"><svg class="icon"><use href="#i-car"/></svg><strong><?= (int) $stats['rides'] ?></strong><span>Trajets</span></a>
    <a href="<?= url('/admin/logements') ?>" class="kpi"><svg class="icon"><use href="#i-home"/></svg><strong><?= (int) $stats['housings'] ?></strong><span>Logements</span></a>
    <div class="kpi"><svg class="icon"><use href="#i-check"/></svg><strong><?= (int) $stats['bookings'] ?></strong><span>Réservations</span></div>
</div>

<div class="admin-grid">
    <section class="panel">
        <h2 class="panel-title">Activité récente</h2>
        <ul class="feed">
            <?php foreach ($activity as $a): [$icon, $verb, $link] = $kinds[$a['kind']]; ?>
                <li>
                    <span class="feed-icon"><svg class="icon"><use href="#<?= $icon ?>"/></svg></span>
                    <span class="feed-text">
                        <strong><?= e($a['actor']) ?></strong> <?= e($verb) ?>
                        <?php if ($link): ?>
                            <a href="<?= url($link . $a['target_id']) ?>"><?= e($a['label']) ?></a>
                        <?php elseif ($a['label']): ?>
                            <span class="muted">(<?= e($a['label']) ?>)</span>
                        <?php endif; ?>
                        <small><?= e(date_fr((string) $a['created_at'], 'time')) ?></small>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <div class="admin-col">
        <section class="panel">
            <h2 class="panel-title"><svg class="icon icon-warn"><use href="#i-alert"/></svg>Festivals à animer</h2>
            <p class="muted small">Sans covoiturage ou sans logement pour l'instant.</p>
            <?php if ($gaps): ?>
                <ul class="mini-list">
                    <?php foreach ($gaps as $f): ?>
                        <li>
                            <a href="<?= url('/festivals/' . $f['slug']) ?>"><?= e($f['name']) ?></a>
                            <span class="mini-tags">
                                <span class="pill <?= (int) $f['rides_count'] === 0 ? 'pill-warn' : '' ?>"><?= (int) $f['rides_count'] ?> trajet<?= (int) $f['rides_count'] > 1 ? 's' : '' ?></span>
                                <span class="pill <?= (int) $f['housings_count'] === 0 ? 'pill-warn' : '' ?>"><?= (int) $f['housings_count'] ?> logement<?= (int) $f['housings_count'] > 1 ? 's' : '' ?></span>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Tous les festivals ont des offres. 🎉</p>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h2 class="panel-title">Nouveaux membres</h2>
            <ul class="mini-list">
                <?php foreach ($newMembers as $m): ?>
                    <li>
                        <span class="person-inline"><span class="avatar avatar-sm"><?= e(initials($m['name'])) ?></span><?= e($m['name']) ?></span>
                        <span class="muted small"><?= e($m['city'] ?? '') ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a href="<?= url('/admin/membres') ?>" class="link-arrow small">Gérer les membres <svg class="icon"><use href="#i-arrow"/></svg></a>
        </section>
    </div>
</div>
