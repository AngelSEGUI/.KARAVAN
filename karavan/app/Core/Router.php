<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Routeur minimaliste : associe (méthode HTTP + motif d'URL) à [Contrôleur, méthode].
 * Les segments {param} sont transmis à la méthode du contrôleur dans l'ordre.
 */
final class Router
{
    private array $routes = [];

    public function get(string $path, array $action): void
    {
        $this->add('GET', $path, $action);
    }

    public function post(string $path, array $action): void
    {
        $this->add('POST', $path, $action);
    }

    private function add(string $method, string $path, array $action): void
    {
        $pattern = preg_replace('#\{([a-z_]+)\}#', '(?P<$1>[^/]+)', rtrim($path, '/') ?: '/');
        $this->routes[$method]['#^' . $pattern . '$#'] = $action;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Retire le sous-dossier éventuel (ex. http://localhost/karavan/public)
        // On accepte aussi l'URL sans "/public" (projet ouvert depuis sa racine, cf. .htaccess racine)
        $base = base_path();
        foreach (array_unique([$base, preg_replace('#/public$#', '', $base)]) as $prefix) {
            if ($prefix !== '' && ($path === $prefix || str_starts_with($path, $prefix . '/'))) {
                $path = substr($path, strlen($prefix));
                break;
            }
        }
        $path = '/' . trim($path, '/');
        if ($path === '/index.php' || str_ends_with($path, '/index.php')) {
            $path = '/';
        }

        foreach ($this->routes[$method] ?? [] as $pattern => [$controller, $action]) {
            if (preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                (new $controller())->$action(...array_values($params));
                return;
            }
        }

        (new Controller())->notFound();
    }
}
