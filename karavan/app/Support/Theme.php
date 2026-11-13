<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Choix du thème graphique.
 *
 * Ordre de priorité :
 *   1. ?theme=nom dans l'URL (si le sélecteur est autorisé) — mémorisé en session ;
 *   2. thème mémorisé en session (si le sélecteur est autorisé) ;
 *   3. KARAVAN_THEME dans le fichier .env.
 */
class Theme
{
    public function __construct(private Request $request)
    {
    }

    /** @return array<string, string> */
    public function available(): array
    {
        return config('karavan.themes');
    }

    public function exists(?string $name): bool
    {
        return $name !== null
            && array_key_exists($name, $this->available())
            && is_file(public_path("css/themes/{$name}.css"));
    }

    public function switcherEnabled(): bool
    {
        return config('karavan.theme_switcher') && ! app()->isProduction();
    }

    public function default(): string
    {
        $theme = config('karavan.theme');

        return $this->exists($theme) ? $theme : 'festival';
    }

    public function current(): string
    {
        if ($this->switcherEnabled() && $this->request->hasSession()) {
            $asked = $this->request->query('theme');
            if ($this->exists($asked)) {
                $this->request->session()->put('theme', $asked);
            }
            $stored = $this->request->session()->get('theme');
            if ($this->exists($stored)) {
                return $stored;
            }
        }

        return $this->default();
    }
}
