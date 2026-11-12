<?php
declare(strict_types=1);

/**
 * Initialisation : autoload PSR-4 (namespace App\ -> dossier app/), gestion des erreurs,
 * vérification de l'environnement, base de données, session, helpers.
 */

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $file = APP_PATH . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// Remplace mbstring si l'extension n'est pas installée
require APP_PATH . '/polyfills.php';

App\Core\Config::load(ROOT_PATH . '/config/config.php');

// Toute erreur affiche une page complète (détaillée en mode debug) au lieu d'une page cassée
App\Core\ErrorHandler::register((bool) App\Core\Config::get('app.debug'));

// Extensions indispensables : message clair plutôt qu'une erreur obscure plus loin
if (App\Core\Config::get('db.driver') === 'sqlite' && !extension_loaded('pdo_sqlite')) {
    throw new RuntimeException("Extension PHP manquante : pdo_sqlite");
}
if (App\Core\Config::get('db.driver') === 'mysql' && !extension_loaded('pdo_mysql')) {
    throw new RuntimeException("Extension PHP manquante : pdo_mysql (installez php-mysql)");
}

// Premier lancement en SQLite : création automatique de la base de démo
if (App\Core\Config::get('db.driver') === 'sqlite' && !is_file(App\Core\Config::get('db.path'))) {
    require ROOT_PATH . '/database/seed.php';
}

App\Core\Migrator::run();

date_default_timezone_set('Europe/Paris');

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

require APP_PATH . '/helpers.php';
