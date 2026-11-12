<?php
/**
 * @var array $featured
 * @var array $upcoming
 * @var array $rides
 * @var array $housings
 * @var array $genres
 * @var array $stats
 */
?>
<section class="hero">
    <div class="container hero-inner">
        <div class="hero-copy">
            <span class="kicker"><svg class="icon icon-sm"><use href="#i-music"/></svg>Saison des festivals</span>
            <h1>La route des festivals se fait <em>ensemble</em>.</h1>
            <p class="lead">Découvrez les festivals qui vous ressemblent, partagez la route avec d'autres festivaliers et dormez chez un membre de la communauté, à deux pas des scènes.</p>

            <form class="hero-search" action="<?= url('/festivals') ?>" method="get" role="search">
                <svg class="icon"><use href="#i-search"/></svg>
                <input type="search" name="q" placeholder="Un festival, une ville, un artiste…" aria-label="Rechercher un festival">
                <button class="btn btn-primary" type="submit">Explorer</button>
            </form>

            <div class="hero-shortcuts">
                <a href="<?= url('/covoiturages') ?>" class="shortcut">
                    <svg class="icon"><use href="#i-car"/></svg>
                    <span><strong>Trouver un trajet</strong><small><?= plural((int) $stats['rides'], 'covoiturage') ?> en ligne</small></span>
                </a>
                <a href="<?= url('/logements') ?>" class="shortcut">
                    <svg class="icon"><use href="#i-home"/></svg>
                    <span><strong>Trouver un logement</strong><small><?= plural((int) $stats['housings'], 'offre') ?> de la communauté</small></span>
                </a>
            </div>
        </div>

        <?php if ($featured): $top = $featured[0]; ?>
            <a href="<?= url('/festivals/' . $top['slug']) ?>" class="hero-poster" style="--h: <?= (int) $top['hue'] ?>">
                <?= partial('festival-cover', ['festival' => $top, 'size' => 'hero']) ?>
                <div class="hero-poster-info">
                    <span class="badge">À la une</span>
                    <strong><?= e($top['name']) ?></strong>
                    <span><?= e($top['city']) ?> · <?= e(date_range($top['start_date'], $top['end_date'])) ?></span>
                </div>
            </a>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Mis en avant</span>
                <h2>À l'affiche cette saison</h2>
            </div>
            <a href="<?= url('/festivals') ?>" class="link-arrow">Tous les festivals <svg class="icon"><use href="#i-arrow"/></svg></a>
        </div>
        <div class="rail" data-rail>
            <?php foreach ($featured as $festival): ?>
                <?= partial('festival-card', ['festival' => $festival]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <h2 class="h3">Explorer par style</h2>
        <div class="chips">
            <?php foreach ($genres as $g): ?>
                <a href="<?= url('/festivals', ['genre' => $g]) ?>" class="chip"><?= e($g) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Covoiturage</span>
                <h2>Prochains départs</h2>
            </div>
            <a href="<?= url('/covoiturages') ?>" class="link-arrow">Tous les trajets <svg class="icon"><use href="#i-arrow"/></svg></a>
        </div>
        <div class="grid grid-2">
            <?php foreach ($rides as $ride): ?>
                <?= partial('ride-card', ['ride' => $ride]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Logements</span>
                <h2>Dormir près des scènes</h2>
            </div>
            <a href="<?= url('/logements') ?>" class="link-arrow">Tous les logements <svg class="icon"><use href="#i-arrow"/></svg></a>
        </div>
        <div class="grid grid-3">
            <?php foreach ($housings as $housing): ?>
                <?= partial('housing-card', ['housing' => $housing]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-dark">
    <div class="container">
        <h2 class="center">Comment ça marche ?</h2>
        <ol class="steps">
            <li>
                <span class="step-num">1</span>
                <h3>Choisissez votre festival</h3>
                <p>Parcourez la page découverte, filtrez par style, région ou mois.</p>
            </li>
            <li>
                <span class="step-num">2</span>
                <h3>Réservez votre place</h3>
                <p>Rejoignez un covoiturage depuis votre ville et partagez les frais d'essence.</p>
            </li>
            <li>
                <span class="step-num">3</span>
                <h3>Posez votre sac</h3>
                <p>Un jardin pour la tente, une chambre ou un van : la communauté vous héberge.</p>
            </li>
        </ol>
        <div class="stats">
            <div><strong><?= (int) $stats['festivals'] ?></strong><span>festivals</span></div>
            <div><strong><?= (int) $stats['rides'] ?></strong><span>trajets</span></div>
            <div><strong><?= (int) $stats['housings'] ?></strong><span>logements</span></div>
            <div><strong><?= (int) $stats['members'] ?></strong><span>membres</span></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-band">
            <div>
                <h2>Une place libre dans la voiture&nbsp;? Un coin de jardin&nbsp;?</h2>
                <p>Proposez-les à la communauté et partagez les frais.</p>
            </div>
            <div class="cta-actions">
                <a href="<?= url('/covoiturages/nouveau') ?>" class="btn btn-light"><svg class="icon"><use href="#i-car"/></svg>Proposer un trajet</a>
                <a href="<?= url('/logements/nouveau') ?>" class="btn btn-outline-light"><svg class="icon"><use href="#i-home"/></svg>Proposer un logement</a>
            </div>
        </div>
    </div>
</section>
