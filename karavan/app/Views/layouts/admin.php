<?php
/**
 * Layout de l'espace d'administration.
 * @var string $content
 * @var string $title
 * @var array $currentUser
 */
$flashes = pull_flashes();
$nav = [
    '/admin'              => ['Tableau de bord', 'i-grid'],
    '/admin/festivals'    => ['Festivals', 'i-ticket'],
    '/admin/covoiturages' => ['Covoiturages', 'i-car'],
    '/admin/logements'    => ['Logements', 'i-home'],
    '/admin/membres'      => ['Membres', 'i-users'],
];
$path = '/' . trim(substr(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', strlen(base_path())), '/');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= e($title ?? 'Admin Karavan') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%2317132b'/><circle cx='16' cy='17' r='7' fill='%23ff5a36'/></svg>">
</head>
<body class="admin-body">
<?= partial('icons') ?>

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <a href="<?= url('/admin') ?>" class="logo logo-light">
                <span class="logo-mark" aria-hidden="true"><svg><use href="#i-van"/></svg></span>karavan
            </a>
            <span class="admin-badge">Admin</span>
        </div>

        <nav class="admin-nav" aria-label="Administration">
            <?php foreach ($nav as $href => [$label, $icon]):
                $active = $href === '/admin' ? $path === '/admin' : str_starts_with($path, $href); ?>
                <a href="<?= url($href) ?>" class="<?= $active ? 'is-active' : '' ?>">
                    <svg class="icon"><use href="#<?= $icon ?>"/></svg><?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="admin-me">
            <a href="<?= url('/') ?>" class="admin-site-link"><svg class="icon"><use href="#i-external"/></svg>Voir le site</a>
            <div class="admin-user">
                <span class="avatar avatar-sm"><?= e(initials($currentUser['name'])) ?></span>
                <span><?= e($currentUser['name']) ?></span>
                <form method="post" action="<?= url('/deconnexion') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" title="Déconnexion" aria-label="Déconnexion"><svg class="icon"><use href="#i-logout"/></svg></button>
                </form>
            </div>
        </div>
    </aside>

    <main class="admin-main" id="main">
        <?php if ($flashes): ?>
            <div class="flashes" role="status">
                <?php foreach ($flashes as $f): ?>
                    <div class="flash flash-<?= e($f['type']) ?>">
                        <span><?= e($f['message']) ?></span>
                        <button type="button" class="flash-close" aria-label="Fermer">×</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>
</div>

<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
