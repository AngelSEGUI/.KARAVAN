# Thèmes et styles de Karavan

Ce guide explique comment changer l'apparence du site **sans toucher aux fonctionnalités** : couleurs, polices, arrondis, ombres, et même la mise en page.

## En bref

- Le site est livré avec **3 thèmes** : `festival` (par défaut), `nuit` et `epure`.
- Un thème est **un seul fichier CSS** dans `public/css/themes/` qui ne contient **que des variables** (`--color-primary`, `--font-display`…).
- Tous les composants lisent ces variables. Changez une valeur, tout le site suit.
- La page **`/guide-styles`** affiche tous les composants du site sur une seule page et permet de passer d'un thème à l'autre en un clic.

Pas d'outil de compilation (Node, Vite, Sass…) : on modifie le CSS, on recharge la page, c'est tout.

---

## 1. Essayer les thèmes existants

**Pour vous seul, depuis le navigateur** (en développement) :
- ouvrez `http://localhost:8000/guide-styles` et cliquez sur un thème ;
- ou ajoutez `?theme=nuit` à n'importe quelle adresse : `http://localhost:8000/?theme=nuit`.

Le choix est mémorisé pour votre session. Les autres visiteurs ne sont pas concernés.

**Pour tout le monde** : dans le fichier `.env`, changez la ligne

```
KARAVAN_THEME=festival
```

En production (`APP_ENV=production`), le changement depuis le navigateur est automatiquement désactivé : seul `KARAVAN_THEME` compte. Vous pouvez aussi le couper en développement avec `KARAVAN_THEME_SWITCHER=false`.

---

## 2. Organisation des fichiers CSS

Chargés dans cet ordre par `resources/views/components/layouts/app.blade.php` :

| Fichier | Contenu | Quand le modifier |
|---|---|---|
| `css/themes/{thème}.css` | **Variables uniquement** : couleurs, polices, arrondis, ombres | Pour changer le look (99 % des cas) |
| `css/base.css` | Remise à zéro, titres, textes, icônes | Rarement |
| `css/layout.css` | En-tête, menus, pied de page, barre d'onglets mobile, grilles | Pour changer la structure des pages |
| `css/components.css` | Boutons, étiquettes, cartes, formulaires, messages, pagination… (sommaire numéroté en tête de fichier) | Pour changer la forme d'un composant |
| `css/pages.css` | Mises en page propres à une page (accueil, fiche festival, profil…) | Pour une page en particulier |
| `css/admin.css` | Espace d'administration (chargé seulement dans `/admin`) | Pour l'admin |

**Règle d'or** : aucune couleur ni police n'est écrite en dur dans ces fichiers, tout passe par `var(--…)`. Si vous ajoutez du CSS, gardez cette règle pour que les thèmes continuent de fonctionner.

Points de rupture responsive : **640 px** (tablette) et **900 px** (ordinateur). Les styles de base visent le mobile, les `@media (min-width: …)` élargissent.

---

## 3. Créer un nouveau thème (5 minutes)

1. Copiez `public/css/themes/festival.css` vers `public/css/themes/montheme.css`.
2. Modifiez les valeurs (chaque variable est commentée dans le fichier).
3. Déclarez-le dans `config/karavan.php` :
   ```php
   'themes' => [
       'festival' => 'Festival — chaleureux, affiches rétro',
       'nuit'     => 'Nuit — sombre, néons',
       'epure'    => 'Épuré — minimal, sobre',
       'montheme' => 'Mon thème — description courte',
   ],
   ```
4. Ouvrez `/guide-styles?theme=montheme` pour le vérifier.
5. Quand il vous plaît : `KARAVAN_THEME=montheme` dans `.env`.

Astuce : partez du thème le plus proche de ce que vous voulez (`nuit` pour un thème sombre, `epure` pour un thème sobre).

---

## 4. Référence des variables

### Typographie

| Variable | Rôle | Exemple |
|---|---|---|
| `--font-display` | Titres, prix, chiffres | `"Bricolage Grotesque", system-ui, sans-serif` |
| `--font-body` | Texte courant, formulaires | `"DM Sans", system-ui, sans-serif` |
| `--font-size-base` | Taille du texte de base | `16px` |
| `--heading-weight` | Graisse des titres | `800` |
| `--heading-tracking` | Espacement des lettres des titres | `-0.02em` |
| `--display-transform` | Noms des festivals sur les affiches | `uppercase` ou `none` |

**Changer de police** : remplacez la ligne `@import url("https://fonts.googleapis.com/…")` en haut du thème (copiez-la depuis [fonts.google.com](https://fonts.google.com) → « Get embed code » → onglet `@import`), puis mettez à jour `--font-display` et `--font-body`.

### Couleurs de fond et de texte

| Variable | Rôle |
|---|---|
| `--color-bg` | Fond de page |
| `--color-bg-alt` | Fond secondaire (admin, zones grisées, étiquettes) |
| `--color-surface` | Cartes et panneaux |
| `--color-input` | Champs de formulaire |
| `--color-text` | Texte principal |
| `--color-text-soft` | Texte secondaire appuyé |
| `--color-muted` | Texte discret (légendes, dates) |
| `--color-line` / `--color-line-strong` | Bordures / bordures au survol |

### Couleurs de marque

| Variable | Rôle |
|---|---|
| `--color-primary` | Boutons principaux, liens forts, onglet actif |
| `--color-primary-hover` | Bouton principal au survol |
| `--color-on-primary` | Texte posé sur la couleur principale |
| `--color-primary-soft` | Version très claire (fonds d'icônes, sélection) |
| `--color-accent` | Seconde couleur des dégradés |
| `--color-highlight` / `--color-on-highlight` | Badge « À la une » et son texte |
| `--color-inverse` / `--color-on-inverse` | Blocs « inversés » : pastille de date, avatars, programmation, menu actif |
| `--color-inverse-accent` | Texte coloré sur ces blocs (initiales, mois) |
| `--gradient-brand` | Dégradé du logo, du mot « ensemble » et du bandeau d'appel à l'action |

### Couleurs d'état

`--color-success`, `--color-danger`, `--color-warning`, `--color-info` (texte) et leurs fonds `--color-…-bg` : messages, places restantes, boutons de suppression, alertes.

### Zones particulières

| Variable | Rôle |
|---|---|
| `--header-bg` | Fond de l'en-tête (semi-transparent pour l'effet de flou) |
| `--footer-bg` / `--footer-text` / `--footer-heading` | Pied de page |
| `--band-dark-bg` / `--band-dark-text` / `--band-dark-accent` | Section « Comment ça marche » de l'accueil |
| `--sidebar-bg` | Barre latérale de l'administration |

### Formes, ombres, effets

| Variable | Rôle | Exemples |
|---|---|---|
| `--radius-sm` / `--radius` / `--radius-lg` | Arrondis : petits éléments / cartes / grands blocs | `10px` / `18px` / `28px` |
| `--radius-input` | Arrondi des champs | `12px` |
| `--radius-button` | Arrondi des boutons | `999px` (pilule) ou `8px` (carré) |
| `--border-width` | Épaisseur des bordures | `1px` |
| `--shadow` / `--shadow-lg` / `--shadow-button` | Ombres | `none` pour un style plat |
| `--logo-tilt` / `--poster-tilt` | Rotation du logo / de l'affiche d'accueil | `0deg` pour désactiver |
| `--hover-lift` | Déplacement des cartes au survol | `-4px`, `0px` pour désactiver |
| `--transition` | Vitesse des animations | `0.18s ease` |

### Couvertures des festivals

Les festivals n'ont pas encore de photos : une affiche est générée en CSS à partir de la teinte choisie dans l'admin (0-359).

| Variable | Rôle |
|---|---|
| `--cover-saturation` / `--cover-lightness` | Intensité et luminosité des affiches |
| `--cover-sun` / `--cover-hills` | `block` ou `none` : afficher le soleil rétro / les collines |

---

## 5. Aller plus loin que les couleurs

Les thèmes changent l'ambiance. Pour changer **la structure** d'un élément, modifiez son composant Blade et sa section CSS : la logique (contrôleurs, droits, formulaires) n'est pas concernée.

| Élément | Vue Blade | CSS |
|---|---|---|
| En-tête, pied de page, menu mobile | `resources/views/components/layouts/app.blade.php` | `layout.css` |
| Carte festival | `components/festival-card.blade.php` | `components.css` §5 |
| Carte covoiturage (billet) | `components/ride-card.blade.php` | `components.css` §6 |
| Carte logement | `components/housing-card.blade.php` | `components.css` §7 |
| Champs de formulaire | `components/field.blade.php` | `components.css` §8 |
| Affiche générée | `components/cover.blade.php` | `components.css` §4 |
| Icônes | `partials/icons.blade.php` (sprite SVG) | `base.css` |
| Pages | `resources/views/{home, festivals, rides, housings, account, auth}/…` | `pages.css` |

Les composants sont utilisés partout sous la forme `<x-ride-card :ride="$ride" />` : en modifier un seul met à jour toutes les pages qui l'affichent.

**Pour ajouter une icône** : ajoutez un `<symbol id="i-nom">` dans `partials/icons.blade.php` (les icônes de [lucide.dev](https://lucide.dev) ont le bon format), puis utilisez `<x-icon name="nom" />`.

---

## 6. Checklist avant de valider un thème

- [ ] `/guide-styles` : tous les textes sont lisibles, en particulier sur les blocs foncés (avatars, pastilles de date, badges).
- [ ] Accueil, fiche festival, détail d'un trajet et d'un logement.
- [ ] Un formulaire avec une erreur (envoyez-le vide) : les messages rouges sont lisibles.
- [ ] Mobile : outils de développement (`F12`) → mode téléphone (`Ctrl + Shift + M`).
- [ ] L'administration (`/admin`), qui utilise les mêmes variables.
- [ ] `php artisan test` : les tests vérifient les fonctionnalités, ils doivent toujours passer.
