<?php

/*
|--------------------------------------------------------------------------
| Petites fonctions d'affichage disponibles partout (vues comprises)
|--------------------------------------------------------------------------
| Chargées automatiquement par Composer (voir "autoload.files" dans composer.json).
*/

if (! function_exists('euros')) {
    /** 32 -> "32 €", 12.5 -> "12,50 €" (sans dépendre de l'extension intl). */
    function euros(float|int|string|null $amount): string
    {
        $amount = (float) $amount;
        $decimals = fmod($amount, 1.0) === 0.0 ? 0 : 2;

        return number_format($amount, $decimals, ',', "\u{202F}") . "\u{00A0}€";
    }
}

if (! function_exists('km')) {
    /** 2.5 -> "2,5 km" */
    function km(float|int|string $distance): string
    {
        return str_replace('.', ',', (string) (float) $distance) . "\u{00A0}km";
    }
}

if (! function_exists('plural')) {
    /** plural(3, 'place') -> "3 places" ; plural(1, 'nuit') -> "1 nuit" */
    function plural(int $count, string $singular, ?string $plural = null): string
    {
        return $count . "\u{00A0}" . ($count > 1 ? ($plural ?? $singular . 's') : $singular);
    }
}

if (! function_exists('active_theme')) {
    /** Thème graphique en cours (voir config/karavan.php et docs/THEMES.md). */
    function active_theme(): string
    {
        return app(\App\Support\Theme::class)->current();
    }
}
