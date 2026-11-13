# Karavan 🚐

Application communautaire pour festivaliers :

- **Découverte** des festivals : mise en avant, filtres par style, région et mois, tri.
- **Covoiturage** : proposer, modifier ou supprimer un trajet, demander des places.
- **Logements** proposés par la communauté près des festivals : publication, modification, demandes de séjour.
- **Demandes de réservation** : le conducteur ou l'hôte **accepte ou refuse**, le demandeur peut **annuler** ; chaque étape envoie un **email**.
- **Comptes** : inscription avec **vérification de l'adresse email**, connexion, espace personnel, modification du profil et du mot de passe, suppression du compte.
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
| `pro@karavan.test` | Organisatrice : gère la page du « Festival Marées Hautes » (`/organisateur`) |
| `yann@karavan.test` | Membre avec une demande de profil organisateur en attente (à valider dans l'admin) |
| `lea@karavan.test` | Membre : trajets, un logement, une réservation |
| `malik@karavan.test`, `chloe@karavan.test`, `thomas@karavan.test`, `ines@karavan.test`, `hugo@karavan.test` | Membres |

> Les dates et programmations sont **fictives** (données de démo dans `database/seeders/data/demo.php`).
> Le « Festival Marées Hautes » est entièrement inventé (artistes et visuels générés) : `database/seeders/OrganizerDemoSeeder.php`, images dans `database/seeders/media/marees-hautes/`.
> Pour tester les réponses aux demandes : connectez-vous avec `lea@karavan.test`, une demande de Malik l'attend dans « Mon espace ».

---

## Les organisateurs (profil pro)

Seul un **organisateur** (profil pro) ou un admin peut créer un festival.

1. **Devenir organisateur** : un membre remplit « Vous organisez un festival ? » dans *Modifier mon profil* (nom de la structure, site, message). Les admins sont prévenus par email.
2. **Validation** : dans *Administration › Membres › Demandes pro*, l'admin valide ou refuse ; le membre reçoit un email dans les deux cas. L'admin peut aussi changer le rôle d'un membre directement (Membre / Organisateur / Admin).
3. **Espace organisateur** (`/organisateur`, menu de l'avatar) : la liste de ses festivals, puis un éditeur en 4 onglets :
   - **Informations** : nom, dates, lieu, styles, prix, liens billetterie et site officiel.
   - **Apparence** : palette (8 au choix ou couleurs sur mesure, fond clair/sombre), police des titres et du texte, paragraphes de présentation. Aperçu en direct.
   - **Images** : bannière, icône, affiche de la programmation, photos du carrousel (avec légendes).
   - **Programmation** : un créneau par ligne — journée, début, fin, artiste, scène, capacité (facultative, **masquée au public si vide**).
4. **Publication** : un festival est créé en **brouillon**, invisible du public (404) tant que l'organisateur ne le publie pas. « À la une » reste réservé aux admins.

Sur la page du festival, la programmation s'affiche en **calendrier** : un onglet par journée, une colonne par scène. Survoler (ou toucher) un créneau affiche le détail : artiste, scène, jour, horaire, durée et capacité si elle est renseignée. Une vue « Liste » est aussi proposée.

> Une journée de festival va de **6h à 6h** : un concert saisi « samedi, 01:30 » a lieu dans la nuit de samedi à dimanche et s'affiche dans la soirée du samedi. Une heure de fin plus petite que le début signifie « après minuit ».

**Où sont les fichiers ?**

| Quoi | Où |
|---|---|
| Palettes et polices proposées, limites (taille d'image, nombre de photos…) | `config/festival_design.php` |
| Conversion palette → variables CSS, contraste automatique du texte | `app/Support/FestivalDesign.php` |
| Calcul du calendrier (positions en minutes) | `app/Support/Timetable.php` |
| Contrôleurs | `app/Http/Controllers/Organizer/` |
| Droits (qui peut voir, modifier, mettre à la une) | `app/Policies/FestivalPolicy.php` |
| Vues de l'éditeur / page publique | `resources/views/organizer/`, `resources/views/festivals/show.blade.php` |
| Styles et scripts | `public/css/organizer.css`, `public/css/festival-page.css`, `public/js/organizer.js`, `public/js/festival.js` |

**Images envoyées** : rangées dans `public/uploads/festivals/{id}/` (aucun `storage:link` nécessaire), supprimées avec le festival. Ce dossier n'est pas versionné : sauvegardez-le en production.
La taille maximale est la plus petite entre `max_image_kb` (5 Mo) et la configuration de PHP. Par défaut PHP limite à **2 Mo** ; pour l'augmenter, dans `php.ini` :

```ini
upload_max_filesize = 8M
post_max_size = 40M
```

(`php --ini` indique quel fichier modifier ; sous Windows avec XAMPP : `C:\xampp\php\php.ini`.) L'éditeur affiche toujours la limite réellement en vigueur.

---

## Les emails

Par défaut (`MAIL_MAILER=log` dans `.env`), **aucun email n'est réellement envoyé** : ils sont écrits à la fin de `storage/logs/laravel.log`. Pratique pour développer sans risque d'envoyer un message à un vrai utilisateur.

- Après une inscription, la page « Vérifiez votre boîte mail » affiche en développement un lien **« confirmer l'adresse maintenant »** : pas besoin d'aller chercher l'email dans le journal.
- Pour **voir les emails comme dans une vraie boîte**, installez [Mailpit](https://mailpit.axllent.org/) (un faux serveur d'emails local), lancez-le, puis mettez dans `.env` :
  ```
  MAIL_MAILER=smtp
  MAIL_HOST=127.0.0.1
  MAIL_PORT=1025
  ```
  Les emails apparaissent sur http://localhost:8025.
- **En production**, utilisez un service d'envoi (Brevo, Mailjet, Postmark, Amazon SES…) et renseignez ses identifiants SMTP dans `.env` (`MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`). Pensez à `APP_URL` : c'est lui qui sert à construire les liens dans les emails.

| Email | Envoyé à | Quand |
|---|---|---|
| Confirmez votre adresse email | Nouveau membre | Inscription, changement d'email, ou clic sur « Renvoyer » |
| Nouvelle demande | Conducteur / hôte | Un membre demande une place ou un séjour |
| C'est validé | Demandeur | La demande est acceptée |
| Demande non retenue | Demandeur | La demande est refusée |
| Demande annulée | Conducteur / hôte | Le demandeur annule, ou supprime son compte |
| Annulation de l'annonce | Demandeurs en cours | L'annonce, son festival ou le compte de son auteur est supprimé |

Les textes sont dans `app/Notifications/` (un fichier par email) et `app/Providers/AppServiceProvider.php` (email de vérification).

Les emails partent immédiatement, pendant la requête. Avec un service d'envoi lent, on peut les envoyer en arrière-plan : voir le commentaire en tête de `app/Notifications/BookingNotification.php`.

---

## Commandes utiles

| Commande | Effet |
|---|---|
| `php artisan serve` | Lance le site sur http://localhost:8000 |
| `php artisan test` | Lance les tests automatisés (63 tests) |
| `composer run demo-reset` | **Efface** la base et recharge les données de démo |
| `php artisan route:list` | Liste toutes les URL du site |
| `php artisan migrate` | Applique les nouvelles migrations de la base |
| `php artisan tinker` | Console interactive (ex. `User::count()`) |

---

## Structure du projet

```
app/
├── Enums/                 UserRole (membre/admin), HousingType (tente, van…), BookingStatus (en attente, acceptée…)
├── Http/
│   ├── Controllers/       Home, Festival, Ride, Housing, Booking (accepter/refuser/annuler), Account, Profile, StyleGuide
│   │   ├── Auth/          Connexion, inscription, vérification de l'email
│   │   └── Admin/         Tableau de bord, festivals, trajets, logements, membres
│   ├── Middleware/        EnsureUserIsAdmin, LogoutSuspendedUsers
│   └── Requests/          Validation des formulaires (RideRequest, HousingRequest…)
├── Models/                User, Festival, Ride, Housing, RideBooking, HousingBooking
│   └── Concerns/IsBooking  Comportement commun des réservations (statuts, transitions)
├── Notifications/         Emails envoyés aux membres
├── Policies/              Qui peut modifier / supprimer / réserver / répondre à quoi
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
- **Festival** : créé par un organisateur ou un admin, en brouillon jusqu'à publication. Un organisateur ne modifie que ses propres festivals ; l'admin les modifie tous. On ne peut proposer trajet ou logement que pour un festival publié.
- **Supprimer une annonce, un festival ou un compte** supprime aussi les réservations liées (avec confirmation).
- **Email non vérifié** : on peut se connecter et consulter le site, mais pas publier, réserver ni répondre aux demandes. Changer d'email impose une nouvelle vérification.
- **Demande de réservation** : impossible sur sa propre annonce, une seule demande en cours par annonce, dans la limite des places ou de la capacité. Elle est créée **en attente**.
- **Réponse** : seul l'auteur de l'annonce accepte ou refuse, une seule fois. Une demande en attente ou acceptée occupe des places ; refusée ou annulée, elle les libère.
- **Annulation** : le demandeur peut annuler une demande en attente ou acceptée, puis en refaire une. Après un refus, il ne peut pas redemander la même annonce.
- **Compte suspendu** : connexion refusée, et la session en cours est coupée immédiatement.
- **Dernier administrateur** : il ne peut pas supprimer son compte tant qu'aucun autre admin n'existe.
- **Sécurité** : jeton CSRF sur tous les formulaires, mots de passe hachés, limite de 6 tentatives de connexion par minute, échappement automatique dans les vues.

---

## Mise en production

1. Dans `.env` : `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://votre-domaine.fr`.
2. Faites pointer le serveur web sur le dossier **`public/`**.
3. `composer install --no-dev --optimize-autoloader`, puis `php artisan migrate --force` et `php artisan optimize`.
4. Donnez les droits d'écriture sur `storage/`, `bootstrap/cache/` et `public/uploads/`. Réglez `upload_max_filesize` et `post_max_size` dans `php.ini` (voir « Les organisateurs »).
5. Créez votre compte, puis nommez-le administrateur :
   ```bash
   php artisan tinker --execute="App\Models\User::where('email', 'vous@exemple.fr')->update(['role' => 'admin'])"
   ```
   Ne chargez pas les données de démo en production (mots de passe connus).

Pour MySQL : renseignez `DB_CONNECTION=mysql`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` dans `.env`, puis `php artisan migrate`.

---

## Prochaines étapes envisagées

- Mot de passe oublié (Laravel fournit le mécanisme, les emails sont déjà en place).
- Messagerie entre membres.
- Photos des annonces, avatars.
- Statistiques pour les organisateurs (vues de la page, trajets et logements générés).
- Notifications aussi sur le site (cloche), et rappel automatique des demandes restées sans réponse.
