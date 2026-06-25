#!/bin/bash
set -e

if [ "$1" = 'apache2-foreground' ]; then
  until php bin/console doctrine:database:create --if-not-exists --no-interaction; do
    echo "Waiting for database..."
    sleep 2
  done

  php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

  BOOK_COUNT=$(php bin/console doctrine:query:sql "SELECT COUNT(*) FROM books" 2>/dev/null | tr -d '[:space:]' || echo "0")
  if [ "$BOOK_COUNT" = "0" ]; then
    php bin/console doctrine:fixtures:load --no-interaction --purge-with-truncate
  else
    echo "Books table already contains data, skipping fixtures."
  fi
fi

exec "$@"
