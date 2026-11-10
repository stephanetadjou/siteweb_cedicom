# Déploiement du site CEDICOM avec GitLab CI/CD : guide pas à pas

Ce guide décrit comment mettre en ligne le site vitrine CEDICOM (Laravel 13 + React via Inertia + Vite + Tailwind 4) sur un serveur Linux, avec le pipeline GitLab du projet.

> **Choix retenu : GitHub Actions.** Le déploiement officiel du site est décrit dans `DEPLOIEMENT_GITHUB.md`.
> Ce guide GitLab est conservé pour le cas où le dépôt passerait sur GitLab. Le pipeline a été mis de côté dans `deploy/gitlab-ci.example.yml` : pour l'activer, le copier à la racine sous le nom `.gitlab-ci.yml`. Dans la suite, « `.gitlab-ci.yml` » désigne ce fichier une fois copié.

Fichiers concernés :

| Fichier | Rôle |
| --- | --- |
| `deploy/gitlab-ci.example.yml` (à copier en `.gitlab-ci.yml`) | Pipeline GitLab : build Vite, tests, déploiement SSH manuel |
| `deploy/remote-deploy.sh` | Script exécuté **sur le serveur** par le job de déploiement |
| `.github/workflows/*.yml` | Anciens workflows GitHub Actions, **non modifiés et non utilisés par GitLab** (voir section 11) |

Aucune valeur de ce guide n'est un vrai secret : les mots de passe, hôtes et clés sont des exemples à remplacer.

---

## 1. Prérequis côté serveur

| Élément | Version / détail |
| --- | --- |
| Système | Linux (Ubuntu 22.04+, Debian 12, ou hébergement cPanel avec accès SSH) |
| PHP (CLI et web) | **8.3 ou plus** (`composer.json` : `"php": "^8.3"`). Si la commande `php` du compte SSH n'est pas la bonne version (cas fréquent sous cPanel), créer la variable `PHP_BIN` (section 4), par ex. `/opt/cpanel/ea-php83/root/usr/bin/php`. |
| Extensions PHP | `mbstring`, `pdo_mysql`, `openssl`, `tokenizer`, `xml`, `dom`, `ctype`, `fileinfo`, `curl`, `zip`, `intl`, `bcmath` |
| Composer | 2.x, trouvé par `command -v composer` pour le compte SSH |
| Outils | `git`, `rsync`, `mysqldump`, `gzip` |
| MySQL / MariaDB | MySQL 8 (ou MariaDB 10.6+), en `utf8mb4` |
| Serveur web | Apache (avec `mod_rewrite`, `.htaccess` fourni dans `public/`) ou Nginx + PHP-FPM |
| Node.js | **Inutile sur le serveur** : le build Vite est fait par GitLab puis envoyé par `rsync` |
| Dossier du site | Un **clone Git** du dépôt GitLab (le déploiement fait `git fetch` puis `git reset --hard <commit>`) |
| HTTPS | Certificat SSL (Let's Encrypt / AutoSSL cPanel) |

## 2. Base MySQL et utilisateur

À exécuter une seule fois, en root MySQL (ou via « Bases de données MySQL » dans cPanel, qui préfixe les noms par le compte) :

```sql
CREATE DATABASE cedicom_site CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cedicom'@'localhost' IDENTIFIED BY 'Exemple-MotDePasse-A-Changer';
GRANT ALL PRIVILEGES ON cedicom_site.* TO 'cedicom'@'localhost';
FLUSH PRIVILEGES;
```

La sauvegarde automatique (`mysqldump --single-transaction --no-tablespaces`) fonctionne avec ces droits.

## 3. Clés SSH

Deux clés différentes sont nécessaires.

**a) Clé de déploiement (GitLab → serveur)**

1. Sur votre poste :
   ```bash
   ssh-keygen -t ed25519 -C "gitlab-deploy-cedicom" -f deploy_cedicom
   ```
   Vous obtenez `deploy_cedicom` (clé **privée**) et `deploy_cedicom.pub` (clé **publique**). Une phrase de passe est possible ; elle ira dans `SSH_PASSPHRASE`.
2. Ajoutez le contenu de `deploy_cedicom.pub` dans `~/.ssh/authorized_keys` du compte de déploiement sur le serveur (ou cPanel > Accès SSH > Gérer les clés > Importer, puis **Autoriser**).
3. Dans GitLab, créez la variable `SSH_PRIVATE_KEY` de type **Fichier** et collez-y tout le contenu de `deploy_cedicom`, de `-----BEGIN OPENSSH PRIVATE KEY-----` à `-----END OPENSSH PRIVATE KEY-----`, **avec un retour à la ligne final**.
4. Récupérez l'empreinte du serveur pour `SSH_KNOWN_HOSTS` (recommandé, évite de faire confiance aveuglément au serveur) :
   ```bash
   ssh-keyscan -p 22 srv.exemple.ga
   ```
   Vérifiez-la, puis collez la sortie dans la variable `SSH_KNOWN_HOSTS`.

**b) Clé de lecture du dépôt (serveur → GitLab)**

Le serveur fait `git fetch` : il doit pouvoir lire le dépôt.

1. Sur le serveur, avec le compte de déploiement : `ssh-keygen -t ed25519 -C "serveur-cedicom"` (sans phrase de passe).
2. Dans GitLab : **Settings > Repository > Deploy keys**, ajoutez `~/.ssh/id_ed25519.pub`, **sans** cocher « Grant write permissions ».
3. Test : `ssh -T git@gitlab.com` (ou votre instance GitLab) doit répondre « Welcome to GitLab ».

## 4. Variables CI/CD à créer dans GitLab

**Settings > CI/CD > Variables > Add variable.** Noms exacts utilisés par `.gitlab-ci.yml` :

| Nom exact | Rôle | Exemple fictif | Protégée | Masquée | Type |
| --- | --- | --- | --- | --- | --- |
| `SSH_HOST` | Adresse du serveur | `203.0.113.10` ou `srv.exemple.ga` | Oui | Non | Variable |
| `SSH_PORT` | Port SSH (22 si vide) | `22` | Oui | Non | Variable |
| `SSH_USER` | Compte SSH de déploiement | `cedicom` | Oui | Non | Variable |
| `SSH_PRIVATE_KEY` | Clé privée de la section 3a | `-----BEGIN OPENSSH PRIVATE KEY----- …` | Oui | Non (multiligne, non masquable) | **Fichier** |
| `SSH_PASSPHRASE` | Phrase de passe de la clé. **Ne pas créer** la variable si la clé n'en a pas. | `exemple-phrase-longue-2026` | Oui | Oui | Variable |
| `DEPLOY_PATH` | Dossier du site sur le serveur (clone Git) | `/home/cedicom/site_web_laravel` | Oui | Non | Variable |
| `SSH_KNOWN_HOSTS` | *Facultative, recommandée.* Empreinte du serveur (section 3a, point 4). Sans elle, le job fait `ssh-keyscan` à chaque déploiement. | `srv.exemple.ga ssh-ed25519 AAAAC3Nza…` | Oui | Non | Variable |
| `PHP_BIN` | *Facultative.* Chemin du PHP 8.3 sur le serveur (`php` par défaut) | `/opt/cpanel/ea-php83/root/usr/bin/php` | Oui | Non | Variable |

Remarques :
- **Protégée** = disponible seulement sur les branches protégées. Il faut donc que `main` soit protégée : **Settings > Repository > Protected branches**, avec « Allowed to merge » et « Allowed to push » réservés aux Maintainers. Seuls ces Maintainers pourront lancer le déploiement.
- Une variable masquée doit faire au moins 8 caractères, sans espace : choisir une phrase de passe compatible.
- Les secrets de l'application (mot de passe MySQL, `APP_KEY`…) **ne passent pas par GitLab** : ils sont uniquement dans le `.env` du serveur (section 5).
- Les jobs de build et de tests n'utilisent aucune variable.

## 5. Fichier `.env` de production

À créer une seule fois sur le serveur dans `DEPLOY_PATH` (`cp .env.example .env`), puis à modifier. Il n'est jamais versionné, et `git reset --hard` ne le touche pas.

```dotenv
APP_NAME="CEDICOM Group"
APP_ENV=production
APP_KEY=                      # rempli par php artisan key:generate
APP_DEBUG=false
APP_URL=https://www.exemple-cedicom.ga

APP_LOCALE=fr
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=error

# Supprimer ou commenter DB_CONNECTION=sqlite et utiliser MySQL :
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cedicom_site
DB_USERNAME=cedicom
DB_PASSWORD=Exemple-MotDePasse-A-Changer

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

# E-mails des formulaires : voir DEPLOIEMENT_GITHUB.md, section 6 ter.
MAIL_MAILER=log
MAIL_FROM_ADDRESS="contact@exemple-cedicom.ga"
MAIL_FROM_NAME="${APP_NAME}"
```

Puis `php artisan key:generate`, une seule fois. Ne jamais la régénérer ensuite, sinon les sessions sont invalidées.

Le script de déploiement lit `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` et `DB_PASSWORD` dans ce `.env` pour la sauvegarde : laisser **une seule** ligne active pour chacune, et `DB_PORT` renseigné.

## 6. Premier déploiement (étapes numérotées)

1. Préparer le serveur (section 1) et créer la base (section 2).
2. Pousser le projet dans GitLab, avec `.gitlab-ci.yml` et `deploy/remote-deploy.sh`, sur la branche `main`. Protéger `main` (section 4).
3. Créer les clés SSH (section 3) et les variables CI/CD (section 4).
4. Sur le serveur, cloner le dépôt dans `DEPLOY_PATH` :
   ```bash
   git clone git@gitlab.com:votre-groupe/site_web_laravel.git /home/cedicom/site_web_laravel
   ```
5. Créer le `.env` (section 5) et lancer `php artisan key:generate`.
6. Faire pointer la racine web (DocumentRoot, ou « racine du document » du domaine dans cPanel) sur **`DEPLOY_PATH/public`**, pas sur le dossier du projet.
7. Dans GitLab, **Build > Pipelines** : le pipeline de `main` lance `build_assets`, `tests` et `pint`. Attendre que `tests` soit vert.
8. Cliquer sur le bouton ▶ du job **`deploy_production`** (job manuel). Il fait tout le reste : envoi du build, `composer install`, migrations, contenus, caches, `storage:link`, droits.
9. Faire les vérifications de la section 8.
10. Pour les déploiements suivants : pousser sur `main` (ou fusionner une merge request), attendre les tests, puis relancer ▶ `deploy_production`.

## 7. Ce que fait le pipeline

**Étape `build` : job `build_assets`** (image `node:22-alpine`)
- `npm ci` puis `npm run build` (Vite) ; `public/build/` est gardé comme artefact une semaine.

**Étape `test`**
- `tests` (image `php:8.3-cli`) : installe les extensions `zip`, `intl`, `bcmath`, Composer, `composer install`, `.env` de test, `key:generate`, puis `php artisan test tests/Feature/Site` (SQLite en mémoire, voir `phpunit.xml`). Utilise le build Vite de l'étape précédente. **Bloquant.**
- `pint` : `vendor/bin/pint --test` (style du code), **non bloquant** (`allow_failure: true`).

**Étape `deploy` : job `deploy_production`**, seulement sur `main`, **manuel**, un seul à la fois (`resource_group`), environnement GitLab `production`.
1. Prépare la clé SSH (enlève la phrase de passe dans une copie temporaire du job) et `known_hosts`.
2. Envoie `public/build/` par `rsync` dans `DEPLOY_PATH/public/build.new/`.
3. Exécute `deploy/remote-deploy.sh` sur le serveur :
   1. `php artisan down` (mode maintenance) ; `php artisan up` est garanti à la fin, même en cas d'échec (`trap … EXIT`)
   2. **Sauvegarde** `mysqldump` compressée dans `~/backups/cedicom/` (les 10 dernières sont gardées)
   3. `git fetch origin` puis `git reset --hard <commit du pipeline>`
   4. `composer install --no-dev --optimize-autoloader --no-interaction`
   5. Remplace `public/build` par `public/build.new`
   6. `php artisan migrate --force` (n'applique que les nouvelles migrations, ne vide rien)
   7. `php artisan cedicom:seed-content` : charge les contenus par défaut **seulement si la base est vide** (premier déploiement). Les modifications faites dans le back office ne sont jamais écrasées.
   8. `php artisan storage:link`, `optimize:clear`, puis `config:cache`, `route:cache`, `view:cache`
   9. Droits : `chmod -R ug+rwX storage bootstrap/cache`. **Aucun `chown`, aucun utilisateur codé en dur.**

## 8. Vérifications après déploiement

- Le job `deploy_production` se termine par `Déployé : <commit>`.
- `https://votre-domaine/` répond 200, en français ; `/langue/en` passe en anglais.
- Les pôles, marques, divisions, logos et photos des restaurants s'affichent.
- Le CSS et le JS chargent (pas de 404 sur `/build/assets/...`).
- Envoyer un message test depuis `/contact` et `/devenir-partenaire`, puis vérifier sur le serveur :
  ```bash
  php artisan tinker --execute="dump(App\Models\ContactMessage::latest()->first());"
  ```
- `APP_DEBUG=false` : une URL inexistante affiche une page 404 sans trace d'erreur.
- `storage/logs/laravel-*.log` ne contient pas de nouvelle erreur.
- Une sauvegarde récente existe : `ls -lt ~/backups/cedicom | head -3`.
- Mobile : tester en largeur de téléphone.

## 9. Retour arrière

**Le plus simple (code seulement)** : dans GitLab, **Operate > Environments > production**, choisir un déploiement précédent et cliquer **Re-deploy** (ou relancer ▶ `deploy_production` d'un ancien pipeline). Le script fait `git reset --hard` sur ce commit-là et renvoie son build Vite.

**Si la base doit aussi revenir en arrière** (migration fautive) :
1. `php artisan down`
2. Restaurer la sauvegarde prise juste avant le déploiement fautif :
   ```bash
   gunzip < ~/backups/cedicom/cedicom_site_AAAAMMJJ_HHMMSS.sql.gz | mysql -u cedicom -p cedicom_site
   ```
3. Redéployer l'ancien commit (ci-dessus), puis `php artisan up`.

**Pour que le dépôt reste aligné** : faire un `git revert` du commit fautif sur `main`, puis relancer le déploiement.

## 10. Problèmes fréquents

| Symptôme | Cause probable | Solution |
| --- | --- | --- |
| Le bouton ▶ `deploy_production` n'apparaît pas | Pipeline lancé sur une autre branche que `main` | Fusionner dans `main` |
| Job de déploiement : variables vides, `install: can't stat ''` | Variables protégées mais `main` non protégée | Protéger `main` (section 4) |
| `Load key … invalid format` / `error in libcrypto` | Clé collée sans retour à la ligne final, ou variable pas en type **Fichier** | Recréer `SSH_PRIVATE_KEY` en type Fichier, avec la ligne vide finale |
| `incorrect passphrase supplied` | `SSH_PASSPHRASE` fausse, ou créée alors que la clé n'en a pas | Corriger ou supprimer la variable |
| `Permission denied (publickey)` | Clé publique non autorisée, mauvais `SSH_USER` / `SSH_PORT` | Section 3a |
| `Host key verification failed` | `SSH_KNOWN_HOSTS` périmée (serveur réinstallé) | Refaire `ssh-keyscan`, vérifier, mettre à jour la variable |
| `rsync: command not found` côté serveur | `rsync` absent du serveur | L'installer (ou le demander à l'hébergeur) |
| `git fetch` refusé sur le serveur | Pas de deploy key en lecture, ou dossier non cloné | Section 3b |
| `mysqldump: Access denied` / `Got error: 1045` | Identifiants du `.env` faux, ou `DB_PORT` vide | Corriger le `.env` |
| `PHP Parse error` / `requires php >= 8.3` | `php` du compte SSH trop ancien | Créer `PHP_BIN` |
| Erreur 500 après déploiement | `.env` absent, `APP_KEY` vide, droits sur `storage/` | `php artisan key:generate`, `chmod -R ug+rwX storage bootstrap/cache`, lire `storage/logs` |
| 403 « Server unable to read htaccess file » | Umask du compte SSH trop strict après `git reset` | `find DEPLOY_PATH -type d -exec chmod 755 {} \;` et `-type f … 644`, puis voir l'umask du compte |
| Page sans style, `Vite manifest not found` | `public/build` absent ou racine web mal placée | Relancer le déploiement ; racine web sur `public/` |
| Modification du `.env` sans effet | Configuration en cache | `php artisan config:clear && php artisan config:cache` |
| Le site reste en maintenance | Échec de `php artisan up` (rare) | `php artisan up` à la main |
| Les tests échouent dans GitLab mais pas en local | Build Vite absent (le job `tests` dépend de `build_assets`) ou extension PHP manquante | Lire le journal du job |

## 11. Incohérences et points d'attention (non corrigés)

1. **Les workflows GitHub Actions n'ont pas été modifiés.** `.github/workflows/deploy.yml` vient d'un autre projet (un CRM « RETAIL GROUP ») : il lance `php artisan crm:refresh-lifecycle`, qui n'existe pas ici (le déploiement échouerait), fait `chown nader:nader`, cite un `CLAUDE.md` absent et annonce Azure App Service. GitLab ne lit pas ces fichiers ; ils peuvent rester, être corrigés ou être supprimés, selon votre choix.
2. **Tests Breeze cassés.** `php artisan test` complet donne 20 échecs et 2 erreurs dans `tests/Feature/Auth/*` et `tests/Feature/ProfileTest.php` : ces tests visent les routes d'authentification, volontairement désactivées. Le pipeline GitLab ne lance donc que `tests/Feature/Site`. À terme : supprimer ces tests ou réactiver l'authentification.
3. **Pint** signale `bootstrap/app.php` (ordre des imports) : d'où le job `pint` non bloquant.
4. **Les contenus se gèrent dans le back office.** Le déploiement ne recharge les contenus par défaut que si la base est vide (`cedicom:seed-content`). Ne plus lancer `php artisan db:seed --class=SiteContentSeeder` en production : cette commande efface toutes les modifications faites dans le back office.
5. **Pas de SSR** Inertia dans le projet : le pipeline n'en construit pas, c'est cohérent. Pas de worker de file non plus, d'où l'absence de `queue:restart`.
6. Le dossier local `C:\app\site_web_laravel` n'est pas encore un dépôt Git : il faudra `git init`, puis pousser vers GitLab.
7. Le pipeline n'a pas encore été exécuté sur une vraie instance GitLab : le premier lancement servira de validation.
