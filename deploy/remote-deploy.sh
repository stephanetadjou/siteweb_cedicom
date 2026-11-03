#!/usr/bin/env bash
# Exécuté SUR LE SERVEUR par le workflow GitHub Deploy (ou le pipeline GitLab d exemple), via ssh ... bash -s.
# Variables reçues : DEPLOY_PATH, DEPLOY_SHA, PHP_BIN (défaut : php).
# Le build Vite a déjà été envoyé par rsync dans public/build.new.
set -euo pipefail

PHP="${PHP_BIN:-php}"
COMPOSER="$(command -v composer)"
BACKUP_DIR="${BACKUP_DIR:-$HOME/backups/cedicom}"

cd "$DEPLOY_PATH"

fix_permissions() {
    # Pas de chown : les fichiers restent au compte SSH de déploiement.
    chmod -R ug+rwX storage bootstrap/cache || true
}

# Quoi qu'il arrive, on sort du mode maintenance pour ne jamais laisser le site fermé.
trap 'fix_permissions; $PHP artisan up || true' EXIT

$PHP artisan down --retry=30 || true

# 1. Sauvegarde MySQL avant toute migration (identifiants lus dans le .env du serveur).
env_value() { grep -E "^$1=" .env | tail -n 1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'; }
if [ "$(env_value DB_CONNECTION)" = "mysql" ]; then
    mkdir -p "$BACKUP_DIR"
    BACKUP_FILE="$BACKUP_DIR/$(env_value DB_DATABASE)_$(date +%Y%m%d_%H%M%S).sql.gz"
    MYSQL_PWD="$(env_value DB_PASSWORD)" mysqldump --single-transaction --no-tablespaces \
        -h "$(env_value DB_HOST)" -P "$(env_value DB_PORT)" -u "$(env_value DB_USERNAME)" \
        "$(env_value DB_DATABASE)" | gzip > "$BACKUP_FILE"
    echo "Sauvegarde : $BACKUP_FILE"
    # On garde les 10 dernières sauvegardes.
    ls -1t "$BACKUP_DIR"/*.sql.gz | tail -n +11 | xargs -r rm -f
fi

# 2. Code : exactement le commit du pipeline (les fichiers ignorés, .env et storage, ne sont pas touchés).
git fetch --quiet origin
git reset --hard "$DEPLOY_SHA"

# 3. Dépendances PHP de production.
$PHP "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction --no-progress

# 4. Front : remplace public/build par le build envoyé par le pipeline (pas de Node sur le serveur).
if [ -d public/build.new ]; then
    rm -rf public/build.old
    [ -d public/build ] && mv public/build public/build.old
    mv public/build.new public/build
    rm -rf public/build.old
fi

# 5. Base : nouvelles migrations seulement, puis rechargement des contenus de la vitrine
#    (SiteContentSeeder ne vide que poles, partner_brands, divisions et enseignes).
$PHP artisan migrate --force
$PHP artisan db:seed --class=SiteContentSeeder --force

# 6. Lien public/storage et caches.
$PHP artisan storage:link || true
$PHP artisan optimize:clear
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache

echo "Déployé : $DEPLOY_SHA"
