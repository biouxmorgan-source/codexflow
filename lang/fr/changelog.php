<?php

// Nouveautés par version, de la plus récente à la plus ancienne. Affichées dans
// « Quoi de neuf » (une fois après chaque mise à jour) et sur la page du même nom.
return [

    '0.13.1' => [
        'date' => '2026-10-08',
        'title' => 'Corrections de la recette V1',
        'items' => [
            'La recherche du MJ trouve aussi les secrets, les informations et objets donnés, les notes partagées des joueurs et les tags de scène.',
            'Dupliquer une campagne copie ses secrets et sa chronologie préparée ; un lien vers une fiche ouvert en session s’affiche dans un panneau, sans quitter la session.',
            'Pages d’erreur traduites, courriel de mot de passe en français, Mode Session et Documents lisibles sur téléphone, menu pour les liens masqués sur petit écran.',
            'Un personnage au repos et un objet validé par le MJ ne sont plus modifiables par le joueur ; la règle temporaire de la carte s’efface d’elle-même.',
        ],
    ],

    '0.13.0' => [
        'date' => '2026-10-08',
        'title' => 'Votre propre IA, sans copier-coller',
        'items' => [
            'Dans « Préférences », vous pouvez enregistrer une clé d’API à votre nom chez Claude (Anthropic), ChatGPT (OpenAI) ou Le Chat (Mistral). L’assistant IA propose alors « Analyser directement » : les propositions arrivent sans copier-coller.',
            'Les appels sont facturés par le fournisseur sur votre compte. La clé est chiffrée, jamais réaffichée ni exportée, et le mode « texte à coller » reste gratuit.',
        ],
    ],

    '0.12.0' => [
        'date' => '2026-10-08',
        'title' => 'Un assistant IA, sans abonnement',
        'items' => [
            'Nouvel outil « Assistant IA » dans la campagne : CodexFlow prépare un texte avec les notes de la séance et le contexte de la campagne, à coller dans l’IA de votre choix. Sa réponse, collée en retour, devient des propositions : résumé, événements joués, relations, statuts, notes de campagne, révélations.',
            'Chaque proposition s’accepte, se modifie ou se rejette. Rien ne change dans la campagne sans vous, et les relations ou statuts proposés restent propres à la campagne, sans toucher au monde partagé.',
        ],
    ],

    '0.11.1' => [
        'date' => '2026-10-08',
        'title' => 'La démonstration dans votre langue',
        'items' => [
            'La campagne de démonstration existe dans les huit langues de l’interface. Elle se charge dans la vôtre, ou dans celle choisie à côté du bouton.',
        ],
    ],

    '0.11.0' => [
        'date' => '2026-10-08',
        'title' => 'Une campagne de démonstration',
        'items' => [
            'Campagne de démonstration à charger en un clic depuis « Mes campagnes » : un jeu inventé, « Brume & Serment », et une intrigue complète de trois séances, avec fiches, portraits, relations, carte, secrets, règles, chronologie et prétirés.',
            'Les petits boutons à icône de la page de campagne ne bougent plus au survol : le nom s’affiche en infobulle, au-dessus du reste.',
            'Les tags se saisissent de la même façon partout, et une fiche propose « Ajouter un tag » directement sous son titre.',
        ],
    ],

    '0.10.0' => [
        'date' => '2026-10-07',
        'title' => 'Une page de campagne plus claire',
        'items' => [
            'Page de campagne remise en ordre : le mode Session en bandeau, quatre zones de préparation, et les autres outils en petits boutons à icône.',
            'Ambiance de l’écran de table, au choix depuis la télécommande : Nuit, Parchemin, Ardoise ou Grimoire.',
        ],
    ],

    '0.9.0' => [
        'date' => '2026-10-07',
        'title' => 'Emporter sa campagne',
        'items' => [
            'Exporter une campagne entière dans une archive .zip : jeu, monde, fiches, scénarios, documents, cartes, secrets, chronologie et fichiers.',
            'Importer une archive depuis « Mes campagnes » : elle recrée la campagne, chez vous ou chez un autre MJ.',
            'Modèles de jeu partageables : types de fiche, champs, étiquettes et règles, sans aucun contenu de campagne.',
        ],
    ],

    '0.8.0' => [
        'date' => '2026-10-07',
        'title' => 'Le graphe et la chronologie',
        'items' => [
            'Graphe des relations : toutes les fiches reliées, ou le réseau autour d\'une fiche, avec une profondeur et un filtre par type.',
            '« Voir comme » dans le graphe : le réseau tel qu\'un personnage le connaît. Les joueurs y accèdent depuis leur personnage.',
            'Chronologie : histoire du monde, événements prévus et joués, avec des dates libres comme « Jour 3 ».',
            'Un événement joué noté pendant la séance est rattaché à la séance et à la scène en cours.',
        ],
    ],

    '0.7.0' => [
        'date' => '2026-10-07',
        'title' => 'Autour de la table',
        'items' => [
            'Cartes : une image sur l\'écran de table, que vous zoomez et déplacez, avec une grille carrée facultative et une échelle.',
            'Jetons facultatifs, liés aux fiches (nom et portrait) : déplacer, redimensionner, montrer ou masquer aux joueurs.',
            'Règle temporaire : tracez une ligne, la distance s\'affiche en cases ou en mètres.',
            'Télécommande : depuis votre téléphone, videz l\'écran, passez à l\'élément suivant de la scène, pilotez la carte.',
            'Joueurs : ce que le MJ vous révèle ou vous donne s\'affiche aussitôt, sans passer par les notifications.',
        ],
    ],

    '0.6.0' => [
        'date' => '2026-10-07',
        'title' => 'La mémoire de la campagne',
        'items' => [
            'Secrets : une information à part, reliée à des fiches, scènes ou documents, révélée d\'un clic à un personnage ou à toute la table.',
            'Historique des révélations : qui a appris quoi, quand, pendant quelle séance et quelle scène ; chaque révélation peut être annulée.',
            '« Voir comme » : le MJ voit la campagne exactement comme un personnage, en lecture seule.',
            '« Cité dans » montre aussi les règles et les notes de session qui mentionnent une fiche.',
            'Relations : l\'inverse (« travaille pour » / « emploie ») se remplit tout seul.',
        ],
    ],

    '0.5.0' => [
        'date' => '2026-10-07',
        'title' => 'Mieux ranger, à plusieurs',
        'items' => [
            'Une page « Tags » pour renommer, colorer, fusionner et supprimer vos tags ; les scènes ont aussi des tags.',
            '« Dupliquer » une fiche, un scénario ou toute une campagne, pour rejouer avec une autre table.',
            'Nouveaux rôles : co-MJ, qui prépare et mène avec vous, et spectateur, qui regarde l\'écran de table.',
            '« Afficher à la table » depuis une fiche, un portrait, une illustration, un document ou une règle.',
            'Mode Session : montrer une fiche ou une règle d\'un clic, voir la scène suivante, touche N pour noter.',
        ],
    ],

    '0.4.1' => [
        'date' => '2026-10-07',
        'title' => 'Aide et signalements',
        'items' => [
            'Une page « Aide » répond aux questions les plus fréquentes, pour le MJ comme pour les joueurs.',
            '« Signaler un problème », en bas de chaque page, envoie votre message à l\'équipe avec la page concernée.',
        ],
    ],

    '0.4.0' => [
        'date' => '2026-10-07',
        'title' => 'Toutes les langues',
        'items' => [
            'L\'interface parle français, anglais, allemand, espagnol, italien, portugais, néerlandais et polonais.',
            'La langue suit celle du navigateur ; chacun peut la choisir dans « Préférences ».',
            'Les notifications arrivent dans la langue de celui qui les reçoit.',
            'Le thème sombre reste en place d\'une page à l\'autre.',
            'Après avoir modifié une fiche depuis la page Personnages, on y revient directement.',
        ],
    ],

    '0.3.0' => [
        'date' => '2026-10-07',
        'title' => 'Le lien vivant',
        'items' => [
            'Messagerie entre le MJ et ses joueurs, et panneau « Discussion » toujours à portée de main (groupe et privé).',
            'Notifications : révélations, objets reçus, messages, avec un compteur dans l\'en-tête.',
            'Tout se met à jour en direct : messages, compteurs, révélations, sans recharger la page.',
            'CodexFlow s\'installe comme une application ; la fiche du personnage reste lisible hors ligne ; notifications sur l\'appareil.',
            'Écran de table : cartes, images, fiches et annonces sur la télé ou le projecteur, et partagé aux joueurs si le MJ le souhaite.',
            'Les personnages se donnent des objets et se transmettent ce qu\'ils savent.',
            'Les joueurs notent leurs connaissances et ajoutent leurs objets ; le MJ valide les objets.',
            'Thème sombre, couleur d\'accent et taille du texte dans « Préférences ».',
        ],
    ],

    '0.2.0' => [
        'date' => '2026-10-07',
        'title' => 'Les joueurs',
        'items' => [
            'Invitations des joueurs par lien.',
            'Personnages des joueurs : fiche, feuille PDF, compteurs (PV, magie, munitions…) et champs modifiables par le joueur.',
            'Révélations et « Donner » : connaissances, possessions, documents et règles.',
            'Espace joueur : notes privées ou partagées, intentions « À jouer », journal du personnage.',
            'Journal des modifications : qui, quoi, quand, avant et après.',
        ],
    ],

    '0.1.0' => [
        'date' => '2026-10-06',
        'title' => 'Le MJ seul',
        'items' => [
            'Mondes, campagnes et fiches avec zone publique et zone MJ, liens [[ ]] entre fiches.',
            'Champs libres par jeu, types de fiche, import CSV/JSON.',
            'Scénarios, scènes, règles et bibliothèque de documents.',
            'Mode Session : scène en cours, fiches utiles, notes rapides, « À jouer » et épingles.',
            'Recherche globale.',
        ],
    ],

];
