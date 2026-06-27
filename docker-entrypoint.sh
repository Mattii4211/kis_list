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

  BOOK_COUNT=$(php bin/console doctrine:query:sql "SELECT COUNT(id) FROM books" 2>/dev/null | tr -d '[:space:]' || echo "0")
  if [ "$BOOK_COUNT" = "0" ]; then
    php bin/console doctrine:fixtures:load --no-interaction --purge-with-truncate
  else
    echo "Books table already contains data, skipping fixtures."
  fi
fi

echo "===== DEBUG ====="
echo "===== APACHE TEST ====="
su -s /bin/sh www-data -c "whoami"
su -s /bin/sh www-data -c "touch /var/www/html/var/cache/prod/test-www-data.txt && echo OK || echo FAIL"
su -s /bin/sh www-data -c "php -r 'rename(\"/tmp/testfile\",\"/var/www/html/var/cache/prod/testfile\");'" || true
php bin/console debug:container --parameter=kernel.cache_dir
whoami
id
ls -ld /var/www/html
ls -ld /var/www/html/var
ls -ld /var/www/html/var/cache
ls -ld /var/www/html/var/cache/prod || true
touch /var/www/html/var/cache/test.txt && echo "WRITE OK" || echo "WRITE FAILED"

exec "$@"
