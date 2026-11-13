<?php

/*
|--------------------------------------------------------------------------
| Réglages propres à Karavan
|--------------------------------------------------------------------------
| Les valeurs viennent du fichier .env : on peut les changer sans toucher au code.
*/

return [

    /*
     | Thème graphique actif. Chaque thème est un fichier public/css/themes/{nom}.css
     | qui ne contient que des variables CSS (couleurs, polices, arrondis...).
     | Voir docs/THEMES.md pour en créer un.
     */
    'theme' => env('KARAVAN_THEME', 'festival'),

    /*
     | Thèmes proposés dans le sélecteur de la page /guide-styles.
     | Clé = nom du fichier, valeur = libellé affiché.
     */
    'themes' => [
        'festival' => 'Festival — chaleureux, affiches rétro',
        'nuit'     => 'Nuit — sombre, néons',
        'epure'    => 'Épuré — minimal, sobre',
    ],

    /*
     | Autorise l'essai d'un autre thème depuis le navigateur (?theme=nuit ou /guide-styles).
     | Le choix est gardé en session, uniquement pour le visiteur qui l'a fait.
     | Toujours désactivé en production.
     */
    'theme_switcher' => (bool) env('KARAVAN_THEME_SWITCHER', true),

    /* Nombre d'éléments par page dans les listes. */
    'per_page' => 12,

    /* Limites métier. */
    'max_ride_seats'       => 8,
    'max_housing_capacity' => 20,
    'max_nights'           => 14,
];
