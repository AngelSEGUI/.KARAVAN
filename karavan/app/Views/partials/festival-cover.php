<?php
/**
 * Couverture d'affiche générée en CSS à partir de la teinte du festival (aucune image requise).
 * Pour utiliser une vraie photo, ajouter une colonne image et un <img> ici.
 * @var array $festival
 * @var string $size  'card' | 'hero'
 */
$size ??= 'card';
$hue = (int) ($festival['hue'] ?? 20);
?>
<div class="cover cover-<?= e($size) ?>" style="--h: <?= $hue ?>" aria-hidden="true">
    <span class="cover-sun"></span>
    <span class="cover-hills"></span>
    <span class="cover-word"><?= e($festival['name'] ?? $festival['festival_name'] ?? '') ?></span>
</div>
