# LoreMundi

**Le monde en mémoire. La partie en mouvement.**

LoreMundi est le poste de pilotage du Maître de Jeu : une application Web (PWA) pour préparer, conduire et mémoriser ses campagnes de jeu de rôle, quel que soit le système de règles.

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

## En production

- PHP avec l'extension **GMP** ou **BCMath** : sans elle, les notifications push (PWA) ne partent pas. Une erreur d'envoi ne bloque jamais l'action qui l'a déclenchée.
- Un processus `php artisan reverb:start` pour le temps réel (sans lui, les pages se rafraîchissent à la prochaine action).
- `APP_ENV=production`, HTTPS, et les variables `LEGAL_*` pour la page de confidentialité.

## Tests

```sh
php artisan test
```

Les tests utilisent la base PostgreSQL `codexflow_test` (voir `phpunit.xml`).

Les tests navigateur (Playwright, `tests/Browser`) cliquent dans de vraies pages. Ils utilisent la base de développement, où ils créent leurs propres comptes `e2e-*@loremundi.test`, et lancent `php artisan serve` si aucun serveur ne tourne :

```sh
npx playwright install chromium   # une fois
npm run test:browser
```

## Documentation

- Conventions de développement : [`CLAUDE.md`](CLAUDE.md)
