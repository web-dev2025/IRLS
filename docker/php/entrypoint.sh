#!/bin/sh
set -e

cd /var/www/html

# ---- INIT ONLY ONCE ----
if [ ! -f storage/.app_initialized ]; then

  echo "🚀 Initializing application..."

  # .env
  if [ ! -f .env ]; then
    cp .env.example .env
  fi

  php artisan key:generate --force

  mkdir -p database
  touch database/database.sqlite

  composer install --no-interaction --prefer-dist
  npm install
  npm run build

  php artisan migrate --force

  touch storage/.app_initialized

  echo "✅ Initialization complete"
fi

# ---- MODE SWITCH ----
if [ "$RUN_QUEUE" = "1" ]; then
  echo "⚙️ Starting queue..."
  exec php artisan queue:work --sleep=3 --tries=3
fi

echo "🌐 Starting app..."
exec php artisan serve --host=0.0.0.0 --port=80