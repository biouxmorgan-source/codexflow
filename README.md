# CodexFlow

**Le monde en mémoire. La partie en mouvement.**

CodexFlow est le poste de pilotage du Maître de Jeu : une application Web (PWA) pour préparer, conduire et mémoriser ses campagnes de jeu de rôle, quel que soit le système de règles.

## Démarrer en local

Prérequis : PHP 8.3+, Composer, Node 22+, PostgreSQL 16.

```sh
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan webpush:vapid   # notifications push, facultatif
php artisan migrate --seed
composer run dev
```

Puis ouvrir http://localhost:8000 et se connecter avec `mj@codexflow.test` / `password`.

## Tests

```sh
php artisan test
```

Les tests utilisent la base PostgreSQL `codexflow_test` (voir `phpunit.xml`).

## Documentation

- Conventions de développement : [`CLAUDE.md`](CLAUDE.md)
