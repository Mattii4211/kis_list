# Biblioteka API

Prosty projekt Symfony 8.1 z PostgreSQL i Docker Compose.

## Uruchomienie

1. `docker compose -f docker-compose.yml up --build`
2. Aplikacja będzie dostępna pod `http://localhost:8000`

> Root `/` przekierowuje do `/api/books`.

## Dostępne endpointy

- `GET /api/books` - lista książek
- `POST /api/books` - dodanie nowej książki
- `DELETE /api/books/{serialNumber}` - usunięcie książki
- `PATCH /api/books/{serialNumber}/borrow` - wypożyczenie książki
- `PATCH /api/books/{serialNumber}/return` - zwrot książki

## Baza danych

PostgreSQL uruchamiany jako kontener Docker.
Dane są przechowywane w wolumenie `db_data`, więc wytrwają restart kontenera.

## Dokumentacja

Jeśli `nelmio/api-doc-bundle` jest aktywny, dokumentacja może być dostępna pod `/api/docs`.

## Uwaga

Jeśli chcesz wykonać migracje lokalnie, uruchom najpierw tylko bazę:

`docker compose -f docker-compose.yml up -d db`

Następnie w katalogu projektu:

`php bin/console doctrine:migrations:diff`
`php bin/console doctrine:migrations:migrate`
