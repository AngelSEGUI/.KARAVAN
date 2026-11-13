<?php

/*
|--------------------------------------------------------------------------
| Personnalisation des pages de festival
|--------------------------------------------------------------------------
| Palettes et polices proposées aux organisateurs dans l'onglet « Apparence ».
| Ajoutez-en librement : elles apparaissent automatiquement dans l'éditeur.
|
| Palette : primary   = couleur principale (boutons, titres, créneaux)
|           secondary = couleur d'accent (dégradés, détails)
|           mode      = light (fond clair) ou dark (fond sombre)
| Police  : nom exact de la famille sur Google Fonts + police de secours.
*/

return [

    'palettes' => [
        'soleil'   => ['label' => 'Coucher de soleil', 'primary' => '#ff5a36', 'secondary' => '#ff3e88', 'mode' => 'light'],
        'neon'     => ['label' => 'Néon nocturne',     'primary' => '#ff4fd8', 'secondary' => '#22d3ff', 'mode' => 'dark'],
        'ocean'    => ['label' => 'Océan',             'primary' => '#0e7c9b', 'secondary' => '#f2b134', 'mode' => 'light'],
        'foret'    => ['label' => 'Forêt',             'primary' => '#2f7d4f', 'secondary' => '#d9a441', 'mode' => 'light'],
        'metal'    => ['label' => 'Métal',             'primary' => '#e3242b', 'secondary' => '#f5c400', 'mode' => 'dark'],
        'retro'    => ['label' => 'Rétro 70',          'primary' => '#c8553d', 'secondary' => '#2d6a6a', 'mode' => 'light'],
        'pastel'   => ['label' => 'Pastel',            'primary' => '#8b6cf6', 'secondary' => '#f59fb5', 'mode' => 'light'],
        'nuit'     => ['label' => 'Nuit étoilée',      'primary' => '#7aa2ff', 'secondary' => '#ffd166', 'mode' => 'dark'],
    ],

    // Fonds et textes selon le mode (le reste découle de la palette)
    'modes' => [
        'light' => ['bg' => '#faf7f2', 'surface' => '#ffffff', 'text' => '#17132b', 'muted' => '#6d6884', 'line' => '#e8e2d6'],
        'dark'  => ['bg' => '#0f0d1a', 'surface' => '#1a1729', 'text' => '#f3f1fb', 'muted' => '#a6a1c2', 'line' => '#2e2a45'],
    ],

    'fonts' => [
        'heading' => [
            'Bricolage Grotesque' => 'sans-serif',
            'Unbounded'           => 'sans-serif',
            'Bebas Neue'          => 'sans-serif',
            'Archivo Black'       => 'sans-serif',
            'Syne'                => 'sans-serif',
            'Righteous'           => 'sans-serif',
            'Playfair Display'    => 'serif',
            'Abril Fatface'       => 'serif',
            'Space Grotesk'       => 'sans-serif',
            'Oswald'              => 'sans-serif',
        ],
        'body' => [
            'DM Sans'        => 'sans-serif',
            'Inter'          => 'sans-serif',
            'Work Sans'      => 'sans-serif',
            'Nunito'         => 'sans-serif',
            'Karla'          => 'sans-serif',
            'IBM Plex Sans'  => 'sans-serif',
            'Lora'           => 'serif',
            'Source Serif 4' => 'serif',
        ],
    ],

    'defaults' => [
        'palette' => 'soleil',
        'heading_font' => 'Bricolage Grotesque',
        'body_font' => 'DM Sans',
    ],

    // Limites des images (en kilo-octets) et du carrousel
    'max_image_kb' => 5120,
    'max_photos' => 12,
    'max_sections' => 10,
    'max_performances' => 150,
];
