<?php

return [

    // Version affichée dans l'appli. À chaque mise à jour notable : l'augmenter et décrire
    // les nouveautés dans lang/fr/changelog.php (la fenêtre « Quoi de neuf » s'affiche alors une fois).
    'version' => '0.49.0',

    // Mentions légales (page « Confidentialité et mentions légales »), renseignées dans le .env.
    'legal' => [
        'publisher' => 'Autistic Intelligence',
        'owner' => env('LEGAL_OWNER'),
        'siret' => env('LEGAL_SIRET'),
        'address' => env('LEGAL_ADDRESS'),
        'email' => env('LEGAL_EMAIL'),
        'host' => env('LEGAL_HOST'),
    ],

    // Sauvegarde de nuit (php artisan sagawyn:backup) : base PostgreSQL et fichiers envoyés,
    // gardés keep_days jours dans path. Une copie hors du serveur reste à prévoir (docs/mise-en-ligne.md).
    'backup' => [
        'enabled' => (bool) env('BACKUP_ENABLED', false),
        'path' => env('BACKUP_PATH', storage_path('backups')),
        'keep_days' => (int) env('BACKUP_KEEP_DAYS', 7),
    ],

    // Langues de l'interface, nommées dans leur propre langue. Le français est la langue source
    // des textes ; les autres ont leur traduction dans lang/{code}.json.
    'locales' => [
        'fr' => 'Français',
        'en' => 'English',
        'de' => 'Deutsch',
        'es' => 'Español',
        'it' => 'Italiano',
        'pt' => 'Português',
        'nl' => 'Nederlands',
        'pl' => 'Polski',
    ],

];
