<?php

// Nouveautés par version, de la plus récente à la plus ancienne. Affichées dans
// « Quoi de neuf » (une fois après chaque mise à jour) et sur la page du même nom.
return [

    '0.44.0' => [
        'date' => '2026-10-09',
        'title' => 'Prêt pour la mise en ligne',
        'items' => [
            'L’administrateur est prévenu dans la console quand l’installation a un point bloquant (débogage actif, e-mails non configurés, temps réel non chiffré…).',
            'La base et les fichiers envoyés sont sauvegardés chaque nuit sur le serveur.',
        ],
    ],

    '0.43.0' => [
        'date' => '2026-10-09',
        'title' => 'Fiabilité',
        'items' => [
            'De nouveaux tests automatiques vérifient dans un vrai navigateur la mise à jour sans temps réel, « Se souvenir de moi », le lien « Aller au contenu », l’affichage sur téléphone en texte très grand et une fonction coupée par le MJ pendant qu’une page est ouverte.',
        ],
    ],

    '0.42.0' => [
        'date' => '2026-10-09',
        'title' => 'Descriptions mises en forme',
        'items' => [
            'Les descriptions de jeu, de monde, de scénario et de document ont le même éditeur que les fiches : gras, italique, intertitres, listes, citations, et liens [[ ]] vers les fiches dans une campagne.',
        ],
    ],

    '0.41.0' => [
        'date' => '2026-10-09',
        'title' => 'Champs partagés',
        'items' => [
            'Un champ peut concerner plusieurs types de fiche à la fois, par exemple les points de vie des personnages et des créatures ; les modèles de jeu et les fichiers de champs gardent ce choix.',
        ],
    ],

    '0.40.0' => [
        'date' => '2026-10-09',
        'title' => 'Application et sécurité',
        'items' => [
            'L’application installée sur téléphone porte une description dans votre langue et une icône adaptée aux écrans ronds d’Android.',
            'Hors connexion, les pages restent lisibles et les boutons de modification sont grisés jusqu’au retour du réseau.',
            'Le navigateur n’accepte plus que les scripts, images et connexions venant de LoreMundi lui-même.',
        ],
    ],

    '0.39.0' => [
        'date' => '2026-10-09',
        'title' => 'Compte et administration',
        'items' => [
            'On peut signaler un problème sans compte, depuis les pages de connexion et d’inscription ou l’aide, en laissant une adresse pour la réponse.',
            'Les Préférences indiquent la date de début de l’abonnement, et que le passage à Premium ouvrira bientôt tant que le paiement n’est pas en place.',
            'Console d’administration : jours de connexion plutôt que connexions, date de la dernière connexion, et alerte quand l’envoi push est impossible ou échoue.',
        ],
    ],

    '0.38.0' => [
        'date' => '2026-10-09',
        'title' => 'Recherche',
        'items' => [
            'La recherche trouve aussi les autres formes d’un mot en français : « lanternes » trouve « lanterne », « éteinte » trouve « éteintes ».',
            'La recherche depuis l’accueil cherche aussi dans vos mondes et jeux qui ne sont rattachés à aucune campagne.',
        ],
    ],

    '0.37.0' => [
        'date' => '2026-10-09',
        'title' => 'Joueurs et rôles',
        'items' => [
            'Une fiche révélée montre au joueur ses illustrations et fichiers de la zone publique, et ses relations publiques vers les fiches qu’il connaît.',
            'Le joueur a un « Fil de la campagne » sur sa fiche : séances, événements joués connus de la table et messages au groupe.',
            'Les co-MJ voient les pages du jeu et du monde en lecture seule, téléchargent l’archive et le modèle du jeu, et gèrent les champs si le propriétaire le coche dans « Membres ». Un co-MJ rétrogradé ne garde pas les notifications reçues comme MJ.',
        ],
    ],

    '0.36.0' => [
        'date' => '2026-10-09',
        'title' => 'Confort du MJ et de la séance',
        'items' => [
            'Les pages d’un PDF affiché à la table se tournent depuis la télécommande, depuis la page du document ou avec les flèches de l’écran du MJ, et les joueurs qui suivent l’écran tournent avec lui. Le lecteur PDF indique « page n / N » et permet d’aller à une page.',
            'En Mode Session, « Événement joué » ajoute le texte saisi à la chronologie, rattaché à la séance et à la scène en cours.',
            'Un personnage se rend à n’importe lequel de ses anciens joueurs revenu dans la campagne, qui retrouve ses propres échanges privés avec le MJ, sans ceux des joueurs intermédiaires.',
            'Le statut d’une fiche s’affiche sous son titre ; les prétirés (tag « prétiré ») sont proposés en tête dans « Nouveau personnage » ; les compteurs de la page Tags listent les éléments tagués ; une démonstration chargée plusieurs fois numérote sa campagne, son jeu et son monde ; le rôle spectateur précise qu’il voit l’écran de table même non partagé.',
        ],
    ],

    '0.35.0' => [
        'date' => '2026-10-09',
        'title' => 'Corrections de la recette finale',
        'items' => [
            'La page d’une séance a un résumé écrit par le MJ, et montre les événements joués et tout ce qui a été révélé ou donné pendant la séance.',
            'L’historique d’une fiche garde aussi ses relations, ses fichiers joints, ses tags et son statut dans la campagne. La page d’un monde montre son histoire, celle d’un jeu ses types de fiche.',
            'Une fonction coupée par le MJ ou par la formule l’est aussi sur les pages restées ouvertes, et le Mode Session s’ouvre quand les cartes sont coupées.',
            'Corrections : quantité annoncée lors d’un échange, case oui/non jamais remplie, page « introuvable » traduite, pages lisibles sur téléphone en grande taille de texte, connexions comptées une fois, aide mise à jour.',
        ],
    ],

    '0.34.0' => [
        'date' => '2026-10-08',
        'title' => 'Fonctions par campagne',
        'items' => [
            'Sur la page de la campagne, le MJ coche les fonctions dont sa table a besoin : écran de table, cartes, échanges entre joueurs, graphe, chronologie, assistant IA. Une fonction décochée disparaît pour tous, sans rien effacer ; elle revient dès qu’on la recoche.',
        ],
    ],

    '0.33.0' => [
        'date' => '2026-10-08',
        'title' => 'Finitions',
        'items' => [
            'La recherche montre le champ de la fiche qui contient le mot trouvé, avec son nom.',
            'Graphe : les noms et libellés qui se chevauchent sont déplacés ou masqués ; survoler une fiche les fait réapparaître.',
            '« Révéler ou donner » : une case « Tous les personnages actifs » coche toute la table d’un coup.',
            'Un joueur retiré puis réinvité retrouve son ancien personnage d’un clic, depuis la page Personnages.',
            'Sans temps réel (serveur Reverb absent ou coupé), cloche, messages et fiches se mettent à jour toutes les 30 secondes.',
        ],
    ],

    '0.32.0' => [
        'date' => '2026-10-08',
        'title' => 'PDF et documents',
        'items' => [
            'Les PDF s’affichent dans un lecteur intégré, le même sur ordinateur, tablette et téléphone, avec zoom et téléchargement.',
            'À l’écran de table, un PDF s’affiche page par page, ajusté à l’écran ; les flèches tournent les pages.',
            'La feuille de personnage garde son nom de fichier d’origine.',
            '« Utilisé par » indique le scénario de chaque scène.',
            'Les pages d’un jeu et d’un monde peuvent avoir une image.',
        ],
    ],

    '0.31.0' => [
        'date' => '2026-10-08',
        'title' => 'Fonctions Premium ✦',
        'items' => [
            'Une petite étoile ✦ signale les fonctions Premium. Quand la formule du propriétaire d’une campagne ne les comprend pas, elles restent visibles, grisées, avec une explication.',
            'À la fin d’un essai ou d’un abonnement, rien n’est effacé : campagnes, cartes, messages et fichiers restent, seules les fonctions ✦ se coupent.',
            'L’administrateur peut offrir une période (Noël…) pendant laquelle les comptes gratuits ont toutes les fonctions Premium.',
        ],
    ],

    '0.30.0' => [
        'date' => '2026-10-08',
        'title' => 'Écriture et liens',
        'items' => [
            'Les champs « texte long » et les notes MJ des règles ont l’éditeur riche et les liens [[ ]].',
            'Sur sa fiche, le joueur voit les textes longs mis en forme, avec des liens vers les fiches que son personnage connaît.',
            'La note rapide de séance propose les fiches dès « [[ ».',
            'Les copies sont numérotées (« copie 2 », « copie 3 ») et un import ne reprend jamais le nom d’un jeu, d’un monde ou d’une campagne que vous avez déjà.',
        ],
    ],

    '0.29.0' => [
        'date' => '2026-10-08',
        'title' => 'Sécurité du compte',
        'items' => [
            'Une nouvelle adresse e-mail n’est adoptée qu’après un clic sur le lien qu’elle reçoit ; l’ancienne adresse est ensuite prévenue.',
            'Les essais sur les formulaires de compte sont comptés par formulaire et par adresse e-mail, et la page d’attente dit combien de secondes patienter.',
            'Seuls les scripts de LoreMundi peuvent s’exécuter dans les pages.',
            'La suppression du compte annonce que vos messages sont effacés.',
        ],
    ],

    '0.28.0' => [
        'date' => '2026-10-08',
        'title' => 'E-mails aux couleurs de LoreMundi',
        'items' => [
            'Les e-mails (mot de passe oublié, changement d’adresse) portent le logo et les couleurs de LoreMundi.',
            'Chaque e-mail part dans la langue du destinataire, même quand c’est l’administrateur qui l’envoie.',
        ],
    ],

    '0.27.0' => [
        'date' => '2026-10-08',
        'title' => 'Une vitrine publique',
        'items' => [
            'Une page d’accueil présente LoreMundi aux visiteurs et aux moteurs de recherche, dans les 8 langues.',
            'L’aide se lit sans compte et gagne un volet « Votre compte » : formules, adresse e-mail, mot de passe, double authentification, données.',
        ],
    ],

    '0.26.0' => [
        'date' => '2026-10-08',
        'title' => 'Corrections de la recette v0.25.0',
        'items' => [
            'Une page restée ouverte revérifie vos droits à chaque action : un joueur retiré ou un co-MJ rétrogradé ne reçoit plus rien de nouveau.',
            'Le joueur à qui l’on confie un personnage ne lit plus la conversation privée de l’ancien joueur avec le MJ.',
            'Les prétirés de la campagne de démonstration sont proposés dans « Nouveau personnage ».',
            'Double authentification : le code de secours marche sur l’écran de connexion, et l’administrateur peut retirer la double authentification d’un compte bloqué.',
            'Nouvelles icônes LoreMundi (onglet, application installée, notifications).',
            'Écran de table partagé lisible sur téléphone ; en-têtes de fiche et de page corrigés sur téléphone et tablette, y compris en grande taille de texte.',
            'Corrections : notifications push sans erreur, boutons de la fenêtre « reçu », référence à une fiche après un renommage, quantité d’un échange, règle de mesure des cartes, bandeau « Hors ligne », messages d’erreur traduits, lien « Aller au contenu ».',
        ],
    ],

    '0.25.0' => [
        'date' => '2026-10-08',
        'title' => 'Importer un livre de jeu avec une IA',
        'items' => [
            'Dans « Importer », « Préparez les fichiers avec une IA » donne un prompt à coller dans l’IA de votre choix avec le PDF d’un jeu ou d’un scénario : elle prépare les fichiers d’import (champs, fiches, règles, scènes) et un guide pas à pas. Le prompt existe aussi en skill Claude.',
        ],
    ],

    '0.24.0' => [
        'date' => '2026-10-08',
        'title' => 'Sécurité et données personnelles',
        'items' => [
            '« Mon compte », dans Préférences : changez votre nom, votre adresse e-mail (l’ancienne adresse est prévenue) et votre mot de passe.',
            'Double authentification facultative, dans Préférences : un code donné par une application de votre téléphone, avec des codes de secours.',
            '« Mes données », dans Préférences : téléchargez ce que LoreMundi garde sur vous, ou supprimez votre compte.',
            'Mots de passe d’au moins 10 caractères avec lettres et chiffres ; changer le sien déconnecte ses autres appareils. La console d’administration redemande le mot de passe.',
            'Nouvelle page « Confidentialité et mentions légales ».',
            'Un joueur retiré de la campagne, ou devenu spectateur, ne joue plus son personnage et ne reçoit plus rien de ce qu’on lui révèle. D’autres vérifications de droits ont été renforcées côté serveur.',
        ],
    ],

    '0.23.0' => [
        'date' => '2026-10-08',
        'title' => 'CodexFlow devient LoreMundi',
        'items' => [
            'CodexFlow s’appelle désormais LoreMundi, édité par Autistic Intelligence. Every world has a story.',
            'Vos campagnes, comptes et archives ne changent pas : les sauvegardes faites avec CodexFlow s’importent toujours. Les fichiers téléchargés commencent désormais par « loremundi- ».',
        ],
    ],

    '0.22.0' => [
        'date' => '2026-10-08',
        'title' => 'Sauvegarde complète',
        'items' => [
            'Le propriétaire peut télécharger une sauvegarde complète : l’archive de la campagne avec la table (personnages, ce qu’ils ont reçu, séances, notes, messages et journal), sans note « Moi seul » ni adresse e-mail. À l’import, les personnages reviennent sans joueur, prêts à être confiés.',
        ],
    ],

    '0.21.0' => [
        'date' => '2026-10-08',
        'title' => 'Recherche depuis l’accueil',
        'items' => [
            'Hors d’une campagne, la barre de recherche cherche dans toutes vos campagnes, leurs mondes et leurs jeux, chacune avec vos droits : tout en MJ, ce que connaît votre personnage en joueur.',
        ],
    ],

    '0.20.0' => [
        'date' => '2026-10-08',
        'title' => '« Cité dans » complet, duplication sans les statuts',
        'items' => [
            '« Cité dans » montre aussi la chronologie, les secrets et les champs des autres fiches qui mentionnent la fiche.',
            'Une campagne dupliquée repart des fiches d’origine : le statut « mort » ou « prisonnier » n’est plus recopié, sauf si vous cochez la case pour le garder.',
        ],
    ],

    '0.19.0' => [
        'date' => '2026-10-08',
        'title' => 'Secrets et nouveaux champs',
        'items' => [
            'Chaque secret a une nature (rumeur, indice ou vérité) et un état déduit de qui le connaît : caché, partiel ou révélé. On peut filtrer les secrets selon l’une et l’autre.',
            'Trois nouveaux types de champ : lien web, fichier (un document de la campagne) et référence à une autre fiche, qui reste liée même si la fiche est renommée.',
            'Une campagne dupliquée garde les liens entre ses fiches copiées.',
        ],
    ],

    '0.18.0' => [
        'date' => '2026-10-08',
        'title' => 'Nouveau personnage, Mes campagnes, pages jeu et monde',
        'items' => [
            'Quand un joueur reçoit un nouveau personnage, le MJ coche ce qui passe de l’ancien : connaissances, informations, documents et règles sont recopiés, les objets changent de main.',
            'Mes campagnes : bouton « Reprendre », date de la dernière séance, et les campagnes archivées rangées à part.',
            'Chaque jeu et chaque monde a sa page : description, campagnes, règles, documents, champs ou fiches réutilisables.',
        ],
    ],

    '0.17.0' => [
        'date' => '2026-10-08',
        'title' => 'Éditeur riche et notes des joueurs',
        'items' => [
            'Les textes longs (descriptions, notes MJ, scènes, règles, chronologie, notes des joueurs) ont un éditeur avec gras, italique, intertitres, listes et citations ; « [[ » propose toujours les fiches à lier.',
            'Les joueurs lient leurs notes aux fiches que leur personnage connaît, et seulement à celles-là.',
            'La page d’une séance montre aussi les notes prises par les joueurs pendant celle-ci, sauf celles qu’ils gardent pour eux.',
        ],
    ],

    '0.16.0' => [
        'date' => '2026-10-08',
        'title' => 'Abonnement premium et essai gratuit',
        'items' => [
            'Passer Premium depuis « Préférences » : paiement mensuel ou annuel sécurisé par Stripe, factures et résiliation dans le portail Stripe. Le premium dure jusqu’à la fin de la période payée.',
            'Essai offert : six semaines avec toutes les fonctions, à partir de votre première campagne en tant que MJ. Un joueur qui n’est jamais MJ ne l’entame pas. La durée se règle dans la console d’administration.',
            'Un onglet « Évolutions » dans la console d’administration pour tenir la feuille de route de la plateforme.',
        ],
    ],

    '0.15.0' => [
        'date' => '2026-10-08',
        'title' => 'Console d’administration et formules',
        'items' => [
            'Une console d’administration : les comptes avec leur formule, les dates d’abonnement, le stockage utilisé, la présence d’une clé d’IA, les campagnes et les connexions, sans donnée personnelle. L’administrateur règle la formule de chacun et peut envoyer un lien de réinitialisation du mot de passe, sans jamais le voir.',
            'Trois formules : administrateur, premium et gratuite. Stockage, nombre de campagnes et fonctions de la formule gratuite se règlent dans la console ; jouer, être co-MJ ou spectateur ne compte jamais.',
            'Le backlog réunit les problèmes signalés, les bugs et les évolutions, avec statut, priorité et version de correction ; les cahiers de recette y sont conservés version après version. Votre formule s’affiche dans « Préférences ».',
        ],
    ],

    '0.14.0' => [
        'date' => '2026-10-08',
        'title' => 'Suites de la recette : échanges validés par le MJ',
        'items' => [
            'Par défaut, le MJ valide les échanges entre joueurs : l’objet ou la connaissance ne change de main qu’une fois l’échange accepté. Une case dans « Personnages des joueurs » permet de les autoriser d’office.',
            'Télécommande : au dernier élément de la scène, « Suivant » devient « Terminer » et vide l’écran.',
            'Journal plus lisible pour les objets validés, messages plus clairs dans « Signaler un problème », et une seule façon de s’adresser à vous dans chaque langue.',
        ],
    ],

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
            'Nouvel outil « Assistant IA » dans la campagne : LoreMundi prépare un texte avec les notes de la séance et le contexte de la campagne, à coller dans l’IA de votre choix. Sa réponse, collée en retour, devient des propositions : résumé, événements joués, relations, statuts, notes de campagne, révélations.',
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
            'LoreMundi s\'installe comme une application ; la fiche du personnage reste lisible hors ligne ; notifications sur l\'appareil.',
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
