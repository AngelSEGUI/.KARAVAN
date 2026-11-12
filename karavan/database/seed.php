<?php
declare(strict_types=1);

/**
 * (Ré)initialise la base avec le schéma et des données de démonstration.
 * Usage : php database/seed.php
 * Les dates et programmations sont FICTIVES (données de démo).
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
    define('APP_PATH', ROOT_PATH . '/app');
    spl_autoload_register(function (string $class): void {
        $file = APP_PATH . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (str_starts_with($class, 'App\\') && is_file($file)) {
            require $file;
        }
    });
    App\Core\Config::load(ROOT_PATH . '/config/config.php');
}

$pdo = App\Core\Database::connection();
$pdo->exec(file_get_contents(__DIR__ . '/schema.sql'));

$insert = function (string $table, array $rows) use ($pdo): void {
    $cols = array_keys($rows[0]);
    $stmt = $pdo->prepare(sprintf(
        'INSERT INTO %s (%s) VALUES (%s)',
        $table,
        implode(',', $cols),
        implode(',', array_fill(0, count($cols), '?'))
    ));
    foreach ($rows as $row) {
        $stmt->execute(array_values($row));
    }
};

$pwd = password_hash('karavan', PASSWORD_DEFAULT);

$insert('users', [
    ['name' => 'Léa Martin',     'email' => 'lea@karavan.test',    'password' => $pwd, 'city' => 'Paris',    'bio' => 'Fan de rock et de longues routes. Playlist garantie.'],
    ['name' => 'Malik Benali',    'email' => 'malik@karavan.test',  'password' => $pwd, 'city' => 'Lyon',     'bio' => 'Électro, techno et van aménagé.'],
    ['name' => 'Chloé Dubois',    'email' => 'chloe@karavan.test',  'password' => $pwd, 'city' => 'Nantes',   'bio' => 'Je loue ma chambre d\'amis pendant la saison des festivals.'],
    ['name' => 'Thomas Leroy',    'email' => 'thomas@karavan.test', 'password' => $pwd, 'city' => 'Lille',    'bio' => 'Conducteur prudent, 8 festivals par été.'],
    ['name' => 'Inès Moreau',     'email' => 'ines@karavan.test',   'password' => $pwd, 'city' => 'Bordeaux', 'bio' => 'Camping-car familial, place pour deux tentes.'],
    ['name' => 'Hugo Petit',      'email' => 'hugo@karavan.test',   'password' => $pwd, 'city' => 'Rennes',   'bio' => 'Breton pur beurre, je connais toutes les routes vers Carhaix.'],
]);

// Compte administrateur (id 7, inséré après les membres pour garder leurs id)
$insert('users', [
    ['name' => 'Admin Karavan', 'email' => 'admin@karavan.test', 'password' => $pwd, 'city' => 'Paris', 'bio' => 'Équipe Karavan.', 'role' => 'admin'],
]);

$insert('festivals', [
    ['slug' => 'hellfest', 'name' => 'Hellfest', 'city' => 'Clisson', 'region' => 'Pays de la Loire', 'venue' => 'Val de Moine',
     'start_date' => '2027-06-17', 'end_date' => '2027-06-20', 'genres' => 'Metal,Rock,Punk',
     'tagline' => 'Le grand pèlerinage metal du vignoble nantais.',
     'description' => "Quatre jours de riffs saturés sur six scènes, une cathédrale de métal et un public venu de toute l'Europe. Le camping se remplit dès le mercredi : partez tôt.",
     'lineup' => 'Iron Maiden,Gojira,Slipknot,Ghost,Bring Me The Horizon,Mass Hysteria', 'attendance' => 240000, 'price_from' => 99, 'hue' => 0, 'featured' => 1],
    ['slug' => 'vieilles-charrues', 'name' => 'Les Vieilles Charrues', 'city' => 'Carhaix', 'region' => 'Bretagne', 'venue' => 'Site de Kerampuilh',
     'start_date' => '2027-07-15', 'end_date' => '2027-07-18', 'genres' => 'Pop,Rock,Rap,Chanson',
     'tagline' => "Le plus grand festival de France, au cœur du Centre-Bretagne.",
     'description' => "Une affiche éclectique, des crêpes à toute heure et une ambiance familiale unique. Les navettes depuis Rennes et Brest sont prises d'assaut : le covoiturage reste la meilleure option.",
     'lineup' => 'Orelsan,Clara Luciani,Muse,Juliette Armanet,Bigflo & Oli,Tryo', 'attendance' => 280000, 'price_from' => 59, 'hue' => 205, 'featured' => 1],
    ['slug' => 'solidays', 'name' => 'Solidays', 'city' => 'Paris', 'region' => 'Île-de-France', 'venue' => 'Hippodrome de Longchamp',
     'start_date' => '2027-06-25', 'end_date' => '2027-06-27', 'genres' => 'Électro,Rap,Pop',
     'tagline' => 'Trois jours de fête solidaire pour la lutte contre le sida.',
     'description' => "Un festival militant et joyeux en plein bois de Boulogne. Idéal pour un premier festival, accessible en transports.",
     'lineup' => 'Damso,Polo & Pan,Aya Nakamura,Fakear,Hamza,L\'Impératrice', 'attendance' => 230000, 'price_from' => 49, 'hue' => 330, 'featured' => 0],
    ['slug' => 'eurockeennes', 'name' => 'Les Eurockéennes', 'city' => 'Belfort', 'region' => 'Bourgogne-Franche-Comté', 'venue' => "Presqu'île du Malsaucy",
     'start_date' => '2027-07-01', 'end_date' => '2027-07-04', 'genres' => 'Rock,Électro,Rap',
     'tagline' => 'Une presqu\'île, un lac et quatre scènes.',
     'description' => "Les pieds dans l'eau et la tête dans les enceintes : un cadre naturel incroyable à la frontière suisse et allemande.",
     'lineup' => 'Arctic Monkeys,Justice,Nekfeu,Fontaines D.C.,Charlotte de Witte', 'attendance' => 130000, 'price_from' => 55, 'hue' => 160, 'featured' => 1],
    ['slug' => 'rock-en-seine', 'name' => 'Rock en Seine', 'city' => 'Saint-Cloud', 'region' => 'Île-de-France', 'venue' => 'Domaine national de Saint-Cloud',
     'start_date' => '2027-08-26', 'end_date' => '2027-08-29', 'genres' => 'Rock,Indie,Électro',
     'tagline' => 'La fin d\'été rock aux portes de Paris.',
     'description' => "Un parc classé, des pelouses à perte de vue et la meilleure scène indé du moment pour clôturer la saison.",
     'lineup' => 'The Strokes,Phoenix,Fever Ray,IDLES,Christine and the Queens', 'attendance' => 150000, 'price_from' => 69, 'hue' => 270, 'featured' => 0],
    ['slug' => 'garorock', 'name' => 'Garorock', 'city' => 'Marmande', 'region' => 'Nouvelle-Aquitaine', 'venue' => 'Plaine de la Filhole',
     'start_date' => '2027-06-30', 'end_date' => '2027-07-03', 'genres' => 'Rap,Électro,Rock',
     'tagline' => 'Le sud-ouest en fusion au bord de la Garonne.',
     'description' => "Une programmation rap et électro survoltée, un camping géant et la chaleur du Lot-et-Garonne. Prévoyez la crème solaire.",
     'lineup' => 'SCH,Gazo,Martin Garrix,Ninho,Vladimir Cauchemar', 'attendance' => 170000, 'price_from' => 65, 'hue' => 35, 'featured' => 1],
    ['slug' => 'main-square', 'name' => 'Main Square', 'city' => 'Arras', 'region' => 'Hauts-de-France', 'venue' => 'Citadelle d\'Arras',
     'start_date' => '2027-07-02', 'end_date' => '2027-07-04', 'genres' => 'Pop,Rock,Électro',
     'tagline' => 'Des têtes d\'affiche internationales dans une citadelle Vauban.',
     'description' => "Trois jours au pied des remparts classés à l'UNESCO, à une heure de Lille et de Bruxelles.",
     'lineup' => 'Coldplay,Stromae,Mika,The Kooks,Angèle', 'attendance' => 120000, 'price_from' => 75, 'hue' => 220, 'featured' => 0],
    ['slug' => 'francofolies', 'name' => 'Les Francofolies', 'city' => 'La Rochelle', 'region' => 'Nouvelle-Aquitaine', 'venue' => 'Esplanade Saint-Jean-d\'Acre',
     'start_date' => '2027-07-10', 'end_date' => '2027-07-14', 'genres' => 'Chanson,Pop,Rap',
     'tagline' => 'La chanson francophone face à l\'océan.',
     'description' => "Cinq jours de concerts entre le Vieux-Port et la grande scène, avec des dizaines de concerts gratuits en ville.",
     'lineup' => 'Zaho de Sagazan,Vianney,Pomme,Gaël Faye,Feu! Chatterton', 'attendance' => 150000, 'price_from' => 45, 'hue' => 190, 'featured' => 0],
    ['slug' => 'nuits-sonores', 'name' => 'Nuits Sonores', 'city' => 'Lyon', 'region' => 'Auvergne-Rhône-Alpes', 'venue' => 'Les Grandes Locos',
     'start_date' => '2027-05-26', 'end_date' => '2027-05-30', 'genres' => 'Électro,Techno,House',
     'tagline' => 'Cinq jours et cinq nuits de musiques électroniques.',
     'description' => "Des anciennes usines ferroviaires transformées en dancefloor géant, plus un circuit de jour dans toute la ville.",
     'lineup' => 'Laurent Garnier,Peggy Gou,Bicep,Folamour,Amelie Lens', 'attendance' => 140000, 'price_from' => 39, 'hue' => 290, 'featured' => 1],
    ['slug' => 'we-love-green', 'name' => 'We Love Green', 'city' => 'Paris', 'region' => 'Île-de-France', 'venue' => 'Bois de Vincennes',
     'start_date' => '2027-06-04', 'end_date' => '2027-06-06', 'genres' => 'Électro,Rap,Indie',
     'tagline' => 'Le festival éco-conçu du bois de Vincennes.',
     'description' => "Scènes alimentées en énergie renouvelable, food court gastronomique et conférences sur l'écologie entre deux concerts.",
     'lineup' => 'Tame Impala,PNL,Disclosure,Lomepal,Bonobo', 'attendance' => 110000, 'price_from' => 79, 'hue' => 110, 'featured' => 0],
    ['slug' => 'jazz-a-vienne', 'name' => 'Jazz à Vienne', 'city' => 'Vienne', 'region' => 'Auvergne-Rhône-Alpes', 'venue' => 'Théâtre antique',
     'start_date' => '2027-06-24', 'end_date' => '2027-07-10', 'genres' => 'Jazz,Soul,Blues',
     'tagline' => 'Le jazz sous les étoiles d\'un théâtre romain.',
     'description' => "Deux semaines de concerts dans un amphithéâtre de 7 000 places, sur les bords du Rhône.",
     'lineup' => 'Herbie Hancock,Gregory Porter,Ibrahim Maalouf,Cécile McLorin Salvant', 'attendance' => 220000, 'price_from' => 35, 'hue' => 45, 'featured' => 0],
    ['slug' => 'cabaret-vert', 'name' => 'Cabaret Vert', 'city' => 'Charleville-Mézières', 'region' => 'Grand Est', 'venue' => 'Square Bayard',
     'start_date' => '2027-08-19', 'end_date' => '2027-08-23', 'genres' => 'Rock,Rap,Électro,Punk',
     'tagline' => 'Le festival rock et engagé des Ardennes.',
     'description' => "Circuits courts, bière locale et une programmation éclectique au bord de la Meuse.",
     'lineup' => 'Shaka Ponk,Orelsan,Queens of the Stone Age,Lujipeka,Prodigy', 'attendance' => 125000, 'price_from' => 52, 'hue' => 140, 'featured' => 0],
]);

$insert('rides', [
    ['user_id' => 1, 'festival_id' => 1, 'departure_city' => 'Paris',    'meeting_point' => 'Porte d\'Orléans',       'departure_at' => '2027-06-17 08:00', 'return_at' => '2027-06-20 14:00', 'seats' => 3, 'price' => 32, 'vehicle' => 'Peugeot 308 break', 'notes' => 'Grand coffre pour les tentes. Playlist metal non négociable 🤘'],
    ['user_id' => 4, 'festival_id' => 1, 'departure_city' => 'Lille',    'meeting_point' => 'Gare Lille-Flandres',    'departure_at' => '2027-06-16 18:00', 'return_at' => '2027-06-20 12:00', 'seats' => 2, 'price' => 45, 'vehicle' => 'Renault Kangoo',    'notes' => 'Départ la veille pour avoir une bonne place au camping.'],
    ['user_id' => 6, 'festival_id' => 2, 'departure_city' => 'Rennes',   'meeting_point' => 'Métro Villejean',        'departure_at' => '2027-07-15 10:00', 'return_at' => '2027-07-18 23:30', 'seats' => 4, 'price' => 12, 'vehicle' => 'Citroën Berlingo',  'notes' => 'Retour de nuit après le dernier concert.'],
    ['user_id' => 3, 'festival_id' => 2, 'departure_city' => 'Nantes',   'meeting_point' => 'Gare sud',               'departure_at' => '2027-07-16 09:30', 'return_at' => '2027-07-18 20:00', 'seats' => 2, 'price' => 18, 'vehicle' => 'Clio',              'notes' => null],
    ['user_id' => 1, 'festival_id' => 2, 'departure_city' => 'Paris',    'meeting_point' => 'Porte Maillot',          'departure_at' => '2027-07-15 07:00', 'return_at' => null,               'seats' => 3, 'price' => 38, 'vehicle' => 'Peugeot 308 break', 'notes' => 'Aller simple, je reste ensuite en Bretagne.'],
    ['user_id' => 2, 'festival_id' => 9, 'departure_city' => 'Grenoble', 'meeting_point' => 'Gare de Grenoble',       'departure_at' => '2027-05-27 17:00', 'return_at' => '2027-05-30 18:00', 'seats' => 3, 'price' => 9,  'vehicle' => 'Van VW T5',         'notes' => 'Possibilité de dormir dans le van.'],
    ['user_id' => 2, 'festival_id' => 4, 'departure_city' => 'Lyon',     'meeting_point' => 'Part-Dieu',              'departure_at' => '2027-07-01 13:00', 'return_at' => '2027-07-04 15:00', 'seats' => 3, 'price' => 22, 'vehicle' => 'Van VW T5',         'notes' => null],
    ['user_id' => 5, 'festival_id' => 6, 'departure_city' => 'Bordeaux', 'meeting_point' => 'Gare Saint-Jean',        'departure_at' => '2027-06-30 15:00', 'return_at' => '2027-07-03 13:00', 'seats' => 2, 'price' => 10, 'vehicle' => 'Camping-car',       'notes' => 'Ambiance familiale, non-fumeurs.'],
    ['user_id' => 4, 'festival_id' => 7, 'departure_city' => 'Lille',    'meeting_point' => 'Métro Porte des Postes', 'departure_at' => '2027-07-02 14:00', 'return_at' => '2027-07-04 01:00', 'seats' => 3, 'price' => 6,  'vehicle' => 'Renault Kangoo',    'notes' => null],
    ['user_id' => 3, 'festival_id' => 8, 'departure_city' => 'Nantes',   'meeting_point' => 'Commerce',               'departure_at' => '2027-07-10 09:00', 'return_at' => '2027-07-14 22:00', 'seats' => 3, 'price' => 15, 'vehicle' => 'Clio',              'notes' => null],
    ['user_id' => 1, 'festival_id' => 12, 'departure_city' => 'Paris',   'meeting_point' => 'Porte de la Villette',   'departure_at' => '2027-08-19 09:00', 'return_at' => '2027-08-23 16:00', 'seats' => 3, 'price' => 26, 'vehicle' => 'Peugeot 308 break', 'notes' => null],
]);

$insert('housings', [
    ['user_id' => 3, 'festival_id' => 1, 'title' => 'Chambre au calme dans les vignes', 'type' => 'chambre', 'city' => 'Gorges', 'distance_km' => 4.5, 'capacity' => 2, 'price_night' => 45, 'description' => "Chambre double dans une longère, petit-déjeuner compris. Je peux déposer au festival le matin.", 'amenities' => 'Petit-déjeuner,Douche,Parking,Navette'],
    ['user_id' => 5, 'festival_id' => 1, 'title' => 'Emplacement tente dans un jardin', 'type' => 'tente', 'city' => 'Clisson', 'distance_km' => 1.2, 'capacity' => 4, 'price_night' => 12, 'description' => "Jardin ombragé à 15 min à pied du site, accès à une douche chaude et aux toilettes.", 'amenities' => 'Douche,Toilettes,Prises électriques'],
    ['user_id' => 6, 'festival_id' => 2, 'title' => 'Maison de bourg pour une bande', 'type' => 'maison', 'city' => 'Carhaix', 'distance_km' => 2.0, 'capacity' => 6, 'price_night' => 140, 'description' => "Maison entière avec trois chambres, jardin et barbecue. Idéal pour un groupe d'amis.", 'amenities' => 'Cuisine,Jardin,Wifi,Parking,Barbecue'],
    ['user_id' => 3, 'festival_id' => 2, 'title' => 'Van aménagé garé près du site', 'type' => 'van', 'city' => 'Carhaix', 'distance_km' => 0.8, 'capacity' => 2, 'price_night' => 35, 'description' => "Lit double, rideaux occultants et réchaud. Le van reste stationné sur un terrain privé.", 'amenities' => 'Lit double,Réchaud,Glacière'],
    ['user_id' => 2, 'festival_id' => 9, 'title' => 'Appart à la Croix-Rousse', 'type' => 'appartement', 'city' => 'Lyon', 'distance_km' => 6.0, 'capacity' => 3, 'price_night' => 70, 'description' => "T2 lumineux, métro direct vers les Grandes Locos. Canapé-lit + lit double.", 'amenities' => 'Wifi,Cuisine,Métro proche'],
    ['user_id' => 5, 'festival_id' => 6, 'title' => 'Camping à la ferme', 'type' => 'tente', 'city' => 'Marmande', 'distance_km' => 3.0, 'capacity' => 8, 'price_night' => 10, 'description' => "Grand pré au bord d'un verger, douches solaires et produits de la ferme.", 'amenities' => 'Douche,Toilettes,Produits locaux'],
    ['user_id' => 4, 'festival_id' => 7, 'title' => 'Chambre d\'amis près de la gare', 'type' => 'chambre', 'city' => 'Arras', 'distance_km' => 1.5, 'capacity' => 2, 'price_night' => 40, 'description' => "Chambre simple et propre, je rentre moi aussi tard du festival.", 'amenities' => 'Wifi,Douche'],
    ['user_id' => 1, 'festival_id' => 3, 'title' => 'Canapé dans le 16e', 'type' => 'appartement', 'city' => 'Paris', 'distance_km' => 3.2, 'capacity' => 1, 'price_night' => 25, 'description' => "Canapé-lit confortable à deux stations de Longchamp.", 'amenities' => 'Wifi,Douche,Métro proche'],
    ['user_id' => 5, 'festival_id' => 8, 'title' => 'Studio vue sur le port', 'type' => 'appartement', 'city' => 'La Rochelle', 'distance_km' => 0.5, 'capacity' => 2, 'price_night' => 85, 'description' => "Studio rénové à deux pas de l'esplanade Saint-Jean-d'Acre.", 'amenities' => 'Wifi,Cuisine,Vue mer'],
    ['user_id' => 2, 'festival_id' => 4, 'title' => 'Tente au bord du lac', 'type' => 'tente', 'city' => 'Lachapelle-sous-Chaux', 'distance_km' => 2.5, 'capacity' => 3, 'price_night' => 15, 'description' => "Terrain privé avec accès au lac pour se rafraîchir entre deux concerts.", 'amenities' => 'Toilettes,Accès lac'],
]);

$insert('ride_bookings', [
    ['ride_id' => 1, 'user_id' => 2, 'seats' => 1, 'message' => 'Je ramène les chips !'],
    ['ride_id' => 3, 'user_id' => 1, 'seats' => 2, 'message' => null],
]);

$insert('housing_bookings', [
    ['housing_id' => 2, 'user_id' => 4, 'guests' => 2, 'nights' => 3, 'message' => null],
]);

if (PHP_SAPI === 'cli') {
    echo "Base Karavan initialisée (12 festivals, 6 membres + 1 admin). Mot de passe démo : karavan\n";
    echo "Admin : admin@karavan.test / karavan\n";
}
