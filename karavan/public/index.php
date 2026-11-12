<?php
declare(strict_types=1);

/**
 * Karavan — Front controller.
 * Toutes les requêtes HTTP passent par ce fichier.
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

// Serveur PHP intégré (php -S) : on sert nous-mêmes les fichiers statiques de public/.
// Ainsi CSS/JS/images fonctionnent même si le serveur est lancé sans "-t public".
if (PHP_SAPI === 'cli-server') {
    $path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
    $file = realpath(__DIR__ . $path);

    if ($file !== false && is_file($file) && str_starts_with($file, __DIR__ . DIRECTORY_SEPARATOR)
        && pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
        $types = [
            'css' => 'text/css', 'js' => 'application/javascript', 'json' => 'application/json',
            'svg' => 'image/svg+xml', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif', 'webp' => 'image/webp', 'ico' => 'image/x-icon',
            'woff' => 'font/woff', 'woff2' => 'font/woff2', 'txt' => 'text/plain',
        ];
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream') . (in_array($ext, ['css', 'js', 'svg', 'txt', 'json'], true) ? '; charset=UTF-8' : ''));
        header('Content-Length: ' . filesize($file));
        readfile($file);
        return true;
    }
}

require APP_PATH . '/bootstrap.php';

$router = new App\Core\Router();
require ROOT_PATH . '/config/routes.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
