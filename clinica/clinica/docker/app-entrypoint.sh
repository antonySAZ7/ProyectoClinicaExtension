#!/bin/sh
set -eu

cd /var/www/html

APP_NAME="${APP_NAME:-ClinicaExtension}"
APP_ENV="${APP_ENV:-local}"
APP_DEBUG="${APP_DEBUG:-true}"
APP_URL="${APP_URL:-http://localhost:8000}"
DB_CONNECTION="${DB_CONNECTION:-mysql}"
DB_HOST="${DB_HOST:-mysql}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-clinica_db}"
DB_USERNAME="${DB_USERNAME:-clinica_user}"
DB_PASSWORD="${DB_PASSWORD:-clinica_pass}"
SESSION_DRIVER="${SESSION_DRIVER:-file}"
CACHE_STORE="${CACHE_STORE:-file}"
QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"
FORCE_VITE_BUILD="${FORCE_VITE_BUILD:-false}"

if [ ! -f artisan ]; then
    echo "Error: Laravel project files are not available in /var/www/html."
    echo "Check the Docker bind mount source path for this project."
    ls -la
    exit 1
fi

if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
    elif [ -f .env.docker.example ]; then
        cp .env.docker.example .env
    else
        echo "Error: no .env template was found (.env.example or .env.docker.example)."
        exit 1
    fi
fi

mkdir -p \
    bootstrap/cache \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

chmod -R ug+rwX storage bootstrap/cache

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

if [ ! -x node_modules/.bin/vite ]; then
    npm install
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
until mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" --silent; do
    sleep 2
done

php artisan config:clear
php artisan migrate --force

if [ ! -f public/build/manifest.json ] || [ "$FORCE_VITE_BUILD" = "true" ]; then
    npm run build
fi

exec php artisan serve --host=0.0.0.0 --port=8000
