<?php /** @var array $festival */ ?>
<article class="festival-card">
    <a href="<?= url('/festivals/' . $festival['slug']) ?>" class="card-link" aria-label="<?= e($festival['name']) ?>"></a>
    <?= partial('festival-cover', ['festival' => $festival]) ?>
    <div class="festival-card-body">
        <div class="date-chip">
            <strong><?= date('j', strtotime($festival['start_date'])) ?></strong>
            <span><?= e(month_short($festival['start_date'])) ?></span>
        </div>
        <div>
            <h3><?= e($festival['name']) ?></h3>
            <p class="muted"><svg class="icon icon-sm"><use href="#i-pin"/></svg><?= e($festival['city']) ?> · <?= e(date_range($festival['start_date'], $festival['end_date'])) ?></p>
        </div>
    </div>
    <div class="festival-card-foot">
        <div class="tags">
            <?php foreach (array_slice(explode(',', $festival['genres']), 0, 2) as $g): ?>
                <span class="tag"><?= e($g) ?></span>
            <?php endforeach; ?>
        </div>
        <div class="offer-counts">
            <span title="Covoiturages"><svg class="icon icon-sm"><use href="#i-car"/></svg><?= (int) ($festival['rides_count'] ?? 0) ?></span>
            <span title="Logements"><svg class="icon icon-sm"><use href="#i-home"/></svg><?= (int) ($festival['housings_count'] ?? 0) ?></span>
        </div>
    </div>
</article>
