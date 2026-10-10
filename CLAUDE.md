# SagaWyn (anciennement LoreMundi, à l'origine CodexFlow)

Assistant Web/PWA pour Maître de Jeu, édité par Autistic Intelligence (devise « Every world has a story. »), indépendant de tout système de règles. La référence fonctionnelle est le cahier des charges V1.1, conservé dans les fichiers du projet Claude (`cadrage/CodexFlow_Cahier_des_charges_V1.1_FR.docx`).

## Stack

- Laravel 13, PHP 8.3+, PostgreSQL 16 (JSONB, recherche plein texte en français)
- Livewire 4 (composants en classe dans `app/Livewire`, vues dans `resources/views/livewire`) + Alpine.js, Tailwind CSS 4
- Fortify pour l'authentification (vues dans `resources/views/auth`)
- Prévu : Tiptap (liens `[[ ]]`), pdf.js (feuilles PDF), Laravel Reverb (temps réel, lot 3)

## Commandes

```sh
composer install && npm install
cp .env.example .env && php artisan key:generate && php artisan webpush:vapid
php artisan migrate --seed        # compte démo : mj@codexflow.test / password
composer run dev                  # serveur + Vite
php artisan test                  # tests (PostgreSQL, base codexflow_test)
npm run test:browser              # tests navigateur Playwright (tests/Browser, base de dev)
vendor/bin/pint                   # style
php artisan sagawyn:check       # installation prête pour la production ? (guide : docs/mise-en-ligne.md)
```

## Règles du projet

- **Aucune colonne propre à un jeu** (force, PV, classe, race…) dans le cœur. Ces notions viendront par templates/packs.
- **Visibilité vérifiée côté serveur, toujours** (policies Laravel). Ne jamais se reposer sur le masquage d'interface.
- **Fiches en deux zones** : publique (`summary`, `description`) et MJ (`gm_notes`, masqué à la sérialisation).
- **Connaissances et possessions appartiennent au personnage**, pas au joueur.
- **Rôle par campagne** (`campaign_memberships.role` : `gm` ou `player`), un seul type de compte.
- **Une entité appartient soit à un monde, soit à une campagne** (contrainte SQL). Les différences propres à une campagne vont dans `campaign_entity_states`, jamais dans la fiche mondiale.
- Recherche, liens internes, journal et notifications appliquent les mêmes règles de visibilité.
- Interface en français ; textes destinés à la traduction dans `lang/`.
- Chaque parcours de recette du cahier des charges doit avoir un test automatisé.

## Lots

1. Le MJ seul : mondes, entités, scènes, mode Session, moteur de contexte, recherche.
2. Les joueurs : invitations, personnages, révélations, « Donner », journal.
3. Le lien vivant : messagerie, notifications temps réel, PWA.
