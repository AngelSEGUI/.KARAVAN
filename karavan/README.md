# Karavan 🚐

Application communautaire pour festivaliers :

- **Découverte** des festivals : mise en avant, filtres par style, région et mois, tri.
- **Covoiturage** : proposer, modifier ou supprimer un trajet, réserver des places.
- **Logements** proposés par la communauté près des festivals : publication, modification, demandes de séjour.
- **Comptes** : inscription, connexion, espace personnel, modification du profil et du mot de passe, suppression du compte.
- **Administration** : catalogue des festivals, modération des annonces, gestion des membres (rôles, suspension).
- **Thèmes graphiques** interchangeables et guide de styles (voir [`docs/THEMES.md`](docs/THEMES.md)).

Construit avec **Laravel 13** (PHP 8.3+), Blade, SQLite par défaut (MySQL/PostgreSQL possibles). Aucun outil front à installer : CSS et JavaScript simples, sans compilation.

---

## Installation

### 1. Prérequis

- **PHP 8.3 ou plus** avec les extensions `mbstring`, `xml`, `sqlite3` (pdo_sqlite), `curl`, `zip`, `openssl`.
- **Composer** (le gestionnaire de dépendances de PHP).

**Linux (Ubuntu / Debian)**

```bash
sudo apt install php-cli php-mbstring php-xml php-sqlite3 php-curl php-zip unzip composer
```

**Windows**

1. PHP : téléchargez le zip « VS17 x64 Thread Safe » de PHP 8.4 sur <https://windows.php.net/download/>, décompressez-le dans `C:\php`, ajoutez `C:\php` au `Path`.
2. Dans `C:\php`, copiez `php.ini-development` en `php.ini`, puis enlevez le `;` devant ces lignes :
   ```
   extension_dir = "ext"
   extension=curl
   extension=fileinfo
   extension=mbstring
   extension=openssl
   extension=pdo_sqlite
   extension=zip
   ```
3. Composer : installez-le avec `Composer-Setup.exe` depuis <https://getcomposer.org/download/>.
4. Ouvrez un **nouveau** terminal et vérifiez : `php -v` et `composer -V`.

**macOS** : `brew install php composer`

### 2. Installer le projet (une seule fois)

Dans le dossier du projet :

```bash
composer run setup
```

Cette commande installe les dépendances, crée le fichier `.env`, génère la clé de l'application, crée la base SQLite et charge les données de démonstration.

### 3. Lancer le site

```bash
php artisan serve
```

Ouvrez **<http://localhost:8000>**. `Ctrl + C` arrête le serveur.

### Comptes de démonstration

Mot de passe pour tous les comptes : **`karavan`**

| Compte | Profil |
|---|---|
| `admin@karavan.test` | Administrateur (accès à `/admin`) |
| `lea@karavan.test` | Membre : trajets, un logement, une réservation |
| `malik@karavan.test`, `chloe@karavan.test`, `thomas@karavan.test`, `ines@karavan.test`, `hugo@karavan.test` | Membres |

> Les festivals, dates et programmations sont **fictifs** (données de démo dans `database/seeders/data/demo.php`).

---

## Commandes utiles

| Commande | Effet |
|---|---|
| `php artisan serve` | Lance le site sur http://localhost:8000 |
| `php artisan test` | Lance les tests automatisés (43 tests) |
| `composer run demo-reset` | **Efface** la base et recharge les données de démo |
| `php artisan route:list` | Liste toutes les URL du site |
| `php artisan migrate` | Applique les nouvelles migrations de la base |
| `php artisan tinker` | Console interactive (ex. `User::count()`) |

---

## Structure du projet

```
app/
├── Enums/                 UserRole (membre/admin), HousingType (tente, van…)
├── Http/
│   ├── Controllers/       Home, Festival, Ride, Housing, Account, Profile, StyleGuide
│   │   ├── Auth/          Connexion, inscription
│   │   └── Admin/         Tableau de bord, festivals, trajets, logements, membres
│   ├── Middleware/        EnsureUserIsAdmin, LogoutSuspendedUsers
│   └── Requests/          Validation des formulaires (RideRequest, HousingRequest…)
├── Models/                User, Festival, Ride, Housing, RideBooking, HousingBooking
├── Policies/              Qui peut modifier / supprimer / réserver quoi
├── Support/Theme.php      Choix du thème graphique
└── helpers.php            euros(), km(), plural()
config/karavan.php         Thèmes, pagination, limites (places, nuits…)
database/
├── migrations/            Structure de la base
├── factories/             Données factices pour les tests
└── seeders/               Données de démonstration
lang/fr/                   Messages en français (validation, connexion)
public/css/                Styles : themes/, base, layout, components, pages, admin
public/js/app.js           Petites interactions (filtres, onglets, totaux…)
resources/views/
├── components/            Composants Blade réutilisables (<x-ride-card>, <x-field>…)
│   └── layouts/           app (site) et admin
├── home, festivals/, rides/, housings/, account/, auth/, admin/
├── styleguide.blade.php   Guide de styles (/guide-styles)
└── errors/                Pages 403, 404, 419, 429, 500, 503
routes/web.php             Toutes les URL (en français) et leurs droits d'accès
tests/Feature/             Tests automatisés des parcours
docs/THEMES.md             Guide pour adapter l'interface graphique
```

### Ajouter une fonctionnalité, en pratique

1. **Base** : `php artisan make:migration create_xxx_table`, puis `php artisan migrate`.
2. **Modèle** : `app/Models/Xxx.php` (relations, requêtes réutilisables).
3. **Route** dans `routes/web.php`, avec le bon groupe (`auth`, `admin`…).
4. **Contrôleur** dans `app/Http/Controllers/`, validation dans `app/Http/Requests/`, droits dans `app/Policies/`.
5. **Vue** dans `resources/views/`, en réutilisant les composants (`<x-field>`, `<x-empty>`…).
6. **Test** dans `tests/Feature/`, puis `php artisan test`.

---

## Règles métier

- **Modifier une annonce** : seul son auteur (ou un admin) peut modifier ou supprimer un trajet ou un logement.
- **Annonce déjà réservée** : le festival ne peut plus être changé, et le nombre de places (ou la capacité) ne peut pas descendre sous ce qui est déjà réservé.
- **Supprimer une annonce, un festival ou un compte** supprime aussi les réservations liées (avec confirmation).
- **Réserver** : impossible sur sa propre annonce, une seule fois par annonce, dans la limite des places ou de la capacité.
- **Compte suspendu** : connexion refusée, et la session en cours est coupée immédiatement.
- **Dernier administrateur** : il ne peut pas supprimer son compte tant qu'aucun autre admin n'existe.
- **Sécurité** : jeton CSRF sur tous les formulaires, mots de passe hachés, limite de 6 tentatives de connexion par minute, échappement automatique dans les vues.

---

## Mise en production

1. Dans `.env` : `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://votre-domaine.fr`.
2. Faites pointer le serveur web sur le dossier **`public/`**.
3. `composer install --no-dev --optimize-autoloader`, puis `php artisan migrate --force` et `php artisan optimize`.
4. Donnez les droits d'écriture sur `storage/` et `bootstrap/cache/`.
5. Créez votre compte, puis nommez-le administrateur :
   ```bash
   php artisan tinker --execute="App\Models\User::where('email', 'vous@exemple.fr')->update(['role' => 'admin'])"
   ```
   Ne chargez pas les données de démo en production (mots de passe connus).

Pour MySQL : renseignez `DB_CONNECTION=mysql`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` dans `.env`, puis `php artisan migrate`.

---

## Prochaines étapes envisagées

- Acceptation / refus des réservations par le conducteur ou l'hôte (statut de réservation, notifications par email).
- Messagerie entre membres.
- Photos des annonces et des festivals, avatars.
- Mot de passe oublié et vérification de l'email (fournis par Laravel, à brancher sur un service d'envoi d'emails).
