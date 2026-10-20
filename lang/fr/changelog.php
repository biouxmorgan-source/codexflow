<?php

// Nouveautés par version, de la plus récente à la plus ancienne. Affichées dans
// « Quoi de neuf » (une fois après chaque mise à jour) et sur la page du même nom.
return [

    '0.86.1' => [
        'date' => '2026-10-20',
        'title' => 'Remplacer une carte par une image d’une autre taille',
        'items' => [
            'Quand le document sert de carte, « Remplacer le fichier » affiche la taille de l’image actuelle et accepte une image d’une autre taille. Les pions, gabarits et points gardent leur position en pixels : un avertissement prévient qu’ils se décaleront sur le dessin.',
        ],
    ],

    '0.86.0' => [
        'date' => '2026-10-20',
        'title' => 'Modifier un document sans le supprimer',
        'items' => [
            'Sur la page d’un document, « Remplacer le fichier » met une nouvelle version à la place de l’ancienne : le document garde son titre, ses réglages et ses liens, et la nouvelle version apparaît aussitôt dans les scènes, fiches, règles, cartes, indices et chez les personnages qui l’ont reçu.',
            'Une image qui sert de carte se remplace par une autre image ; pions, grille et points restent à leur place.',
        ],
    ],

    '0.85.0' => [
        'date' => '2026-10-20',
        'title' => 'Lancer les dés depuis la feuille',
        'items' => [
            'Sur la fiche d’un personnage, un petit dé à côté de chaque nombre et de chaque compteur lance les dés avec cette valeur : « Lancer sous… (seuil) » ou « Ajouter… au jet (bonus) ». Le plateau de dés s’ouvre prêt à lancer, et le résultat part à la table comme d’habitude.',
            'Sur la fiche de votre personnage, la valeur reste liée à la fiche : si elle change, le lancer enregistré suit. Le MJ peut aussi lancer depuis la fiche d’un joueur.',
            '« Mes feuilles » a ses propres dés : le même petit dé à côté des valeurs en chiffres, et un bouton « Dés » en bas de la feuille. Le résultat s’affiche sur la feuille, visible de vous seul.',
        ],
    ],

    '0.84.2' => [
        'date' => '2026-10-20',
        'title' => 'Nouvelles cartes pour la démonstration',
        'items' => [
            'La démonstration « Le Serment de Pierrecendre » a de nouvelles cartes illustrées : le village côtier pour la carte de campagne, le quai souterrain du phare pour la sous-carte. Chargez-la de nouveau depuis « Mes campagnes » pour les voir.',
        ],
    ],

    '0.84.1' => [
        'date' => '2026-10-19',
        'title' => 'Plus de place dans Mes feuilles',
        'items' => [
            'Une feuille garde maintenant jusqu’à 200 blocs, 500 champs et 30 onglets. « + Nouvelle note » ne renvoie plus de page d’erreur : à la limite, un message l’explique.',
        ],
    ],

    '0.84.0' => [
        'date' => '2026-10-19',
        'title' => 'Des onglets dans Mes feuilles',
        'items' => [
            'Dans « Modifier », « + Nouvel onglet » partage une feuille en onglets que vous nommez (Personnage, Sorts, Inventaire…), que vous rangez et retirez.',
            'Chaque bloc choisit son onglet, et chaque onglet place ses blocs sur ses trois colonnes. Une feuille existante garde tous ses blocs dans un premier onglet.',
        ],
    ],

    '0.83.0' => [
        'date' => '2026-10-19',
        'title' => 'Plusieurs notes et trois colonnes dans Mes feuilles',
        'items' => [
            '« + Nouvelle note » ajoute un bloc de notes : faites-en autant que vous voulez (dettes, rumeurs, contacts…). Vos notes d’avant deviennent un premier bloc « Notes ».',
            'Les blocs se placent maintenant sur trois colonnes, notes comprises, avec « Organiser les blocs ».',
        ],
    ],

    '0.82.0' => [
        'date' => '2026-10-19',
        'title' => 'Une démonstration à jour',
        'items' => [
            'La campagne de démonstration « Le Serment de Pierrecendre » montre les dernières nouveautés : sur la carte de campagne, le phare mène à une sous-carte, la cave, prête pour le combat avec ses jetons, deux gabarits de zone et un ordre d’initiative. Chaque point relie plusieurs fiches et plusieurs scènes.',
            'Dés à la table : des lancers préparés qui reprennent la caractéristique de la fiche, un bonus nommé, et les lancers de la séance 1, dont une demande du MJ.',
            'Aussi : un son de « Mes sons », une scène « Jouée comme prévu » et une autre avec « Ce qui s’est passé », un indice « Mensonge », une fiche et une règle maison avec leur version officielle, et des joueurs qui complètent leur portrait.',
            'Chargez de nouveau la démonstration depuis « Mes campagnes » pour la découvrir : une copie déjà chargée reste telle quelle.',
        ],
    ],

    '0.81.0' => [
        'date' => '2026-10-19',
        'title' => 'Carte de campagne : zoom, table et lieux plus riches',
        'items' => [
            'La carte de campagne se zoome (molette, + et −, pincer sur tablette) et se déplace en glissant. Conseil : une image 4K (3840 × 2160) reste nette.',
            'Quand la carte est à l’écran de table, la sous-carte ouverte depuis un point l’y remplace, et la carte d’au-dessus revient quand vous remontez. Dans la sous-carte, « Ce lieu » garde les scènes et fiches du point à portée de clic.',
            'Un point se relie maintenant à plusieurs scènes et plusieurs fiches : le lieu, ses PNJ, toutes les scènes qui s’y jouent.',
        ],
    ],

    '0.80.0' => [
        'date' => '2026-10-19',
        'title' => 'Des blocs, des notes et des cases à cocher dans Mes feuilles',
        'items' => [
            'Dans « Mes feuilles », les champs se rangent dans des blocs que vous nommez : Caractéristiques, Compétences, Équipement, Sorts…',
            '« Organiser les blocs » : glissez un bloc par sa poignée, ou utilisez les flèches, pour le placer où vous voulez, sur une ou deux colonnes.',
            'Chaque champ peut porter une note et une case à cocher, à cocher pendant la partie : une compétence réussie, un sort préparé.',
        ],
    ],

    '0.79.0' => [
        'date' => '2026-10-19',
        'title' => 'Bibliothèque de démos',
        'items' => [
            'Nouvelle « Bibliothèque de démos », depuis « Mes campagnes » : des campagnes prêtes à jouer (un univers, un court scénario, des prétirés), dont vous chargez votre propre copie en un clic.',
            'Chaque démo d’un auteur ou d’un éditeur garde un lien « Acheter le jeu », dans la bibliothèque et en haut de votre copie.',
            'Le prix de Premium s’affiche sur le bouton d’abonnement des Préférences : 4,99 € par mois.',
        ],
    ],

    '0.78.0' => [
        'date' => '2026-10-18',
        'title' => 'Ce qui s’est passé, et le livre des aventures',
        'items' => [
            'Chaque scène a maintenant deux parties : « Ce qui est prévu » et « Ce qui s’est passé ». Si la table a suivi le plan, cochez simplement « Jouée comme prévu ».',
            'Nouveau « Livre des aventures » dans la campagne : les scènes jouées, scénario par scénario, puis les résumés des séances, à lire, imprimer ou enregistrer en PDF.',
            'Une version rédigée peut s’y ajouter, écrite par vous ou par l’IA de votre choix (texte à copier, ou votre propre clé d’API, à vos frais). Le livre reste réservé au MJ.',
        ],
    ],

    '0.77.0' => [
        'date' => '2026-10-18',
        'title' => 'Rangez vos écrans à votre façon',
        'items' => [
            'Nouveau bouton « Organiser l’écran » sur la page de la campagne et en mode Session : glissez chaque bloc par sa poignée ⠿, à la souris ou au doigt, ou utilisez les flèches.',
            'En mode Session, un bloc peut passer d’une colonne à l’autre : la musique en haut, les notes à droite, comme vous préférez.',
            '« Masquer » retire un bloc dont vous ne vous servez pas, « Afficher » le fait revenir, « Disposition par défaut » remet tout en place. Cette disposition n’appartient qu’à vous : joueurs et co-MJ gardent la leur.',
        ],
    ],

    '0.76.0' => [
        'date' => '2026-10-18',
        'title' => 'Votre fiche, même sans réseau',
        'items' => [
            'Hors ligne, la fiche de votre personnage reste utilisable : réglez vos compteurs avec − et +, modifiez les champs que le MJ vous laisse et écrivez des notes.',
            'Tout est gardé sur l’appareil puis envoyé au retour de la connexion, champ par champ : un compteur reçoit ce que vous avez retiré ou ajouté, et ce que le MJ a changé entre-temps (un secret révélé, un champ modifié) est gardé.',
            'Après l’envoi, un résumé dit ce qui a été mis à jour, et signale un champ que le MJ a modifié en même temps que vous.',
        ],
    ],

    '0.75.0' => [
        'date' => '2026-10-18',
        'title' => 'La grande carte de campagne',
        'items' => [
            'Nouvelle « Carte de campagne » : choisissez une carte comme vue d’ensemble et posez-y des points d’intérêt, une ville, un donjon, le lieu d’une scène.',
            'Un point peut ouvrir une sous-carte d’un clic, qui a elle-même ses points ; le fil d’Ariane permet de remonter jusqu’à la vue d’ensemble.',
            'Reliés à une scène, les points montrent ce qui est joué, en cours ou à venir ; reliés à une fiche, ils l’ouvrent. Tout reste au MJ, sauf le nom des points « Visibles à la table », affiché sur l’écran de table.',
        ],
    ],

    '0.74.0' => [
        'date' => '2026-10-18',
        'title' => 'Vos personnages joués ailleurs',
        'items' => [
            'Nouvelle page « Mes feuilles » : rangez les personnages que vous jouez hors de SagaWyn, chez un ami, en club ou ailleurs. Vous seul les voyez.',
            'Chaque personnage a sa feuille PDF, un portrait, des champs libres (textes et compteurs à régler avec − et + pendant la partie) et vos notes, avec le nom du jeu et de la table pour les retrouver.',
        ],
    ],

    '0.73.0' => [
        'date' => '2026-10-18',
        'title' => 'Une bibliothèque de sons pour toutes vos campagnes',
        'items' => [
            'Nouvelle page « Mes sons » : vos musiques et ambiances, rangées une seule fois dans votre compte, puis ajoutées aux campagnes que vous menez. Le fichier n’est pas copié et ne compte qu’une fois dans votre espace.',
            'Dans la page Sons d’une campagne : « Depuis ma bibliothèque » pour y ajouter vos sons, « Ranger dans ma bibliothèque » pour réutiliser un son ailleurs, et une case pour ranger un nouveau téléversement directement dans la bibliothèque.',
            'Ranger dans la bibliothèque un son déjà téléversé dans une autre campagne fusionne les deux copies : les scènes gardent leur musique et l’espace est libéré.',
        ],
    ],

    '0.72.0' => [
        'date' => '2026-10-18',
        'title' => 'Le mode d’emploi partout',
        'items' => [
            'Le bouton « ? » arrive sur les sons, le graphe des relations, la chronologie, les avis des joueurs, l’assistant IA, le journal et la fiche de personnage.',
            'Sur le graphe, la chronologie et la fiche de personnage, les joueurs lisent un mode d’emploi écrit pour eux.',
        ],
    ],

    '0.71.1' => [
        'date' => '2026-10-18',
        'title' => 'Retirer ou ajouter plusieurs points d’un coup',
        'items' => [
            'Sous chaque compteur que le joueur peut modifier (PV, munitions, argent…), un champ « Montant » : on tape 8 puis −, et le compteur perd 8 d’un coup. Il revient à 1 par défaut.',
        ],
    ],

    '0.71.0' => [
        'date' => '2026-10-18',
        'title' => 'Un mode d’emploi sur plus d’écrans',
        'items' => [
            'Le bouton « ? » arrive aussi sur la fiche (côté MJ), le mode Session, les scénarios et les scènes, l’import de fichiers et les membres de la campagne.',
            'La page « Aide » explique désormais le lancer brut et les bonus lus sur la fiche du personnage.',
        ],
    ],

    '0.70.0' => [
        'date' => '2026-10-18',
        'title' => 'Un mode d’emploi sur les écrans délicats',
        'items' => [
            'Un bouton « ? » à côté du titre des écrans qui ne s’expliquent pas tout seuls ouvre leur mode d’emploi en quelques lignes : types de fiche, champs du jeu, tags, dés, cartes, télécommande de l’écran de table, indices et historique des révélations.',
            'Chaque mode d’emploi renvoie à la question correspondante de la page « Aide », qui s’ouvre directement dessus.',
        ],
    ],

    '0.69.0' => [
        'date' => '2026-10-18',
        'title' => 'Bonus et seuil lus sur la fiche',
        'items' => [
            'Dans les dés, le bonus peut reprendre une ou plusieurs valeurs de la fiche du personnage (caractéristique, compétence, tout champ nombre ou compteur de la zone publique), entières ou en dizaine, moitié ou cinquième, en plus du bonus libre. Le seuil peut aussi se lire sur la fiche. Les valeurs sont lues au moment du lancer, sur la fiche de chaque personnage qui lance.',
            'Plusieurs bonus nommés (Arme +2, Blessure −1…) s’ajoutent au même lancer, avec les valeurs de la fiche.',
            'Les exemples tout faits disparaissent du plateau : il garde vos lancers enregistrés.',
            'Un lancer enregistré se supprime d’un geste : le × sur sa pastille, ou « Supprimer » une fois chargé.',
        ],
    ],

    '0.68.0' => [
        'date' => '2026-10-17',
        'title' => 'Lancer brut',
        'items' => [
            'Nouvelle lecture « Brut », choisie par défaut : on prend le type et le nombre de dés (1d20 + 2d6…), on lance, et chaque dé s’affiche tel qu’il tombe, sans total, sans seuil ni autre règle.',
        ],
    ],

    '0.67.0' => [
        'date' => '2026-10-17',
        'title' => 'Vos contenus, vos droits',
        'items' => [
            'La page « Confidentialité et mentions légales » précise que SagaWyn n’est pas propriétaire de ce que vous y mettez : chaque utilisateur reste propriétaire de ses contenus et responsable de ce qu’il publie.',
            'Nouvelles rubriques : « Importation et structuration de contenus », « Services d’intelligence artificielle », « Signaler un contenu illicite », « Disponibilité et sauvegardes » et « Jeux et marques ». Les pages d’import y renvoient.',
        ],
    ],

    '0.66.0' => [
        'date' => '2026-10-17',
        'title' => 'Dés mélangés et lancers à soi',
        'items' => [
            'Un même lancer peut mêler plusieurs types de dés : touchez un d20 puis deux d6, et le plateau lance 1d20 + 2d6. Chaque type a son nombre, réglable ou retirable.',
            'Le seuil se pose au choix sur le total (« Seuil sur le total », atteint ou à ne pas dépasser) ou sur chaque dé (« Seuil par dé » : tous les dés à 4 ou plus sont des réussites, par exemple).',
            'Chacun, joueur comme MJ, enregistre ses propres lancers dans « Mes lancers », les recharge d’un geste et les met à jour quand son personnage évolue. Le MJ garde aussi les « Lancers de la campagne » pour toute la table.',
        ],
    ],

    '0.65.1' => [
        'date' => '2026-10-17',
        'title' => 'Accès vérifiés partout',
        'items' => [
            'Taper une adresse à la main n’ouvre jamais une campagne, une fiche ou un fichier auquel on n’a pas droit. Un compte qui n’est pas membre reçoit désormais « Page introuvable », comme pour une adresse qui n’existe pas : essayer des numéros n’apprend même pas qu’une campagne existe.',
        ],
    ],

    '0.65.0' => [
        'date' => '2026-10-17',
        'title' => 'Les dés à la table',
        'items' => [
            'Nouveau bouton « Dés » en bas de chaque page de la campagne : le nombre et le type de dés (4 à 100 faces, ou autre), puis la façon de les lire : additionner, atteindre un seuil, rester sous un seuil, compter les réussites, garder les meilleurs ou les plus faibles, lire en chiffres. Avec un bonus, et des dés qui relancent sur le max (+) ou sur le 1 (−).',
            'Le MJ demande un lancer à toute la table ou à certains personnages, à la vue de tous ou en discret. Une fenêtre s’ouvre chez les joueurs concernés ; sans seuil, chacun saisit le sien et ajoute son bonus, et un seuil caché reste chez le MJ, qui voit qui a lancé quoi.',
            'Les lancers à la table roulent quelques secondes sur l’écran de table ; un lancer discret ne se montre qu’au MJ. Le MJ enregistre ses lancers habituels pour toute la table, et les dés se coupent dans « Fonctions de la campagne ».',
        ],
    ],

    '0.64.1' => [
        'date' => '2026-10-17',
        'title' => 'Gabarits mesurés comme la règle, QR codes',
        'items' => [
            'Les gabarits se règlent dans la même unité que la règle (mètres, pieds…) : la « Taille réelle d’une case », dans Grille, calibre les deux, quelle que soit la taille des cases à l’écran.',
            'Un bouton « Supprimer » plus visible sur chaque gabarit, et « Supprimer tous les gabarits » à la fin du sort.',
            'Chaque lien de « Rejoindre à distance » a son QR code, à montrer pour que les joueurs le scannent avec leur téléphone.',
        ],
    ],

    '0.64.0' => [
        'date' => '2026-10-17',
        'title' => 'Gabarits de zone sur la carte',
        'items' => [
            'Nouveaux gabarits dans la page de la carte : cercle, carré, rectangle, cône et ligne, dimensionnés en cases (ou dans l’unité de l’échelle). On les glisse sur la carte, on les oriente, on les masque, et la liste des jetons pris dans la zone s’affiche aussitôt.',
            'Les gabarits visibles apparaissent sur l’écran de table, sous les jetons.',
        ],
    ],

    '0.63.0' => [
        'date' => '2026-10-17',
        'title' => 'Carte de combat partagée',
        'items' => [
            'Sur l’écran de table partagé, chaque joueur déplace le jeton de son personnage. Dans la carte, « Déplacé par » confie un autre jeton à un joueur : suivant, familier, ennemi contrôlé…',
            'Nouvel ordre d’initiative, depuis la télécommande ou la carte : PJ et ennemis avec leur valeur, tour en cours, rounds. Le MJ le montre par-dessus l’écran quand il le souhaite ; les lignes masquées restent chez lui, et une rangée « Aussi présents » montre des PNJ ou objets sans initiative.',
            '« Rejoindre à distance » dans la télécommande : le lien de l’écran pour les joueurs, et un lien invité sans compte, en lecture seule et révocable, pour quelqu’un qui veut seulement regarder.',
        ],
    ],

    '0.62.0' => [
        'date' => '2026-10-13',
        'title' => 'La communauté sur Discord',
        'items' => [
            'SagaWyn a son serveur Discord : un lien en bas de chaque page et sur la page d’accueil pour poser vos questions, proposer vos idées et rencontrer d’autres maîtres de jeu.',
        ],
    ],

    '0.61.0' => [
        'date' => '2026-10-13',
        'title' => 'Mode démo avant le Kickstarter',
        'items' => [
            'Nouveau mode démo, à activer dans Administration › Formules : chaque nouveau compte reçoit la formule Démo, avec sa propre copie de la campagne de démonstration et un bandeau qui annonce le financement participatif à venir.',
            'La démonstration d’un compte démo est verrouillée : tout se visite, mais seuls la séance, les notes, les jetons de la carte et l’écran de table se manipulent. Le compte peut aussi créer une petite campagne à lui (50 Mo, 3 membres, sans IA ni export).',
            'Une invitation d’un administrateur ouvre un compte normal. Dans « Comptes », « Débloquer » fait passer un compte démo en formule gratuite et démarre son essai, que la démo n’entame pas.',
        ],
    ],

    '0.60.0' => [
        'date' => '2026-10-13',
        'title' => 'Contenu maison',
        'items' => [
            'Nouvelle coche « Contenu maison » sur les fiches (PNJ, sorts, objets, lieux…) : sans coche, la fiche est officielle. Un badge « Maison » la signale dans la liste et sur la fiche, et un filtre Officiel / Maison trie l’univers.',
            'Pour une fiche maison ou une règle d’origine « Maison », la version officielle remplacée se note à côté (par exemple l’initiative au D10 au lieu du D20) : elle reste en zone MJ, repliée sous la fiche ou la règle.',
            'La liste des règles se filtre par origine. L’export et l’import de campagne gardent le contenu maison et la version officielle.',
        ],
    ],

    '0.59.1' => [
        'date' => '2026-10-13',
        'title' => 'Vérification de mise en ligne plus juste',
        'items' => [
            'Administration : `sagawyn:check` ne signale plus à tort un journal en mode débogage quand LOG_LEVEL vaut warning.',
        ],
    ],

    '0.59.0' => [
        'date' => '2026-10-13',
        'title' => 'Le logo d’Autistic Intelligence',
        'items' => [
            'Le logo d’Autistic Intelligence, l’éditeur de SagaWyn, apparaît en bas des pages, sur l’écran de connexion et dans les mentions légales. Il suit le thème clair ou sombre.',
        ],
    ],

    '0.58.0' => [
        'date' => '2026-10-12',
        'title' => 'Un indice peut être un mensonge',
        'items' => [
            'Indices : nouvelle nature « Mensonge », à côté de Rumeur, Indice et Vérité, avec son filtre. Comme les autres natures, elle n’est visible que du MJ : le joueur à qui on le révèle ne lit que l’indice.',
        ],
    ],

    '0.57.0' => [
        'date' => '2026-10-12',
        'title' => 'Préparer une campagne entière avec une IA',
        'items' => [
            'Mes campagnes → Importer une campagne : nouveau lien « Préparez l’archive avec une IA ». Le prompt (ou le skill Claude) fait lire le livre de jeu de rôle à votre IA, qui fabrique une archive .zip : le jeu, le monde, la campagne, les fiches, les scènes et les documents sont créés d’un coup.',
        ],
    ],

    '0.56.1' => [
        'date' => '2026-10-11',
        'title' => 'Pages jeu et monde lisibles sur téléphone',
        'items' => [
            'Sur téléphone, l’image d’un jeu ou d’un monde passe au-dessus du nom en pleine largeur : le nom et la description ne sont plus écrasés et le bouton Modifier ne les recouvre plus.',
        ],
    ],

    '0.56.0' => [
        'date' => '2026-10-11',
        'title' => 'Les secrets deviennent des indices',
        'items' => [
            'Les « secrets » de la campagne s’appellent maintenant des « indices » : page Indices, panneau des fiches, scènes et documents, mode Session, recherche, révélations et aide. Rien ne change dans vos données.',
        ],
    ],

    '0.54.0' => [
        'date' => '2026-10-11',
        'title' => 'Les joueurs modifient leur personnage',
        'items' => [
            'Page Champs : « Modifiable par le joueur » se coche pour tout un groupe (caractéristiques, compétences…) ou pour tous les champs publics en un clic.',
            'Nouvelle option dans la page Joueurs : le MJ peut laisser chaque joueur modifier le portrait, le résumé et la description de son personnage. La zone MJ reste cachée.',
        ],
    ],

    '0.51.0' => [
        'date' => '2026-10-11',
        'title' => 'Badge Fondateur et codes cadeaux',
        'items' => [
            'Les contributeurs du Kickstarter pourront être marqués Fondateur dans la console d’administration : un badge ★ Fondateur visible dans les préférences, la liste des membres et la fiche du personnage.',
            'Les Fondateurs ont de l’espace de stockage en plus de leur formule, gratuite ou premium (500 Mo par défaut, réglable dans « Formules »).',
            'Nouvelle page « Mes récompenses » (depuis les préférences) : la récompense Kickstarter, les codes cadeaux Premium à offrir par lien, avec le nom de la personne qui a utilisé chacun, et une case pour utiliser un code reçu. Les mois s’ajoutent à la suite du Premium en cours.',
        ],
    ],

    '0.50.1' => [
        'date' => '2026-10-11',
        'title' => 'Santé : filtre par rôle',
        'items' => [
            'Dans l’onglet Santé, un filtre par rôle de compte (sans les administrateurs, administrateurs, premium, essai, gratuit).',
            'La campagne de démonstration n’est plus comptée dans les statistiques.',
        ],
    ],

    '0.50.0' => [
        'date' => '2026-10-10',
        'title' => 'Santé de la plateforme',
        'items' => [
            'Nouvel onglet Santé dans la console d’administration : personnes connectées et nombre de connexions par jour, semaine ou mois, joueurs par campagne et par MJ, espace occupé (fichiers, base, disque) et temps de réponse du serveur heure par heure.',
            'La console prévient quand le serveur ralentit : plus de 5 % des requêtes au-delà d’une seconde sur 24 heures.',
        ],
    ],

    '0.49.1' => [
        'date' => '2026-10-10',
        'title' => 'Une aide plus complète',
        'items' => [
            'L’aide répond à 19 nouvelles questions : jeu, monde et campagne, démonstration, recherche et liens, tags, scénarios, mode Session, champs du jeu, règles, duplication, échanges, départ d’un joueur, journal, fin de Premium, et côté joueurs : fiche, notes, messages et avis.',
        ],
    ],

    '0.49.0' => [
        'date' => '2026-10-10',
        'title' => 'La démonstration montre tout SagaWyn',
        'items' => [
            'La campagne de démonstration utilise maintenant chaque fonction : un personnage joueur tiré d’un prétiré, avec ce qu’il a reçu (fiche, document, règle, secret, objets) et que le MJ peut « Voir comme » ; des notes de séance ; une liste « À jouer » ; des fiches épinglées ; des champs référence, document et commun à deux types ; une pièce jointe MJ ; des secrets de chaque nature ; des tags colorés ; des couvertures pour le jeu et le monde.',
            'Recharger la démonstration reprend le type de fiche « Faction » déjà créé au lieu d’en ajouter un second.',
        ],
    ],

    '0.48.0' => [
        'date' => '2026-10-10',
        'title' => 'Une démonstration plus complète',
        'items' => [
            'La campagne de démonstration « Le Serment de Pierrecendre » contient maintenant trois ambiances sonores originales liées aux scènes, une première séance déjà jouée avec son résumé, et une demande d’avis aux joueurs.',
            'Correction : le fondu entre deux morceaux ne bloque plus la lecture dans certains navigateurs.',
        ],
    ],

    '0.47.0' => [
        'date' => '2026-10-10',
        'title' => 'LoreMundi devient SagaWyn',
        'items' => [
            'LoreMundi s’appelle désormais SagaWyn, toujours édité par Autistic Intelligence. Every world has a story.',
            'Vos campagnes, comptes et archives ne changent pas : les sauvegardes faites avec LoreMundi ou CodexFlow s’importent toujours. Les fichiers téléchargés commencent désormais par « sagawyn- ».',
        ],
    ],

    '0.46.0' => [
        'date' => '2026-10-10',
        'title' => 'Avis des joueurs',
        'items' => [
            'En fin de séance ou de campagne, demandez à vos joueurs une note de 1 à 5 étoiles, ce qui leur a plu et ce qui pourrait être mieux (« Avis des joueurs » dans la campagne, ou depuis la page d’une séance).',
            'Réponses anonymes ou signées, au choix du MJ : les joueurs le savent avant de répondre. Vous voyez la moyenne, la répartition des notes et tous les commentaires.',
        ],
    ],

    '0.45.0' => [
        'date' => '2026-10-10',
        'title' => 'Bibliothèque sonore',
        'items' => [
            'Nouvelle page « Sons » : téléversez vos musiques et ambiances (mp3, ogg, m4a, wav, flac), rangez-les par tags et liez-les aux scènes.',
            'En mode Session, le bloc « Musique » propose d’abord les sons de la scène : « Ici » les joue sur votre appareil, sans couper quand vous changez de page ; « Table » les joue sur l’écran de table, avec pause, boucle et volume pilotés depuis la session.',
        ],
    ],

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
