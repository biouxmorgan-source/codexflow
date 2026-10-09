<?php

return [

    // Version affichée dans l'appli. À chaque mise à jour notable : l'augmenter et décrire
    // les nouveautés dans lang/fr/changelog.php (la fenêtre « Quoi de neuf » s'affiche alors une fois).
    'version' => '0.42.0',

    // Mentions légales (page « Confidentialité et mentions légales »), renseignées dans le .env.
    'legal' => [
        'publisher' => 'Autistic Intelligence',
        'owner' => env('LEGAL_OWNER'),
        'siret' => env('LEGAL_SIRET'),
        'address' => env('LEGAL_ADDRESS'),
        'email' => env('LEGAL_EMAIL'),
        'host' => env('LEGAL_HOST'),
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
