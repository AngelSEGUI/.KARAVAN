<?php
/**
 * @var array $festival
 * @var array $rides
 * @var array $housings
 */
$days = (int) round((strtotime($festival['end_date']) - strtotime($festival['start_date'])) / 86400) + 1;
?>
<section class="festival-hero" style="--h: <?= (int) $festival['hue'] ?>">
    <?= partial('festival-cover', ['festival' => $festival, 'size' => 'hero']) ?>
    <div class="container festival-hero-inner">
        <a href="<?= url('/festivals') ?>" class="back-link"><svg class="icon"><use href="#i-back"/></svg>Festivals</a>
        <div class="tags">
            <?php foreach (explode(',', $festival['genres']) as $g): ?>
                <a href="<?= url('/festivals', ['genre' => $g]) ?>" class="tag tag-light"><?= e($g) ?></a>
            <?php endforeach; ?>
        </div>
        <h1><?= e($festival['name']) ?></h1>
        <?php if ($festival['tagline']): ?><p class="lead"><?= e($festival['tagline']) ?></p><?php endif; ?>
    </div>
</section>

<div class="container festival-layout">
    <aside class="festival-facts">
        <dl>
            <div><dt><svg class="icon"><use href="#i-calendar"/></svg>Dates</dt><dd><?= e(date_range($festival['start_date'], $festival['end_date'])) ?><small><?= plural($days, 'jour') ?></small></dd></div>
            <div><dt><svg class="icon"><use href="#i-pin"/></svg>Lieu</dt><dd><?= e($festival['venue'] ?: $festival['city']) ?><small><?= e($festival['city']) ?>, <?= e($festival['region']) ?></small></dd></div>
            <?php if ($festival['attendance'] !== null): ?>
                <div><dt><svg class="icon"><use href="#i-users"/></svg>Affluence</dt><dd><?= number_format((int) $festival['attendance'], 0, ',', ' ') ?><small>festivaliers attendus</small></dd></div>
            <?php endif; ?>
            <?php if ($festival['price_from'] !== null): ?>
                <div><dt><svg class="icon"><use href="#i-ticket"/></svg>Billets</dt><dd>dès <?= price((float) $festival['price_from']) ?><small>pass journée</small></dd></div>
            <?php endif; ?>
        </dl>
        <div class="facts-actions">
            <a href="<?= url('/covoiturages/nouveau', ['festival' => $festival['id']]) ?>" class="btn btn-primary btn-block"><svg class="icon"><use href="#i-car"/></svg>Proposer un trajet</a>
            <a href="<?= url('/logements/nouveau', ['festival' => $festival['id']]) ?>" class="btn btn-ghost btn-block"><svg class="icon"><use href="#i-home"/></svg>Proposer un logement</a>
        </div>
    </aside>

    <div class="festival-content">
        <section>
            <h2 class="h3">Le festival</h2>
            <p><?= nl2br(e($festival['description'])) ?></p>
            <?php if ($festival['lineup']): ?>
                <h3 class="h4">Programmation</h3>
                <ul class="lineup">
                    <?php foreach (explode(',', $festival['lineup']) as $artist): ?>
                        <li><?= e(trim($artist)) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p class="muted small">Programmation donnée à titre indicatif.</p>
            <?php endif; ?>
        </section>

        <div class="tabs" data-tabs>
            <div class="tab-list" role="tablist">
                <button role="tab" aria-selected="true" aria-controls="tab-rides" id="t-rides">
                    <svg class="icon"><use href="#i-car"/></svg>Covoiturages <span class="count"><?= count($rides) ?></span>
                </button>
                <button role="tab" aria-selected="false" aria-controls="tab-housings" id="t-housings">
                    <svg class="icon"><use href="#i-home"/></svg>Logements <span class="count"><?= count($housings) ?></span>
                </button>
            </div>

            <div role="tabpanel" id="tab-rides" aria-labelledby="t-rides" class="tab-panel">
                <?php if ($rides): ?>
                    <div class="stack">
                        <?php foreach ($rides as $ride): ?>
                            <?= partial('ride-card', ['ride' => $ride, 'showFestival' => false]) ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty empty-sm">
                        <p>Aucun trajet pour l'instant. Soyez le premier à proposer le vôtre&nbsp;!</p>
                        <a href="<?= url('/covoiturages/nouveau', ['festival' => $festival['id']]) ?>" class="btn btn-primary btn-sm">Proposer un trajet</a>
                    </div>
                <?php endif; ?>
            </div>

            <div role="tabpanel" id="tab-housings" aria-labelledby="t-housings" class="tab-panel" hidden>
                <?php if ($housings): ?>
                    <div class="grid grid-2">
                        <?php foreach ($housings as $housing): ?>
                            <?= partial('housing-card', ['housing' => $housing, 'showFestival' => false]) ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty empty-sm">
                        <p>Aucun logement proposé près de ce festival pour l'instant.</p>
                        <a href="<?= url('/logements/nouveau', ['festival' => $festival['id']]) ?>" class="btn btn-primary btn-sm">Proposer un logement</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
