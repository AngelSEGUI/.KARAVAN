<?php /** @var string $title @var string $message */ ?>
<div class="container">
    <div class="empty empty-page">
        <svg class="icon"><use href="#i-compass"/></svg>
        <h1><?= e($title) ?></h1>
        <p><?= e($message) ?></p>
        <a href="<?= url('/') ?>" class="btn btn-primary">Retour à l'accueil</a>
    </div>
</div>
