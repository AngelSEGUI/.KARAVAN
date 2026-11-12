<?php
/**
 * Bouton de suppression avec confirmation.
 * @var string $action  URL de suppression
 * @var string $confirm Message de confirmation
 * @var string|null $label
 */
?>
<form method="post" action="<?= url($action) ?>" data-confirm="<?= e($confirm) ?>" class="inline-form">
    <?= csrf_field() ?>
    <button type="submit" class="icon-btn icon-btn-danger" title="Supprimer" aria-label="Supprimer">
        <svg class="icon"><use href="#i-trash"/></svg><?php if (!empty($label)): ?><span><?= e($label) ?></span><?php endif; ?>
    </button>
</form>
