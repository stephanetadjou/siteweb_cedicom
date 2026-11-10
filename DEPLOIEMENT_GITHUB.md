# Déploiement du site CEDICOM avec GitHub Actions : guide pas à pas

Ce guide décrit comment mettre en ligne le site vitrine CEDICOM (Laravel 13 + React via Inertia + Vite + Tailwind 4) sur un serveur Linux, avec GitHub Actions.

> **État actuel : les nouveaux workflows ne sont pas encore en place.**
> Les versions corrigées pour ce site sont dans `deploy/github/tests.yml` et `deploy/github/deploy.yml`. Les fichiers actifs `.github/workflows/tests.yml` et `.github/workflows/deploy.yml` sont encore ceux d'origine (venus d'un autre projet, voir section 11). Pour activer les nouveaux, copier les deux fichiers de `deploy/github/` dans `.github/workflows/` en remplaçant les anciens (section 6, étape 2).

Fichiers concernés :

| Fichier | Rôle |
| --- | --- |
| `.github/workflows/tests.yml` | Tests à chaque push ou pull request sur `main` et `staging` ; build Vite gardé en artefact |
| `.github/workflows/deploy.yml` | Déploiement manuel en SSH, après les tests |
| `deploy/remote-deploy.sh` | Script exécuté **sur le serveur** par le déploiement |

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
| Node.js | **Inutile sur le serveur** : le build Vite est fait par GitHub (Node 22) puis envoyé par `rsync` |
| Dossier du site | Un **clone Git** du dépôt GitHub (le déploiement fait `git fetch` puis `git reset --hard <commit>`) |
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

**a) Clé de déploiement (GitHub → serveur)**

1. Sur votre poste :
   ```bash
   ssh-keygen -t ed25519 -C "github-deploy-cedicom" -f deploy_cedicom
   ```
   Vous obtenez `deploy_cedicom` (clé **privée**) et `deploy_cedicom.pub` (clé **publique**). Une phrase de passe est possible ; elle ira dans `SSH_PASSPHRASE`.
2. Ajoutez le contenu de `deploy_cedicom.pub` dans `~/.ssh/authorized_keys` du compte de déploiement sur le serveur (ou cPanel > Accès SSH > Gérer les clés > Importer, puis **Autoriser**).
3. Collez tout le contenu de `deploy_cedicom`, de `-----BEGIN OPENSSH PRIVATE KEY-----` à `-----END OPENSSH PRIVATE KEY-----`, dans le secret `SSH_PRIVATE_KEY` (section 4).
4. Récupérez l'empreinte du serveur pour `SSH_KNOWN_HOSTS` (recommandé, évite de faire confiance aveuglément au serveur) :
   ```bash
   ssh-keyscan -p 22 srv.exemple.ga
   ```
   Vérifiez-la, puis collez la sortie dans le secret `SSH_KNOWN_HOSTS`.
5. Supprimez `deploy_cedicom` de votre poste une fois le secret créé (ou rangez-le dans un coffre de mots de passe).

**b) Clé de lecture du dépôt (serveur → GitHub)**

Le serveur fait `git fetch` : il doit pouvoir lire le dépôt.

1. Sur le serveur, avec le compte de déploiement : `ssh-keygen -t ed25519 -C "serveur-cedicom"` (sans phrase de passe).
2. Dans GitHub : **Settings > Deploy keys > Add deploy key**, collez `~/.ssh/id_ed25519.pub`, **sans** cocher « Allow write access ».
3. Test : `ssh -T git@github.com` doit répondre « Hi … You've successfully authenticated ».

## 4. Secrets à créer dans GitHub

**Où :** dépôt GitHub > **Settings > Secrets and variables > Actions**.

Deux possibilités, au choix :
- **Secrets de l'environnement `production`** (recommandé) : **Settings > Environments > New environment** > nom `production`, puis « Environment secrets > Add secret ». Le workflow utilise `environment: production`. On peut y ajouter des **Required reviewers** (le déploiement attend une validation) et limiter les branches à `main` (« Deployment branches and tags > Selected branches > main »).
- **Secrets du dépôt** : onglet **Secrets > New repository secret**. Plus simple, mais disponibles pour tous les workflows du dépôt.

Noms exacts utilisés par `deploy.yml` :

| Nom exact | Sorte | Rôle | Exemple fictif | Obligatoire |
| --- | --- | --- | --- | --- |
| `SSH_HOST` | Secret | Adresse du serveur | `203.0.113.10` ou `srv.exemple.ga` | Oui |
| `SSH_PORT` | Secret | Port SSH (22 si vide) | `22` | Non (22 par défaut) |
| `SSH_USER` | Secret | Compte SSH de déploiement | `cedicom` | Oui |
| `SSH_PRIVATE_KEY` | Secret | Clé privée de la section 3a, multiligne, collée telle quelle | `-----BEGIN OPENSSH PRIVATE KEY----- …` | Oui |
| `SSH_PASSPHRASE` | Secret | Phrase de passe de la clé. Ne pas créer si la clé n'en a pas. | `exemple-phrase-longue-2026` | Non |
| `DEPLOY_PATH` | Secret | Dossier du site sur le serveur (clone Git) | `/home/cedicom/site_web_laravel` | Oui |
| `SSH_KNOWN_HOSTS` | Secret | Empreinte du serveur (section 3a, point 4). Sans elle, le workflow fait `ssh-keyscan` à chaque déploiement. | `srv.exemple.ga ssh-ed25519 AAAAC3Nza…` | Non, recommandé |
| `PHP_BIN` | **Variable** (onglet *Variables*, pas *Secrets*) | Chemin du PHP 8.3 sur le serveur | `/opt/cpanel/ea-php83/root/usr/bin/php` | Non (`php` par défaut) |

Remarques :
- Les secrets GitHub sont toujours masqués dans les journaux et ne sont pas transmis aux workflows lancés depuis une fork.
- Les secrets de l'application (mot de passe MySQL, `APP_KEY`…) **ne passent pas par GitHub** : ils sont uniquement dans le `.env` du serveur (section 5).
- `tests.yml` n'utilise aucun secret.
- Protéger `main` : **Settings > Branches > Add branch ruleset** (pull request obligatoire, statut « Tests » requis).

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

# E-mails : voir la section 6 ter.
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=mail.cedicomgroup.com
MAIL_PORT=465
MAIL_USERNAME=info@cedicomgroup.com
MAIL_PASSWORD=Exemple-MotDePasse-Boite
MAIL_FROM_ADDRESS="info@cedicomgroup.com"
MAIL_FROM_NAME="CEDICOM Group"
MAIL_NOTIFY_TO=info@cedicomgroup.com

# Anti-spam : voir la section 6 quater.
RECAPTCHA_SITE_KEY=cle_du_site_fournie_par_google
RECAPTCHA_SECRET_KEY=cle_secrete_fournie_par_google
RECAPTCHA_MIN_SCORE=0.5
ANTISPAM_ALERTS_PER_HOUR=30
```

Puis `php artisan key:generate`, une seule fois. Ne jamais la régénérer ensuite, sinon les sessions sont invalidées.

Le script de déploiement lit `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` et `DB_PASSWORD` dans ce `.env` pour la sauvegarde : laisser **une seule** ligne active pour chacune, et `DB_PORT` renseigné.

## 6. Premier déploiement (étapes numérotées)

1. Préparer le serveur (section 1) et créer la base (section 2).
2. Activer les nouveaux workflows : copier `deploy/github/tests.yml` et `deploy/github/deploy.yml` dans `.github/workflows/` (en remplaçant les anciens), puis `git init` si besoin, commit et push sur `main`.
3. Créer les clés SSH (section 3), l'environnement `production` et les secrets (section 4).
4. Sur le serveur, cloner le dépôt dans `DEPLOY_PATH` :
   ```bash
   git clone git@github.com:votre-compte/site_web_laravel.git /home/cedicom/site_web_laravel
   ```
5. Créer le `.env` (section 5) et lancer `php artisan key:generate`.
6. Faire pointer la racine web (DocumentRoot, ou « racine du document » du domaine dans cPanel) sur **`DEPLOY_PATH/public`**, pas sur le dossier du projet.
7. Vérifier dans l'onglet **Actions** que le workflow **Tests** du dernier push est vert.
8. **Actions > Deploy > Run workflow**, branche `main`, puis **Run workflow**. Si l'environnement a des relecteurs, valider avec « Review deployments ».
9. Faire les vérifications de la section 8.
10. Pour les déploiements suivants : fusionner sur `main`, puis relancer **Run workflow**.

## 6 bis. Compte administrateur du back office (/admin)

Le site a un back office simple sur `https://votre-domaine/admin` : tableau de bord, demandes de partenariat et messages de contact (recherche, statut nouveau / traité, détail, export CSV). Il n'y a pas d'inscription publique : les comptes se créent sur le serveur.

1. Après le premier déploiement, dans `DEPLOY_PATH` :
   ```bash
   php artisan cedicom:admin direction@exemple-cedicom.ga --name="Direction CEDICOM"
   ```
2. La commande affiche **une seule fois** un mot de passe aléatoire : le noter tout de suite (gestionnaire de mots de passe). Il n'est stocké que sous forme chiffrée.
3. Se connecter sur `/admin/login` avec cette adresse et ce mot de passe.
4. Mot de passe perdu : relancer la même commande avec la même adresse ; un nouveau mot de passe est généré et l'ancien ne fonctionne plus.

## 6 ter. E-mails des formulaires

À chaque message de contact et à chaque demande de partenariat, le site envoie une alerte à `MAIL_NOTIFY_TO` (`info@cedicomgroup.com` par défaut), avec tous les champs et un bouton vers le back office. « Répondre » dans la messagerie écrit directement au visiteur.

La demande est enregistrée avant l'envoi : si le SMTP est en panne, le formulaire fonctionne quand même, l'erreur est écrite dans `storage/logs` et la demande reste visible dans le back office. L'envoi est direct, sans file d'attente, avec une limite de 10 secondes.

1. Dans cPanel > Comptes de messagerie, créer la boîte `info@cedicomgroup.com`. « Connect Devices » donne le serveur sortant et le port.
2. Dans le `.env` du serveur, renseigner le bloc `MAIL_*` de la section 5. Port 465 : `MAIL_SCHEME=smtps`. Port 587 : `MAIL_SCHEME=smtp`. `MAIL_FROM_ADDRESS` doit être identique à `MAIL_USERNAME`. Mettre `MAIL_PASSWORD` entre guillemets s'il contient `#` ou un espace.
3. Prendre en compte le `.env` : `php artisan config:clear` puis `php artisan config:cache`.
4. Tester en envoyant un message depuis `/contact`. Si rien n'arrive, chercher « Échec de l'envoi » dans `storage/logs/laravel-*.log`.
5. Dans cPanel > Délivrabilité des e-mails, vérifier que SPF et DKIM sont valides pour `cedicomgroup.com`, sinon les e-mails risquent d'arriver en spam.

## 6 quater. Protection anti-spam des formulaires

L'hébergeur a signalé que le formulaire de contact servait à envoyer du spam. Les formulaires de contact, de partenariat et de connexion au back office sont maintenant protégés par plusieurs barrières :

| Protection | Effet |
| --- | --- |
| Google reCAPTCHA v3 (invisible) | Google donne un score de 0 (robot) à 1 (humain) ; en dessous de `RECAPTCHA_MIN_SCORE` (0.5), l'envoi est rejeté. Aucune case à cocher pour le visiteur. |
| Champ piège et délai minimum | Un champ invisible rempli, ou un envoi moins de 3 secondes après l'affichage du formulaire (jeton horodaté signé), trahit un robot. |
| Rejet silencieux | Un envoi rejeté reçoit le même message de succès qu'un vrai : rien n'est enregistré ni envoyé, le robot ne sait pas qu'il est bloqué. Chaque rejet est noté dans `storage/logs` (« Formulaire rejeté (anti-spam) »). |
| Limitation de débit | 3 envois par 10 minutes, par adresse IP et par formulaire ; 5 tentatives de connexion par minute au back office. |
| Validation stricte | Pas de retour à la ligne dans les noms et l'e-mail (anti-injection d'en-têtes), pas de HTML, aucun lien dans les noms et 2 liens au maximum dans le message, domaine de l'e-mail vérifié, longueurs limitées. |
| E-mails | Les alertes partent uniquement vers `MAIL_NOTIFY_TO`. L'adresse du visiteur n'est jamais destinataire (seulement en « Répondre à ») et aucun accusé de réception automatique n'est envoyé. Au-delà de `ANTISPAM_ALERTS_PER_HOUR` alertes par heure, les e-mails s'arrêtent mais les demandes restent dans le back office. |

**Créer les clés reCAPTCHA (une seule fois) :**
1. Ouvrir https://www.google.com/recaptcha/admin avec un compte Google de l'entreprise, puis « + » (créer).
2. Libellé : `CEDICOM site`. Type : **reCAPTCHA v3** (basé sur un score).
3. Domaines : `cedicom-group.com` (couvre aussi `www.cedicom-group.com`). Ajouter `localhost` seulement pour tester en local.
4. Valider : Google affiche la **clé du site** et la **clé secrète**.
5. Dans le `.env` du serveur : `RECAPTCHA_SITE_KEY=` (clé du site) et `RECAPTCHA_SECRET_KEY=` (clé secrète). La clé secrète ne doit jamais être publiée ni envoyée par e-mail.
6. `php artisan config:clear` puis `php artisan config:cache`.
7. Vérifier : sur `/contact`, le petit badge reCAPTCHA apparaît en bas à droite ; envoyer un message test.

Sans ces deux clés, reCAPTCHA est simplement désactivé (les autres protections restent actives). Si de vrais visiteurs sont bloqués, baisser `RECAPTCHA_MIN_SCORE` à `0.3` ; s'il reste du spam, le monter à `0.7`. Le tableau de bord reCAPTCHA de Google montre la répartition des scores.

## 6 quinquies. Gestion des contenus dans le back office

Dans `/admin`, la rubrique **Contenus du site** permet de modifier sans toucher au code :

| Écran | Ce qu'on y modifie |
| --- | --- |
| Paramètres du site | Téléphones, e-mail, adresse, réseaux sociaux (Facebook, Instagram, LinkedIn, WhatsApp), chiffres clés (m², camions, années), grand titre et paragraphe d'introduction de l'accueil en FR et EN. Un champ laissé vide reprend le texte par défaut. |
| Diaporama de l'accueil | Photos du haut de l'accueil : ajout, remplacement, ordre, voile léger ou fort, cadrage, texte alternatif FR/EN, affichage oui/non. |
| Pôles d'activité | Cartes « Nos pôles d'activité » : titre, description, ligne de marques, photo, lien. |
| Divisions | Titre, description, photo, catégories d'activité. |
| Enseignes | Logos des franchises et concepts propres de chaque division. |
| Marques partenaires | Logo, catégorie, ordre, affichage oui/non, mise en avant (« Partenaire majeur », affichée en premier). |

Chaque liste a des flèches ↑ ↓ pour l'ordre, un crayon pour modifier et une corbeille pour supprimer (avec confirmation). Les changements sont visibles immédiatement sur le site (le cache est vidé à chaque modification).

**Images :** JPG, PNG ou WebP, 5 Mo maximum. Elles sont réduites automatiquement (1920 px pour les photos, 600 px pour les logos) et rangées dans `storage/app/public/uploads`, servies par l'adresse `/storage/…`. Les images livrées avec le site (`public/images`) restent en place et ne sont jamais supprimées.

**Sur le serveur, une seule fois :**
1. `php artisan storage:link` (le déploiement le fait aussi) : sans ce lien, les images envoyées depuis le back office ne s'affichent pas. Sous cPanel, si la commande est refusée, créer le lien à la main : `ln -s ../storage/app/public public/storage` dans le dossier du site.
2. Droits d'écriture sur `storage/` pour l'utilisateur du serveur web.
3. `php artisan migrate --force` puis `php artisan cedicom:seed-content` : crée les nouvelles tables et ajoute le diaporama par défaut, **sans toucher aux pôles, divisions et marques déjà en base**.

**Important :** ne plus lancer `php artisan db:seed --class=SiteContentSeeder` en production. Cette commande remet tous les contenus par défaut et efface les modifications faites dans le back office.

## 7. Ce que font les workflows

**`tests.yml`** (push et pull request sur `main` et `staging`, et avant chaque déploiement)
- PHP 8.3, **Node 22**, `composer install`, `npm ci`, `npm run build`, base SQLite, `key:generate`, `migrate --force`.
- `pint --test` : **non bloquant** (`continue-on-error`).
- `php artisan test tests/Feature/Site tests/Feature/Admin` : **tests du site et du back office**. Les tests Breeze (`tests/Feature/Auth`, `tests/Feature/ProfileTest.php`) restent dans le dépôt mais ne sont plus lancés (section 11).
- Envoie `public/build/` comme artefact `vite-build` (7 jours).

**`deploy.yml`** (manuel uniquement, branche `main`, un seul déploiement à la fois, environnement `production`)
1. Relance `tests.yml` ; arrêt si un test échoue.
2. Récupère l'artefact `vite-build`.
3. Prépare la clé SSH (enlève la phrase de passe dans une copie temporaire, supprimée à la fin) et `known_hosts`.
4. Envoie `public/build/` par `rsync` dans `DEPLOY_PATH/public/build.new/`.
5. Exécute `deploy/remote-deploy.sh` sur le serveur :
   1. `php artisan down` (mode maintenance) ; `php artisan up` est garanti à la fin, même en cas d'échec (`trap … EXIT`)
   2. **Sauvegarde** `mysqldump` compressée dans `~/backups/cedicom/` (les 10 dernières sont gardées)
   3. `git fetch origin` puis `git reset --hard <commit du workflow>`
   4. `composer install --no-dev --optimize-autoloader --no-interaction`
   5. Remplace `public/build` par `public/build.new`
   6. `php artisan migrate --force` (n'applique que les nouvelles migrations, ne vide rien)
   7. `php artisan cedicom:seed-content` : charge les contenus par défaut **seulement si la base est vide** (premier déploiement). Les modifications faites dans le back office ne sont jamais écrasées.
   8. `php artisan storage:link`, `optimize:clear`, puis `config:cache`, `route:cache`, `view:cache`
   9. Droits : `chmod -R ug+rwX storage bootstrap/cache`. **Aucun `chown`, aucun utilisateur codé en dur.**

## 8. Vérifications après déploiement

- Le job **Deploy to production (SSH)** se termine par `Déployé : <commit>`.
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

**Code seulement** : faire un `git revert` du commit fautif sur `main`, pousser, puis **Run workflow**. (Relancer un ancien run avec « Re-run all jobs » redéploie aussi son commit, tant que son artefact `vite-build` n'a pas expiré, soit 7 jours.)

**En urgence, directement sur le serveur** :
```bash
cd /home/cedicom/site_web_laravel
php artisan down
git log --oneline -5
git reset --hard <hash_du_commit_precedent>
composer install --no-dev --optimize-autoloader --no-interaction
php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```
Attention : `public/build` reste celui du dernier déploiement ; si le front a changé, relancer plutôt le workflow sur le bon commit.

**Si la base doit aussi revenir en arrière** (migration fautive) :
```bash
php artisan down
gunzip < ~/backups/cedicom/cedicom_site_AAAAMMJJ_HHMMSS.sql.gz | mysql -u cedicom -p cedicom_site
php artisan up
```

## 10. Problèmes fréquents

| Symptôme | Cause probable | Solution |
| --- | --- | --- |
| Le job deploy est « skipped » | Workflow lancé sur une autre branche que `main` | Choisir `main` dans « Run workflow » |
| Le job attend « Waiting for review » | Relecteurs obligatoires sur l'environnement `production` | Un relecteur clique « Review deployments » |
| Variables vides, `ssh: Could not resolve hostname` | Secrets créés dans un autre environnement, ou nom mal orthographié | Vérifier les noms exacts (section 4) |
| `Load key … invalid format` / `error in libcrypto` | Clé privée tronquée ou mal collée | Recoller toute la clé dans `SSH_PRIVATE_KEY` |
| `incorrect passphrase supplied` | `SSH_PASSPHRASE` fausse, ou créée alors que la clé n'en a pas | Corriger ou supprimer le secret |
| `Permission denied (publickey)` | Clé publique non autorisée, mauvais `SSH_USER` / `SSH_PORT` | Section 3a |
| `Host key verification failed` | `SSH_KNOWN_HOSTS` périmée (serveur réinstallé) | Refaire `ssh-keyscan`, vérifier, mettre à jour le secret |
| `Artifact not found: vite-build` | Le job de tests n'a pas envoyé le build | Lire le journal du job Tests |
| `rsync: command not found` | `rsync` absent du serveur | L'installer (ou le demander à l'hébergeur) |
| `git fetch` refusé sur le serveur | Pas de deploy key en lecture, ou dossier non cloné | Section 3b |
| `mysqldump: Access denied` / `Got error: 1045` | Identifiants du `.env` faux, ou `DB_PORT` vide | Corriger le `.env` |
| `requires php >= 8.3` | `php` du compte SSH trop ancien | Créer la variable `PHP_BIN` |
| Erreur 500 après déploiement | `.env` absent, `APP_KEY` vide, droits sur `storage/` | `php artisan key:generate`, `chmod -R ug+rwX storage bootstrap/cache`, lire `storage/logs` |
| 403 « Server unable to read htaccess file » | Umask du compte SSH trop strict après `git reset` | `find DEPLOY_PATH -type d -exec chmod 755 {} \;` et `-type f … 644`, puis voir l'umask du compte |
| Page sans style, `Vite manifest not found` | `public/build` absent ou racine web mal placée | Relancer le déploiement ; racine web sur `public/` |
| Modification du `.env` sans effet | Configuration en cache | `php artisan config:clear && php artisan config:cache` |
| Le site reste en maintenance | Échec de `php artisan up` (rare) | `php artisan up` à la main |

## 11. Ce qui a changé par rapport aux anciens workflows, et points d'attention

Retiré de l'ancien `deploy.yml` (copié d'un CRM « RETAIL GROUP ») :
- `php artisan crm:refresh-lifecycle` : commande inexistante dans ce site, qui faisait échouer le déploiement ;
- `chown -R nader:nader` : utilisateur d'un autre serveur ;
- le chemin PHP cPanel codé en dur (`/opt/cpanel/ea-php83/...`), remplacé par la variable facultative `PHP_BIN` ;
- la mention d'une migration vers Azure App Service et du `CLAUDE.md` ;
- `npm ci` / `npm run build` sur le serveur (le build vient maintenant de GitHub) et `queue:restart` (aucun worker de file sur la vitrine).

Ajouté : sauvegarde `mysqldump` avant `migrate --force`, `cedicom:seed-content` (contenus par défaut si la base est vide), déploiement du commit exact testé, un seul déploiement à la fois, environnement `production`.

Points d'attention :
1. **Tests Breeze exclus, pas supprimés.** `php artisan test` complet donne 20 échecs et 2 erreurs dans `tests/Feature/Auth/*` et `tests/Feature/ProfileTest.php` : ces tests visent les routes d'authentification, volontairement désactivées. À terme : les supprimer ou réactiver l'authentification.
2. **Pint** : `bootstrap/app.php` et `app/Models/Division.php` ont été remis au style Pint ; `pint --test` passe. L'étape reste non bloquante.
3. **Les contenus se gèrent dans le back office.** Le déploiement ne recharge les contenus par défaut que si la base est vide (`cedicom:seed-content`). Ne plus lancer `php artisan db:seed --class=SiteContentSeeder` en production : cette commande efface toutes les modifications faites dans le back office.
4. **Pas de SSR** Inertia dans le projet : rien à construire de ce côté.
5. Le dossier local `C:\app\site_web_laravel` n'est pas encore un dépôt Git.
6. Les workflows n'ont pas encore tourné sur GitHub : le premier lancement servira de validation.
