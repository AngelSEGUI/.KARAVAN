<?php
/**
 * @var array $housings
 * @var array $filters
 * @var array $festivals
 * @var array $types
 */
?>
<section class="page-head">
    <div class="container page-head-row">
        <div>
            <span class="eyebrow">Logements</span>
            <h1>Dormir près des scènes</h1>
            <p class="lead">Un jardin pour la tente, une chambre, un van : la communauté vous accueille.</p>
        </div>
        <a href="<?= url('/logements/nouveau') ?>" class="btn btn-primary"><svg class="icon"><use href="#i-plus"/></svg>Proposer un logement</a>
    </div>
</section>

<div class="container">
    <form class="filters" method="get" action="<?= url('/logements') ?>" data-autosubmit>
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
            <span class="sr-only">Type</span>
            <select name="type">
                <option value="">Tous les types</option>
                <?php foreach ($types as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $filters['type'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field">
            <span class="sr-only">Voyageurs</span>
            <select name="guests">
                <option value="">Voyageurs</option>
                <?php foreach ([1, 2, 3, 4, 6] as $n): ?>
                    <option value="<?= $n ?>" <?= (int) $filters['guests'] === $n ? 'selected' : '' ?>><?= $n ?>+ pers.</option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field">
            <span class="sr-only">Budget</span>
            <select name="max_price">
                <option value="">Tous budgets</option>
                <?php foreach ([20, 50, 100] as $p): ?>
                    <option value="<?= $p ?>" <?= (int) $filters['max_price'] === $p ? 'selected' : '' ?>>≤ <?= $p ?> € / nuit</option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field">
            <span class="sr-only">Trier</span>
            <select name="sort">
                <option value="distance" <?= $filters['sort'] !== 'price' ? 'selected' : '' ?>>Plus proches</option>
                <option value="price" <?= $filters['sort'] === 'price' ? 'selected' : '' ?>>Moins chers</option>
            </select>
        </label>
        <button type="submit" class="btn btn-primary js-hide">Rechercher</button>
    </form>

    <p class="results-count"><?= plural(count($housings), 'logement') ?></p>

    <?php if ($housings): ?>
        <div class="grid grid-3">
            <?php foreach ($housings as $housing): ?>
                <?= partial('housing-card', ['housing' => $housing]) ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty">
            <svg class="icon"><use href="#i-tent"/></svg>
            <h2>Aucun logement ne correspond</h2>
            <p>Élargissez vos critères ou proposez votre propre logement.</p>
            <a href="<?= url('/logements') ?>" class="btn btn-primary">Voir tous les logements</a>
        </div>
    <?php endif; ?>
</div>
