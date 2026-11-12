<?php
/**
 * Layout principal.
 * @var string $content  Contenu de la vue
 * @var string $title
 * @var array|null $currentUser
 */
$flashes = pull_flashes();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Karavan') ?></title>
    <meta name="description" content="Karavan : découvrez les festivals, trouvez un covoiturage et un logement grâce à la communauté.">
    <meta name="theme-color" content="#17132b">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%23ff5a36'/><circle cx='16' cy='17' r='7' fill='%23ffd23f'/></svg>">
</head>
<body>
<?= partial('icons') ?>

<header class="site-header">
    <div class="container header-inner">
        <a href="<?= url('/') ?>" class="logo" aria-label="Karavan, accueil">
            <span class="logo-mark" aria-hidden="true"><svg><use href="#i-van"/></svg></span>
            karavan
        </a>

        <nav class="main-nav" aria-label="Navigation principale">
            <a href="<?= url('/festivals') ?>" class="<?= is_active('/festivals') ? 'is-active' : '' ?>">Festivals</a>
            <a href="<?= url('/covoiturages') ?>" class="<?= is_active('/covoiturages') ? 'is-active' : '' ?>">Covoiturage</a>
            <a href="<?= url('/logements') ?>" class="<?= is_active('/logements') ? 'is-active' : '' ?>">Logements</a>
        </nav>

        <div class="header-actions">
            <?php if ($currentUser): ?>
                <details class="dropdown">
                    <summary class="btn btn-ghost btn-sm">
                        <svg class="icon"><use href="#i-plus"/></svg><span class="hide-sm">Proposer</span>
                    </summary>
                    <div class="dropdown-menu">
                        <a href="<?= url('/covoiturages/nouveau') ?>"><svg class="icon"><use href="#i-car"/></svg>Un trajet</a>
                        <a href="<?= url('/logements/nouveau') ?>"><svg class="icon"><use href="#i-home"/></svg>Un logement</a>
                    </div>
                </details>
                <details class="dropdown">
                    <summary class="avatar" title="<?= e($currentUser['name']) ?>"><?= e(initials($currentUser['name'])) ?></summary>
                    <div class="dropdown-menu dropdown-right">
                        <span class="dropdown-label"><?= e($currentUser['name']) ?></span>
                        <a href="<?= url('/mon-espace') ?>"><svg class="icon"><use href="#i-user"/></svg>Mon espace</a>
                        <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                            <a href="<?= url('/admin') ?>"><svg class="icon"><use href="#i-shield"/></svg>Administration</a>
                        <?php endif; ?>
                        <form method="post" action="<?= url('/deconnexion') ?>">
                            <?= csrf_field() ?>
                            <button type="submit"><svg class="icon"><use href="#i-logout"/></svg>Déconnexion</button>
                        </form>
                    </div>
                </details>
            <?php else: ?>
                <a href="<?= url('/connexion') ?>" class="btn btn-ghost btn-sm">Connexion</a>
                <a href="<?= url('/inscription') ?>" class="btn btn-primary btn-sm hide-sm">Rejoindre</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if ($flashes): ?>
    <div class="flashes container" role="status">
        <?php foreach ($flashes as $f): ?>
            <div class="flash flash-<?= e($f['type']) ?>">
                <span><?= e($f['message']) ?></span>
                <button type="button" class="flash-close" aria-label="Fermer">×</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<main id="main">
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <div>
            <a href="<?= url('/') ?>" class="logo logo-light">
                <span class="logo-mark" aria-hidden="true"><svg><use href="#i-van"/></svg></span>karavan
            </a>
            <p>La route des festivals se fait mieux à plusieurs. Partagez la voiture, la tente et les souvenirs.</p>
        </div>
        <div>
            <h4>Explorer</h4>
            <a href="<?= url('/festivals') ?>">Tous les festivals</a>
            <a href="<?= url('/covoiturages') ?>">Covoiturages</a>
            <a href="<?= url('/logements') ?>">Logements</a>
        </div>
        <div>
            <h4>Participer</h4>
            <a href="<?= url('/covoiturages/nouveau') ?>">Proposer un trajet</a>
            <a href="<?= url('/logements/nouveau') ?>">Proposer un logement</a>
            <a href="<?= url('/inscription') ?>">Rejoindre la communauté</a>
        </div>
    </div>
    <div class="container footer-bottom">© <?= date('Y') ?> Karavan · Projet communautaire</div>
</footer>

<!-- Barre d'onglets mobile -->
<nav class="tabbar" aria-label="Navigation mobile">
    <a href="<?= url('/') ?>" class="<?= is_active('/') ? 'is-active' : '' ?>"><svg class="icon"><use href="#i-compass"/></svg>Découvrir</a>
    <a href="<?= url('/festivals') ?>" class="<?= is_active('/festivals') ? 'is-active' : '' ?>"><svg class="icon"><use href="#i-ticket"/></svg>Festivals</a>
    <a href="<?= url('/covoiturages') ?>" class="<?= is_active('/covoiturages') ? 'is-active' : '' ?>"><svg class="icon"><use href="#i-car"/></svg>Trajets</a>
    <a href="<?= url('/logements') ?>" class="<?= is_active('/logements') ? 'is-active' : '' ?>"><svg class="icon"><use href="#i-home"/></svg>Logements</a>
    <a href="<?= url($currentUser ? '/mon-espace' : '/connexion') ?>" class="<?= is_active('/mon-espace') || is_active('/connexion') ? 'is-active' : '' ?>"><svg class="icon"><use href="#i-user"/></svg>Profil</a>
</nav>

<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
