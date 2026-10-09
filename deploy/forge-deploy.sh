# Script de déploiement LoreMundi pour Laravel Forge.
# À coller dans Forge : site → Deployments → Deploy script (remplace le script proposé).
# Il tourne à chaque « Deploy now » et à chaque push sur main si le déploiement
# automatique est activé. Guide complet : docs/mise-en-ligne.md.
set -e

cd $FORGE_SITE_PATH
git pull origin $FORGE_SITE_BRANCH

# Dépendances PHP sans les outils de développement.
$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Interface : compilée sur le serveur, après le .env (les réglages VITE_REVERB_* y sont lus).
npm ci --no-audit --no-fund
npm run build

( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service $FORGE_PHP_FPM reload ) 9>/tmp/fpmlock

# Base, caches, puis redémarrage des démons pour qu'ils chargent le nouveau code.
$FORGE_PHP artisan migrate --force
$FORGE_PHP artisan optimize
$FORGE_PHP artisan queue:restart
$FORGE_PHP artisan reverb:restart

# Liste ce qui reste à régler (n'arrête pas le déploiement).
$FORGE_PHP artisan loremundi:check || true
