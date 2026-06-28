#!/bin/bash
set -e

mkdir -p /var/www/html/var/cache /var/www/html/var/log
chmod -R 777 /var/www/html/var

if [ "$1" = 'apache2-foreground' ]; then
  until php bin/console doctrine:database:create --if-not-exists --no-interaction; do
    echo "Waiting for database..."
    sleep 2
  done

  echo "Running migrations..."
  php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
  # Load fixtures if the environment is not production
  #if [ "$APP_ENV" != 'prod' ]; then
    echo "Loading fixtures..."
    php bin/console doctrine:fixtures:load --no-interaction --purge-with-truncate
  #fi
fi

exec "$@"
