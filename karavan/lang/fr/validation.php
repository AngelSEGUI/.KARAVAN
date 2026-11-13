<?php

/*
|--------------------------------------------------------------------------
| Messages de validation en français
|--------------------------------------------------------------------------
| Seules les règles utilisées par Karavan sont traduites ; les autres
| retombent sur l'anglais (APP_FALLBACK_LOCALE=en).
| :attribute est remplacé par le libellé défini dans 'attributes' en bas du fichier.
*/

return [
    'accepted' => ':Attribute doit être accepté.',
    'after' => ':Attribute doit être postérieure à :date.',
    'after_or_equal' => ':Attribute doit être identique ou postérieure à :date.',
    'alpha_dash' => ':Attribute ne peut contenir que des lettres, chiffres et tirets.',
    'array' => ':Attribute doit être une liste.',
    'between' => [
        'numeric' => ':Attribute doit être compris entre :min et :max.',
        'string' => ':Attribute doit contenir entre :min et :max caractères.',
    ],
    'boolean' => ':Attribute doit être vrai ou faux.',
    'confirmed' => 'La confirmation ne correspond pas.',
    'current_password' => 'Le mot de passe est incorrect.',
    'date' => ":Attribute n'est pas une date valide.",
    'email' => ':Attribute doit être une adresse email valide.',
    'enum' => ":Attribute n'est pas valide.",
    'exists' => ":Attribute n'existe pas.",
    'in' => ":Attribute n'est pas valide.",
    'integer' => ':Attribute doit être un nombre entier.',
    'lowercase' => ':Attribute doit être en minuscules.',
    'max' => [
        'numeric' => ':Attribute ne peut pas dépasser :max.',
        'string' => ':Attribute ne peut pas dépasser :max caractères.',
    ],
    'min' => [
        'numeric' => ':Attribute doit être au moins :min.',
        'string' => ':Attribute doit contenir au moins :min caractères.',
    ],
    'numeric' => ':Attribute doit être un nombre.',
    'required' => ':Attribute est obligatoire.',
    'string' => ':Attribute doit être un texte.',
    'unique' => ':Attribute est déjà utilisé.',

    'custom' => [
        'email' => [
            'unique' => 'Un compte existe déjà avec cette adresse email.',
        ],
    ],

    'attributes' => [
        'name' => 'le nom',
        'email' => "l'email",
        'password' => 'le mot de passe',
        'current_password' => 'le mot de passe actuel',
        'city' => 'la ville',
        'bio' => 'la présentation',

        'festival_id' => 'le festival',
        'departure_city' => 'la ville de départ',
        'meeting_point' => 'le point de rendez-vous',
        'departs_at' => 'la date de départ',
        'returns_at' => 'la date de retour',
        'seats' => 'le nombre de places',
        'price' => 'le prix',
        'vehicle' => 'le véhicule',
        'notes' => 'le message',
        'message' => 'le message',

        'title' => 'le titre',
        'type' => 'le type de logement',
        'distance_km' => 'la distance',
        'capacity' => 'la capacité',
        'price_per_night' => 'le prix par nuit',
        'description' => 'la description',
        'amenities' => 'les équipements',
        'guests' => 'le nombre de voyageurs',
        'nights' => 'le nombre de nuits',

        'slug' => "l'adresse",
        'region' => 'la région',
        'venue' => 'le lieu',
        'starts_on' => 'la date de début',
        'ends_on' => 'la date de fin',
        'genres' => 'les styles',
        'lineup' => 'la programmation',
        'tagline' => "l'accroche",
        'attendance' => "l'affluence",
        'price_from' => 'le prix du billet',
        'hue' => 'la couleur',
    ],
];
