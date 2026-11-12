<?php
/**
 * @var array $housing
 * @var array $requests
 * @var bool $isHost
 * @var bool $hasBooked
 * @var array|null $currentUser
 */
use App\Models\Housing;

$icon = $housing['type'] === 'van' ? 'i-van' : ($housing['type'] === 'tente' ? 'i-tent' : 'i-home');
$nights = max(1, (int) round((strtotime($housing['festival_end']) - strtotime($housing['festival_start'])) / 86400) + 1);
?>
<div class="container detail">
    <a href="<?= url('/logements') ?>" class="back-link back-dark"><svg class="icon"><use href="#i-back"/></svg>Logements</a>

    <div class="detail-layout">
        <div class="detail-main">
            <div class="housing-banner" style="--h: <?= (int) $housing['festival_hue'] ?>">
                <svg class="icon"><use href="#<?= $icon ?>"/></svg>
                <div>
                    <span class="eyebrow"><?= e(Housing::typeLabel($housing['type'])) ?></span>
                    <h1><?= e($housing['title']) ?></h1>
                    <p><svg class="icon icon-sm"><use href="#i-pin"/></svg><?= e($housing['city']) ?> · à <?= e(str_replace('.', ',', (string) (float) $housing['distance_km'])) ?> km de
                        <a href="<?= url('/festivals/' . $housing['festival_slug']) ?>"><?= e($housing['festival_name']) ?></a></p>
                </div>
            </div>

            <section class="card">
                <div class="facts-inline">
                    <span><svg class="icon"><use href="#i-users"/></svg>Jusqu'à <?= (int) $housing['capacity'] ?> pers.</span>
                    <span><svg class="icon"><use href="#i-calendar"/></svg><?= e(date_range($housing['festival_start'], $housing['festival_end'])) ?></span>
                </div>
                <?php if ($housing['description']): ?>
                    <p><?= nl2br(e($housing['description'])) ?></p>
                <?php endif; ?>
                <?php if ($housing['amenities']): ?>
                    <h2 class="h4">Équipements</h2>
                    <ul class="amenities">
                        <?php foreach (explode(',', $housing['amenities']) as $a): ?>
                            <li><svg class="icon icon-sm"><use href="#i-check"/></svg><?= e(trim($a)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <section class="card">
                <h2 class="h4">Votre hôte</h2>
                <div class="person">
                    <span class="avatar avatar-lg"><?= e(initials($housing['host_name'])) ?></span>
                    <div>
                        <strong><?= e($housing['host_name']) ?></strong>
                        <span class="muted">Membre de la communauté Karavan</span>
                    </div>
                </div>
            </section>

            <?php if ($isHost && $requests): ?>
                <section class="card">
                    <h2 class="h4">Demandes reçues (<?= count($requests) ?>)</h2>
                    <ul class="people-list">
                        <?php foreach ($requests as $r): ?>
                            <li>
                                <span class="avatar avatar-sm"><?= e(initials($r['name'])) ?></span>
                                <?= e($r['name']) ?> <span class="muted">· <?= plural((int) $r['guests'], 'pers.', 'pers.') ?>, <?= plural((int) $r['nights'], 'nuit') ?></span>
                                <?php if ($r['message']): ?><small class="muted block">« <?= e($r['message']) ?> »</small><?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>
        </div>

        <aside class="booking-box">
            <div class="booking-price"><?= price($housing['price_night']) ?> <span>/ nuit</span></div>

            <?php if ($isHost): ?>
                <p class="notice">C'est votre logement. <?= $requests ? plural(count($requests), 'demande reçue', 'demandes reçues') : 'Aucune demande pour l\'instant.' ?></p>
            <?php elseif ($hasBooked): ?>
                <p class="notice notice-success"><svg class="icon icon-sm"><use href="#i-check"/></svg>Demande envoyée à <?= e($housing['host_name']) ?>.</p>
            <?php elseif (!$currentUser): ?>
                <a href="<?= url('/connexion') ?>" class="btn btn-primary btn-block">Se connecter pour réserver</a>
            <?php else: ?>
                <form method="post" action="<?= url('/logements/' . $housing['id'] . '/reserver') ?>" class="form" data-price="<?= (float) $housing['price_night'] ?>">
                    <?= csrf_field() ?>
                    <div class="form-row">
                        <label class="field-label">Voyageurs
                            <select name="guests">
                                <?php for ($i = 1; $i <= (int) $housing['capacity']; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </label>
                        <label class="field-label">Nuits
                            <select name="nights" data-qty>
                                <?php for ($i = 1; $i <= $nights + 1; $i++): ?>
                                    <option value="<?= $i ?>" <?= $i === $nights ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </label>
                    </div>
                    <label class="field-label">Message à l'hôte <span class="muted">(facultatif)</span>
                        <textarea name="message" rows="3" maxlength="500" placeholder="Présentez-vous, heure d'arrivée…"></textarea>
                    </label>
                    <div class="total">Total <strong data-total><?= price($housing['price_night'] * $nights) ?></strong></div>
                    <button type="submit" class="btn btn-primary btn-block">Envoyer la demande</button>
                </form>
            <?php endif; ?>
        </aside>
    </div>
</div>
