# CEDICOM Group : site vitrine (Laravel)

Site vitrine en Laravel 13 + React (via Inertia.js) + Tailwind CSS 4, compilé par Vite. Bilingue français / anglais. Pas d'e-commerce ni d'espace connecté.

## Pages

| URL | Contenu |
| --- | --- |
| `/` | Accueil : hero, pôles d'activité, histoire (`#a-propos`), marques partenaires (`#marques`), divisions Retail Group (`#divisions`), logistique (`#logistique`), formulaire partenaire (`#partenariat`), contact |
| `/devenir-partenaire` | Formulaire complet « Partner with us » |
| `/contact` | Coordonnées et formulaire de contact |
| `/langue/fr`, `/langue/en` | Change la langue (gardée en session, français par défaut) |

Les demandes de partenariat sont enregistrées dans la table `partnership_requests`, les messages de contact dans `contact_messages`.
Les contenus répétés (pôles, marques partenaires, divisions et enseignes) viennent des tables `poles`, `partner_brands`, `divisions` et `enseignes`, remplies par `database/seeders/SiteContentSeeder.php`. Pour modifier un texte ou ajouter une marque, éditer ce seeder puis relancer `php artisan db:seed --class=SiteContentSeeder` (il vide et recharge uniquement ces quatre tables).

Les images sont dans `public/images/` (`partenaires/`, `enseignes/`, `divisions/`, `poles/`, `logistique/`, `logo/`).

## Lancer le site en local (SQLite)

```bash
composer install
npm install
php artisan migrate --seed
npm run build
php artisan serve --port=8010
```

Puis ouvrir http://127.0.0.1:8010. Pendant le développement, `npm run dev` recharge le CSS à chaud.

## Mise en production (MySQL)

1. Créer la base et l'utilisateur MySQL :

   ```sql
   CREATE DATABASE cedicom_site CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'cedicom'@'localhost' IDENTIFIED BY 'mot_de_passe_solide';
   GRANT ALL PRIVILEGES ON cedicom_site.* TO 'cedicom'@'localhost';
   ```

2. Copier `.env.example` en `.env` sur le serveur, puis :
   - `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://votre-domaine` ;
   - commenter `DB_CONNECTION=sqlite` et décommenter le bloc MySQL en y mettant les vrais identifiants ;
   - générer la clé : `php artisan key:generate`.

3. Installer et construire :

   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   php artisan migrate --force
   php artisan db:seed --force
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```

4. Faire pointer la racine web du serveur (Apache/Nginx) sur le dossier `public/`, et donner les droits d'écriture à `storage/` et `bootstrap/cache/`.

Le dossier `public/build` est produit par `npm run build` : soit on le construit sur le serveur, soit on le construit en local et on l'envoie avec le reste.

## Consulter les demandes reçues

Il n'y a pas encore d'interface d'administration. En attendant :

```bash
php artisan tinker
>>> App\Models\PartnershipRequest::latest()->get();
>>> App\Models\ContactMessage::latest()->get();
```

## Langues

- Textes de l’interface : `lang/fr.json` et `lang/en.json` (mêmes clés). Ils sont partagés avec React par `HandleInertiaRequests` et lus avec `useT()` (`resources/js/lib/i18n.js`). Les messages de validation y sont aussi.
- Contenus en base (pôles, divisions) : colonnes JSON `{"fr": ..., "en": ...}`, à modifier dans `SiteContentSeeder`.
- Chaque demande enregistre la langue utilisée (colonne `locale`). Les secteurs et objets sont stockés sous forme de clé (`fmcg`, `fashion`, `sales`…), libellés dans les fichiers de langue.

## Structure du front

- `resources/views/app.blade.php` : page racine Inertia.
- `resources/js/Pages/Site/` : pages `Home`, `Partner`, `Contact` (props envoyées par les contrôleurs depuis la base).
- `resources/js/Components/Site/` : en-tête, pied de page, formulaires (`useForm` d’Inertia), champs, icônes.
- `resources/css/site.css` : thème Tailwind (couleurs, polices).

## Notes techniques

- Le projet partait d’un squelette Breeze React/Inertia. Ses pages (`resources/js/Pages/Auth`, `Dashboard`…) et `routes/auth.php` sont conservés mais exclus du build et des routes. Les paquets Breeze inutilisés (`@headlessui/react`, `@tailwindcss/forms`, `autoprefixer`) ont été retirés de `package.json`.
- Les formulaires sont protégés par CSRF, limités à 6 envois par minute et par IP, et ont un champ piège anti-spam.
