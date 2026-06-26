# Biblioteka API

Prosty projekt Symfony 8.1 z PostgreSQL i Docker Compose.

## Uruchomienie

1. `docker compose up --build`
2. Aplikacja będzie dostępna pod `http://localhost:8000`
3. Dmyślnie dodawanych jest kilka książek na róznym statusie.

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

Dokumentacja endpointów dostępna pod `/api/doc`.

## Architektura
Architektura jest bardzo prosta (nie to było celem zadania). Celowo pominięty został obszar cachowania (wykorzystałnym Redis).
