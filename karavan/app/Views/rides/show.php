<?php
/**
 * @var array $ride
 * @var array $passengers
 * @var bool $isDriver
 * @var bool $hasBooked
 * @var array|null $currentUser
 */
$left = (int) $ride['seats_left'];
?>
<div class="container detail">
    <a href="<?= url('/covoiturages') ?>" class="back-link back-dark"><svg class="icon"><use href="#i-back"/></svg>Covoiturages</a>

    <div class="detail-layout">
        <div class="detail-main">
            <div class="ticket" style="--h: <?= (int) $ride['festival_hue'] ?>">
                <div class="ticket-top">
                    <span class="eyebrow">Covoiturage vers</span>
                    <a href="<?= url('/festivals/' . $ride['festival_slug']) ?>" class="ticket-festival"><?= e($ride['festival_name']) ?></a>
                </div>
                <div class="timeline">
                    <div class="timeline-stop">
                        <span class="dot"></span>
                        <div>
                            <strong><?= e($ride['departure_city']) ?></strong>
                            <span><?= e(date_fr($ride['departure_at'], 'time')) ?></span>
                            <?php if ($ride['meeting_point']): ?><small>RDV : <?= e($ride['meeting_point']) ?></small><?php endif; ?>
                        </div>
                    </div>
                    <div class="timeline-stop">
                        <span class="dot dot-end"></span>
                        <div>
                            <strong><?= e($ride['festival_city']) ?></strong>
                            <span><?= e($ride['festival_name']) ?></span>
                        </div>
                    </div>
                </div>
                <?php if ($ride['return_at']): ?>
                    <p class="ticket-return"><svg class="icon icon-sm"><use href="#i-back"/></svg>Retour prévu le <?= e(date_fr($ride['return_at'], 'time')) ?></p>
                <?php else: ?>
                    <p class="ticket-return">Aller simple</p>
                <?php endif; ?>
            </div>

            <section class="card">
                <h2 class="h4">Votre conducteur</h2>
                <div class="person">
                    <span class="avatar avatar-lg"><?= e(initials($ride['driver_name'])) ?></span>
                    <div>
                        <strong><?= e($ride['driver_name']) ?></strong>
                        <span class="muted"><?= e($ride['driver_city'] ?? '') ?><?= $ride['vehicle'] ? ' · ' . e($ride['vehicle']) : '' ?></span>
                    </div>
                </div>
                <?php if ($ride['notes']): ?>
                    <blockquote><?= nl2br(e($ride['notes'])) ?></blockquote>
                <?php endif; ?>
            </section>

            <?php if ($passengers): ?>
                <section class="card">
                    <h2 class="h4">À bord (<?= array_sum(array_column($passengers, 'seats')) ?>)</h2>
                    <ul class="people-list">
                        <?php foreach ($passengers as $p): ?>
                            <li>
                                <span class="avatar avatar-sm"><?= e(initials($p['name'])) ?></span>
                                <?= e($p['name']) ?> <span class="muted">· <?= plural((int) $p['seats'], 'place') ?></span>
                                <?php if ($isDriver && $p['message']): ?><small class="muted block">« <?= e($p['message']) ?> »</small><?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>
        </div>

        <aside class="booking-box">
            <div class="booking-price"><?= price($ride['price']) ?> <span>/ place</span></div>
            <p class="seats-line <?= $left === 0 ? 'is-full' : '' ?>">
                <?= $left === 0 ? 'Complet' : plural($left, 'place restante', 'places restantes') ?> sur <?= (int) $ride['seats'] ?>
            </p>

            <?php if ($isDriver): ?>
                <p class="notice">C'est votre trajet. Les réservations apparaissent dans <a href="<?= url('/mon-espace') ?>">votre espace</a>.</p>
            <?php elseif ($hasBooked): ?>
                <p class="notice notice-success"><svg class="icon icon-sm"><use href="#i-check"/></svg>Vous êtes inscrit sur ce trajet.</p>
            <?php elseif ($left === 0): ?>
                <p class="notice">Ce trajet est complet. <a href="<?= url('/covoiturages', ['festival' => $ride['festival_id']]) ?>">Voir d'autres trajets</a></p>
            <?php elseif (!$currentUser): ?>
                <a href="<?= url('/connexion') ?>" class="btn btn-primary btn-block">Se connecter pour réserver</a>
            <?php else: ?>
                <form method="post" action="<?= url('/covoiturages/' . $ride['id'] . '/reserver') ?>" class="form" data-price="<?= (float) $ride['price'] ?>">
                    <?= csrf_field() ?>
                    <label class="field-label">Nombre de places
                        <select name="seats" data-qty>
                            <?php for ($i = 1; $i <= $left; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </label>
                    <label class="field-label">Un mot pour <?= e(explode(' ', $ride['driver_name'])[0]) ?> <span class="muted">(facultatif)</span>
                        <textarea name="message" rows="3" maxlength="500" placeholder="Bagages, musique, horaires…"></textarea>
                    </label>
                    <div class="total">Total <strong data-total><?= price($ride['price']) ?></strong></div>
                    <button type="submit" class="btn btn-primary btn-block">Réserver</button>
                </form>
            <?php endif; ?>
        </aside>
    </div>
</div>
