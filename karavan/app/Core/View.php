<?php
declare(strict_types=1);

namespace App\Core;

/** Moteur de vues PHP natif : app/Views/{view}.php inséré dans app/Views/layouts/{layout}.php. */
final class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'main'): string
    {
        $content = self::capture(APP_PATH . '/Views/' . $view . '.php', $data);

        if ($layout === null) {
            return $content;
        }

        return self::capture(APP_PATH . '/Views/layouts/' . $layout . '.php', $data + ['content' => $content]);
    }

    /** Inclut un fragment réutilisable (app/Views/partials/...). */
    public static function partial(string $name, array $data = []): string
    {
        return self::capture(APP_PATH . '/Views/partials/' . $name . '.php', $data);
    }

    private static function capture(string $__file, array $__data): string
    {
        if (!is_file($__file)) {
            throw new \RuntimeException("Vue introuvable : {$__file}");
        }
        extract($__data, EXTR_SKIP);
        ob_start();
        require $__file;
        return (string) ob_get_clean();
    }
}
