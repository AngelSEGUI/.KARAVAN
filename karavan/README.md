# Karavan 🚐

Application communautaire pour festivaliers :
- **Découverte** de festivals (mise en avant, filtres par style / région / mois, tri)
- **Covoiturage** : proposer un trajet, réserver des places
- **Logements** proposés par la communauté près des festivals (tente, van, chambre, appartement, maison)
- Comptes membres, espace personnel (mes trajets, mes logements, mes réservations)

PHP 8.1+ (extension `pdo_sqlite` requise ; `mbstring` conseillée mais facultative) sans dépendance ni framework, architecture **MVC**, design responsive (mobile first, barre d'onglets sur mobile).

## Démarrage rapide

```bash
cd karavan
php -S localhost:8000 -t public public/index.php
```

Ouvrir http://localhost:8000. Au premier lancement, la base SQLite (`storage/karavan.sqlite`) est créée et remplie automatiquement.

Comptes de démo (mot de passe `karavan`) :
- **Administrateur** : `admin@karavan.test` → espace `/admin`
- Membres : `lea@karavan.test`, `malik@karavan.test`, `chloe@karavan.test`…

N'importe qui peut créer un compte membre via `/inscription`.

Réinitialiser les données : `php database/seed.php`

> Les dates, programmations et chiffres des festivals sont **fictifs** (données de démonstration).

Avec Apache (XAMPP, WAMP, MAMP) : copier le dossier dans `htdocs/` puis ouvrir `http://localhost/karavan/`. Le `.htaccess` racine envoie tout vers `public/` ; activer `mod_rewrite` pour des URL propres. En production, faire pointer le VirtualHost directement sur `public/`.

### Le site s'affiche sans mise en forme ?

0. Regarder **en bas de la page** ou dans le terminal du serveur : une erreur PHP peut interrompre l'affichage. Depuis la v1.2, une page « Oups, une erreur est survenue » explique la cause et la solution.

1. Vérifier que vous ouvrez bien `http://localhost:8000` (ou `http://localhost/karavan/`), et **pas** le fichier en double-cliquant dessus (`file:///…`) : un site PHP doit passer par un serveur.
2. Forcer le rechargement du cache : `Ctrl + F5` (Windows) ou `Cmd + Shift + R` (Mac).
3. Ouvrir les outils de développement (`F12`) → onglet **Réseau**, recharger, et regarder la ligne `style.css` : son statut (404, 403…) et son URL indiquent la cause.
4. Sous Apache : `mod_rewrite` doit être actif et `AllowOverride All` autorisé pour le dossier.

## Structure

```
karavan/
├── public/                 ← seul dossier exposé au web
│   ├── index.php           Front controller (point d'entrée unique)
│   ├── .htaccess
│   └── assets/css|js
├── config/
│   ├── config.php          Paramètres (BDD, debug)
│   └── routes.php          Table de routage  ← ajouter une page commence ici
├── app/
│   ├── bootstrap.php       Autoload PSR-4, session, config
│   ├── helpers.php         e(), url(), csrf_field(), old(), date_fr(), price()…
│   ├── Core/               Micro-framework : Router, Controller, Model, View, Database, Auth, Validator
│   ├── Controllers/        Home, Festival, Ride, Housing, Auth, Account
│   │   └── Admin/          Dashboard, Festival, Ride, Housing, User (rôle admin requis)
│   ├── Models/             Festival, Ride, Housing, User, RideBooking, HousingBooking
│   └── Views/
│       ├── layouts/        main.php (site) et admin.php (administration)
│       ├── partials/       Cartes réutilisables (festival, trajet, logement), icônes
│       └── home/ festivals/ rides/ housings/ auth/ account/ admin/ errors/
├── database/
│   ├── schema.sql
│   └── seed.php
└── storage/                Base SQLite
```

## Routes

| Méthode | URL | Action |
|---|---|---|
| GET | `/` | Accueil / découverte |
| GET | `/festivals` | Catalogue filtrable (`q`, `genre`, `region`, `month`, `sort`) |
| GET | `/festivals/{slug}` | Fiche festival + trajets + logements |
| GET | `/covoiturages` | Liste (`festival`, `from`, `seats`) |
| GET/POST | `/covoiturages/nouveau`, `/covoiturages` | Proposer un trajet 🔒 |
| GET | `/covoiturages/{id}` | Détail du trajet |
| POST | `/covoiturages/{id}/reserver` | Réserver des places 🔒 |
| GET | `/logements` | Liste (`festival`, `type`, `guests`, `max_price`, `sort`) |
| GET/POST | `/logements/nouveau`, `/logements` | Proposer un logement 🔒 |
| GET | `/logements/{id}` | Détail du logement |
| POST | `/logements/{id}/reserver` | Demande de séjour 🔒 |
| GET/POST | `/connexion`, `/inscription` | Authentification |
| POST | `/deconnexion` | Déconnexion |
| GET | `/mon-espace` | Tableau de bord du membre 🔒 |
| GET/POST | `/admin/...` | Administration (voir plus bas) 🛡️ |

## Administration

Accessible aux comptes ayant le rôle `admin` (lien « Administration » dans le menu du profil). Toutes les actions sont protégées par CSRF et demandent une confirmation.

| URL | Fonction |
|---|---|
| `/admin` | Tableau de bord : chiffres clés, activité récente, festivals sans offre, nouveaux membres |
| `/admin/festivals` | Créer, modifier, supprimer un festival ; étoile = mettre à la une ; aperçu en direct de la couverture |
| `/admin/covoiturages` | Modération des trajets (taux de remplissage, suppression) |
| `/admin/logements` | Modération des logements (demandes reçues, suppression) |
| `/admin/membres` | Recherche, nommer/retirer un admin, suspendre/réactiver, supprimer un compte |

Règles :
- Un compte **suspendu** ne peut plus se connecter et sa session en cours est fermée immédiatement ; ses offres restent visibles (à supprimer si besoin).
- Supprimer un festival ou un membre supprime en cascade ses trajets, logements et réservations.
- Un admin ne peut pas modifier son propre compte depuis la liste (pas de risque de se retirer ses droits par erreur).
- **Premier admin sur une base existante** : la migration automatique (`app/Core/Migrator.php`) ajoute les colonnes `role` et `suspended` et nomme administrateur le compte le plus ancien. Autre option :
  `sqlite3 storage/karavan.sqlite "UPDATE users SET role='admin' WHERE email='vous@exemple.fr'"`

Le code se trouve dans `app/Controllers/Admin/` (tous héritent de `AdminController`, qui vérifie le rôle), `app/Views/admin/`, `app/Views/layouts/admin.php` et `public/assets/css/admin.css`.

## Ajouter une fonctionnalité (exemple : page « À propos »)

1. Route dans `config/routes.php` : `$router->get('/a-propos', [PageController::class, 'about']);`
2. Contrôleur `app/Controllers/PageController.php` qui étend `App\Core\Controller` et appelle `$this->render('pages/about', [...])`
3. Vue `app/Views/pages/about.php`

Pour les données : créer un modèle dans `app/Models/` qui étend `App\Core\Model` avec `protected static string $table = '...'` — `find()`, `all()`, `create()`, `update()` sont fournis.

## Sécurité incluse

- Échappement systématique (`e()`), requêtes préparées PDO
- Jeton CSRF sur tous les formulaires POST
- Mots de passe hachés (`password_hash`), régénération de session à la connexion
- Validation serveur avec messages en français et conservation de la saisie

## Passer sur MySQL

1. Dans `config/config.php` : `'driver' => 'mysql'` + identifiants
2. Dans `database/schema.sql`, remplacer `INTEGER PRIMARY KEY AUTOINCREMENT` par `INT AUTO_INCREMENT PRIMARY KEY`
3. Lancer `php database/seed.php`

Les requêtes des modèles sont écrites pour fonctionner sur les deux moteurs.

## Pistes d'évolution

- Upload de photos (festivals, logements) — la couverture est aujourd'hui générée en CSS à partir d'une teinte
- Messagerie entre membres, validation/refus des demandes par le conducteur ou l'hôte
- Avis et notes des membres, carte interactive (Leaflet), administration des festivals
