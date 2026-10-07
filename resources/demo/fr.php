<?php

/*
 * Campagne de démonstration, texte en français (version de référence).
 *
 * Seul le texte est ici : la structure (qui est relié à qui, les jetons, les liens) est dans
 * App\Actions\Demo\LoadDemoCampaign et reste la même dans toutes les langues. Chaque traduction
 * reprend exactement ces clés. Dans les scènes, « [[clé]] » désigne une fiche par sa clé.
 */
return [
    'campaign' => [
        'name' => 'Le Serment de Pierrecendre',
        'description' => 'Campagne de démonstration : trois séances dans la ville-port de Pierrecendre, où un serment oublié revient réclamer son dû. Tout le contenu est original et libre de droits.',
    ],
    'game' => [
        'name' => 'Brume & Serment',
        'description' => 'Jeu d’enquête et de serments, inventé pour la démonstration. Quatre caractéristiques notées de 1 à 5, des serments qui pèsent sur les jets, aucune mécanique propriétaire.',
    ],
    'world' => [
        'name' => 'Vehrmund',
        'description' => 'Un archipel de marches noyées et de ports bâtis sur la cendre, où une parole donnée vaut contrat.',
    ],

    'types' => [
        'faction' => 'Faction',
        'pregen' => 'Prétiré',
    ],

    'groups' => [
        'traits' => 'Caractéristiques',
        'profile' => 'Profil',
        'secrets' => 'Secrets',
    ],

    'fields' => [
        'body' => 'Corps',
        'skill' => 'Adresse',
        'mind' => 'Esprit',
        'heart' => 'Cœur',
        'breath' => 'Souffle',
        'oaths' => 'Serments tenus',
        'trade' => 'Métier',
        'trait' => 'Trait marquant',
        'ties' => 'Attaches',
        'hidden_oath' => 'Serment caché',
        'betrayal' => 'Ce qui le ferait trahir',
    ],

    'tags' => [
        'city' => 'ville',
        'act1' => 'acte 1',
        'act2' => 'acte 2',
        'act3' => 'acte 3',
        'intrigue' => 'intrigue',
        'hall' => 'halle',
        'quays' => 'quais',
        'marshes' => 'marches',
        'guard' => 'garde',
        'pregen' => 'prétiré',
        'base' => 'base',
        'oaths' => 'serments',
        'house' => 'maison',
    ],

    'quay_state' => [
        'status' => 'sous couvre-feu',
        'notes' => 'Fermé la nuit depuis la noyade de Gueffroy.',
    ],

    'entities' => [
        'city' => [
            'name' => 'Pierrecendre',
            'summary' => 'Ville-port bâtie sur la coulée de cendre d’un volcan éteint.',
            'description' => "Quinze mille âmes, deux collines et une baie en demi-lune. On y vit du sel, du verre et des serments : tout contrat passé à la Halle y est gravé sur une tuile de cendre vitrifiée.\n\nLa ville sent le varech et le soufre froid. Les rues hautes appartiennent aux maisons de négoce, les rues basses à ceux qui travaillent l’eau.",
            'gm_notes' => 'Le vrai pouvoir est à la Halle, pas à la Garde. Si les joueurs menacent la Garde, Mornevent cède ; s’ils menacent la Halle, toute la ville se referme.',
        ],
        'hall' => [
            'name' => 'La Halle des Serments',
            'summary' => 'Bâtiment de pierre claire où les serments de la ville sont gravés et conservés.',
            'description' => 'Une nef sans dieu, remplie d’étagères de tuiles vitrifiées. Chaque tuile porte un serment, son jour et ses témoins. On y entre tête nue, on en sort lié.',
            'gm_notes' => 'Les tuiles de l’année de la grande brume ont été retirées. Elzevir sait où elles sont : dans la cave du phare, pas à la Halle.',
        ],
        'quay' => [
            'name' => 'Le Quai des Lanternes',
            'summary' => 'Le quai des pêcheurs, éclairé toute la nuit par des lanternes à huile de poisson.',
            'description' => 'Trente lanternes, allumées au crépuscule par un gamin payé à la semaine. Quand l’une s’éteint, les anciens rentrent chez eux sans finir leur verre.',
            'gm_notes' => 'La troisième lanterne en partant du nord n’est jamais rallumée : c’est le signal du Passeur.',
        ],
        'marshes' => [
            'name' => 'Les Marches noyées',
            'summary' => 'Marais salés qui séparent Pierrecendre du continent, praticables à marée basse.',
            'description' => 'Trois heures de chemin sûr par marée, douze heures d’attente sinon. Des perches plantées marquent le gué ; quelqu’un les déplace.',
            'gm_notes' => 'Les perches sont déplacées par les Serments brisés, pour que les voyageurs se perdent et disparaissent.',
        ],
        'lighthouse' => [
            'name' => 'Le Phare d’Orvent',
            'summary' => 'Phare abandonné sur la pointe sud, dont la lanterne s’allume encore certaines nuits.',
            'description' => 'Trente-deux mètres de pierre, un escalier en spirale, une cave inondée à marée haute.',
            'gm_notes' => 'Les tuiles disparues de la Halle sont dans la cave, dans une caisse à sel. L’Inconnu les garde.',
        ],
        'ysane' => [
            'name' => 'Dame Ysane Korr',
            'summary' => 'Gardienne des serments : elle grave les tuiles et témoigne des contrats.',
            'description' => 'Soixante ans, mains brûlées par le four à vitrifier, une mémoire que personne n’ose contredire.',
            'gm_notes' => 'C’est elle qui a fait retirer les tuiles de l’année de la grande brume : son propre nom est sur l’une d’elles. Elle n’est pas méchante, elle est terrifiée.',
            'fields' => [
                'trade' => 'Gardienne des serments',
                'trait' => 'Ne regarde jamais deux fois la même personne dans les yeux',
                'hidden_oath' => 'A juré, il y a trente ans, de laisser les Noyeux prendre une barque par an. La ville n’a plus de naufrages depuis.',
                'betrayal' => 'La sécurité de sa petite-fille',
            ],
        ],
        'brannoc' => [
            'name' => 'Brannoc le Passeur',
            'summary' => 'Passe les gens et les caisses par les marches, à l’heure qui l’arrange.',
            'description' => 'Grand, lent, parle peu et compte vite. Connaît le gué par cœur, même déplacé.',
            'gm_notes' => 'Il sait que les perches bougent. Il se taira jusqu’à ce qu’on lui propose de racheter sa dette au Fil Gris.',
            'fields' => [
                'trade' => 'Passeur',
                'trait' => 'Ne jure jamais, ce qui en ville passe pour une insulte',
                'hidden_oath' => 'Doit onze ans de passages gratuits à la Compagnie du Fil Gris.',
                'betrayal' => 'L’effacement de sa dette',
            ],
        ],
        'elzevir' => [
            'name' => 'Maître Elzevir',
            'summary' => 'Archiviste de la Halle, qui sait lire les tuiles les plus anciennes.',
            'description' => 'Petit, poudré de cendre, incapable de mentir sans tousser.',
            'gm_notes' => 'Il a recopié les tuiles retirées avant qu’on les emporte. Sa copie est dans la doublure de son manteau.',
            'fields' => [
                'trade' => 'Archiviste',
                'trait' => 'Tousse quand il ment',
                'hidden_oath' => 'A juré à Ysane de ne jamais parler de l’année de la grande brume.',
                'betrayal' => 'Qu’on lui promette que les tuiles seront remises à leur place',
            ],
        ],
        'vanne' => [
            'name' => 'Sœur Vanne',
            'summary' => 'Soigne les noyés et les brûlés, sans demander de quel côté ils sont.',
            'description' => 'Tient une salle de six lits au-dessus d’une corderie.',
            'gm_notes' => 'Elle a soigné deux Serments brisés la semaine dernière. Elle ne le dira qu’en échange de sel et de bandes.',
            'fields' => [
                'trade' => 'Soigneuse',
                'trait' => 'Appelle tout le monde « petit »',
            ],
        ],
        'mornevent' => [
            'name' => 'Capitaine Hald Mornevent',
            'summary' => 'Commande la Garde des Quais, vingt-deux hommes et une barque.',
            'description' => 'Compétent, fatigué, parfaitement conscient qu’il n’a pas les moyens de sa charge.',
            'gm_notes' => 'Il couvre la disparition de trois voyageurs pour ne pas affoler la ville. Il acceptera de l’aide si on la lui offre sans public.',
            'fields' => [
                'trade' => 'Capitaine de la Garde',
                'trait' => 'Note tout dans un carnet qu’il ne relit jamais',
                'hidden_oath' => 'A promis au conseil que personne ne disparaîtrait sous son commandement.',
                'betrayal' => 'Sauver la face devant le conseil',
            ],
        ],
        'stranger' => [
            'name' => 'L’Inconnu du Phare',
            'summary' => 'Celui qui rallume la lanterne du phare d’Orvent. Personne ne l’a vu de près.',
            'gm_notes' => 'C’est Gueffroy, le gamin aux lanternes, noyé il y a six mois et rendu par les Noyeux. Il garde les tuiles et n’attend qu’une chose : qu’on lise son nom à voix haute.',
            'fields' => [
                'trade' => 'Allumeur de lanternes',
                'trait' => 'Sent le sel froid',
                'hidden_oath' => 'A juré, en mourant, de rallumer les lanternes jusqu’à ce qu’on lui rende son nom.',
            ],
        ],
        'drowned' => [
            'name' => 'Les Noyeux',
            'summary' => 'Ce qui remonte des marches quand la brume tient plus de trois jours.',
            'description' => 'On les décrit comme des silhouettes qui marchent sous l’eau peu profonde, à hauteur d’homme.',
            'gm_notes' => 'Ils ne tuent pas : ils réclament. Un Noyeux relâche sa prise si on tient à sa place le serment qu’il est venu chercher.',
        ],
        'seal' => [
            'name' => 'Le Sceau de cendre',
            'summary' => 'Le poinçon qui grave les tuiles de la Halle. Sans lui, aucun serment n’est valide.',
            'description' => 'Un cylindre de verre noir, lourd, gravé en creux des armes de la ville.',
            'gm_notes' => 'Ysane l’a caché. Le rendre public clôt la campagne par la négociation ; le détruire la clôt par la rupture.',
        ],
        'greythread' => [
            'name' => 'La Compagnie du Fil Gris',
            'summary' => 'Maison de négoce qui achète des dettes et revend des services.',
            'description' => 'Trois comptoirs, aucun navire en propre, et un carnet de dettes plus épais que le registre de la ville.',
            'gm_notes' => 'Veut le Sceau de cendre : qui grave les serments fixe le prix des dettes.',
        ],
        'broken' => [
            'name' => 'Les Serments brisés',
            'summary' => 'Ceux qui ont rompu un serment et vivent désormais hors de la ville, dans les marches.',
            'gm_notes' => 'Ils déplacent les perches pour que la ville ait enfin peur de l’eau. Leur meneuse est la fille d’Ysane.',
        ],
        'guard' => [
            'name' => 'La Garde des Quais',
            'summary' => 'Vingt-deux hommes chargés du port, des lanternes et du couvre-feu.',
            'gm_notes' => 'Deux d’entre eux sont payés par le Fil Gris. Mornevent ne le sait pas.',
        ],
        'teska' => [
            'name' => 'Teska la Rameuse',
            'summary' => 'Rame depuis l’enfance, connaît la baie mieux que la Garde.',
            'description' => 'Vous avez juré à votre frère de ne jamais quitter Pierrecendre. Il est parti le mois dernier.',
            'fields' => [
                'trade' => 'Rameuse',
                'trait' => 'Dit tout, tout de suite',
                'ties' => 'Son frère, parti sans un mot. Brannoc, qui lui doit une barque.',
            ],
        ],
        'oriel' => [
            'name' => 'Oriel Chantegrèle',
            'summary' => 'Témoin de métier : on le paie pour assister aux serments et s’en souvenir.',
            'description' => 'Vous avez témoigné de deux cents serments. Vous en avez oublié un seul, exprès.',
            'fields' => [
                'trade' => 'Témoin',
                'trait' => 'Répète les phrases importantes à voix basse',
                'ties' => 'Maître Elzevir, son maître d’apprentissage. Le Fil Gris, qui l’emploie trop souvent.',
            ],
        ],
        'dorn' => [
            'name' => 'Dorn Fer-Froid',
            'summary' => 'Ancien garde, renvoyé pour avoir refusé d’appliquer un couvre-feu.',
            'description' => 'Vous avez juré de ne plus jamais obéir à un ordre que vous ne comprenez pas.',
            'fields' => [
                'trade' => 'Garde renvoyé',
                'trait' => 'Se place toujours entre la porte et les autres',
                'ties' => 'Mornevent, qui l’a renvoyé à regret. Sœur Vanne, qui l’a recousu deux fois.',
            ],
        ],
        'lisenn' => [
            'name' => 'Lisenn aux Deux Noms',
            'summary' => 'Vient des marches, vit en ville sous un nom qui n’est pas le sien.',
            'description' => 'Vous avez rompu un serment. Personne ici ne le sait encore.',
            'fields' => [
                'trade' => 'Guide des marches',
                'trait' => 'Ne dort jamais deux nuits au même endroit',
                'ties' => 'Les Serments brisés, qu’elle a quittés. Lisenn, la morte dont elle porte le nom.',
            ],
        ],
    ],

    // Libellé de la relation, puis son libellé inverse (null : pas d'inverse).
    'relations' => [
        'ysane_hall' => ['garde', 'gardée par'],
        'elzevir_hall' => ['travaille à', 'emploie'],
        'elzevir_ysane' => ['a juré le silence à', 'tient par un serment'],
        'brannoc_marshes' => ['connaît le gué de', 'traversées par'],
        'brannoc_greythread' => ['doit une dette à', 'détient la dette de'],
        'mornevent_guard' => ['commande', 'commandée par'],
        'guard_quay' => ['surveille', 'surveillé par'],
        'greythread_guard' => ['achète deux hommes de', null],
        'greythread_seal' => ['convoite', 'convoité par'],
        'broken_marshes' => ['vivent dans', 'abritent'],
        'broken_ysane' => ['sont menés par sa fille', null],
        'drowned_marshes' => ['remontent de', null],
        'drowned_ysane' => ['ont un serment avec', null],
        'stranger_lighthouse' => ['rallume', 'rallumé par'],
        'stranger_quay' => ['allumait les lanternes de', null],
        'vanne_broken' => ['en a soigné deux', null],
        'hall_city' => ['se dresse à', 'abrite'],
        'quay_city' => ['borde', 'ouvre sur'],
        'seal_hall' => ['grave les tuiles de', null],
        'teska_brannoc' => ['lui a prêté une barque', 'lui doit une barque'],
        'oriel_elzevir' => ['a été son apprenti', 'a formé'],
        'dorn_mornevent' => ['a servi sous', 'a renvoyé'],
        'lisenn_broken' => ['les a quittés', null],
    ],

    'documents' => [
        'tile' => [
            'title' => 'Tuile 1147 — serment de la grande brume',
            'description' => 'Le relevé qu’Elzevir a recopié avant que les tuiles ne quittent la Halle.',
            'lines' => [
                'Relevé de la tuile 1147, Halle des Serments de Pierrecendre.',
                '',
                'Jurante : Ysane Korr, gardienne.',
                'Serment : « Une barque par an, et la baie restera calme. »',
                'Témoins : Elzevir, archiviste. Gueffroy, allumeur de lanternes.',
                '',
                'Note de l’archiviste : tuile retirée du rayon le 3 du mois du sel.',
            ],
        ],
        'notice' => [
            'title' => 'Avis de couvre-feu',
            'description' => 'Placardé sur le Quai des Lanternes. À montrer aux joueurs dès la première scène.',
            'lines' => [
                'Par ordre du capitaine Hald Mornevent, Garde des Quais.',
                '',
                'Le Quai des Lanternes est fermé de la dernière lanterne à l’aube.',
                'Nul ne prend la mer sans un billet de la Garde.',
                'Toute lanterne éteinte doit être signalée au poste.',
                '',
                'Cet avis vaut serment : qui l’enfreint répond devant la Halle.',
            ],
        ],
        'tides' => [
            'title' => 'Table des marées des Marches noyées',
            'description' => 'Aide de jeu : trois heures de gué par marée basse.',
            'lines' => [
                'Marches noyées — passage du gué',
                '',
                'Marée basse : trois heures de chemin sûr, perches visibles.',
                'Marée montante : une heure de sursis, eau à mi-cuisse.',
                'Marée haute : aucun passage. Douze heures d’attente.',
                '',
                'Les perches sont replantées chaque mois par la Garde.',
            ],
        ],
        'plan' => [
            'title' => 'Plan du port de Pierrecendre',
            'description' => 'Le port, ses quais et la pointe du phare. Montrable à la table.',
            'file' => 'plan-du-port',
        ],
    ],

    'map' => [
        'name' => 'Le port de Pierrecendre',
        'unit' => 'm',
    ],

    'rules' => [
        'roll' => [
            'title' => 'Jet de serment',
            'category' => 'Base',
            'summary' => 'Caractéristique + 1 d6 contre une difficulté de 4 à 9.',
            'procedure' => "1. Annoncez la caractéristique employée et ce que le personnage veut obtenir.\n2. Lancez 1 d6 et ajoutez la caractéristique.\n3. 4 pour une tâche de métier, 7 pour une tâche difficile, 9 pour l’impossible.\n4. Si le personnage agit pour tenir un serment, ajoutez +1 par serment tenu, jusqu’à +3.",
            'source' => 'Livret de base, p. 12',
        ],
        'breath' => [
            'title' => 'Souffle',
            'category' => 'Base',
            'summary' => 'Le Souffle remplace les points de vie : il se dépense pour tenir, pas pour encaisser.',
            'procedure' => "Dépensez 1 Souffle pour relancer un dé, pour continuer malgré une blessure, ou pour refuser un Noyeux.\nÀ 0, le personnage s’arrête : il n’est pas mort, il ne peut plus rien promettre jusqu’au prochain repos.",
            'source' => 'Livret de base, p. 18',
        ],
        'breaking' => [
            'title' => 'Rompre un serment',
            'category' => 'Serments',
            'summary' => 'Rompre un serment donne un avantage immédiat et un prix durable.',
            'procedure' => "Le joueur décrit ce que la rupture lui permet : il l’obtient, sans jet.\nPuis il perd tous ses serments tenus, et la table note qui l’a appris.",
            'gm_notes' => 'Ne jamais refuser une rupture. Le prix se paie dans la fiction, par la réaction de ceux qui apprennent.',
            'source' => 'Livret de base, p. 24',
        ],
        'mist' => [
            'title' => 'Compte de la brume',
            'category' => 'Maison',
            'summary' => 'Règle maison : la brume monte d’un cran à chaque séance, jusqu’à ce que les Noyeux marchent en ville.',
            'procedure' => "Tenez un compte de 0 à 6, visible de la table.\n+1 à chaque fin de séance, +1 chaque fois qu’un serment est rompu devant témoin.\nÀ 3, le gué devient incertain. À 6, les Noyeux entrent dans Pierrecendre.",
            'gm_notes' => 'Compte à l’écran de table, en secret jusqu’à 3.',
        ],
        'word' => [
            'title' => 'Parole donnée à la table',
            'category' => 'Maison',
            'summary' => 'À tester : une promesse faite par le joueur à voix haute compte comme serment.',
            'procedure' => 'Quand un joueur promet quelque chose à un personnage, notez-le. S’il la tient, +1 serment tenu ; sinon, la rupture s’applique.',
            'gm_notes' => 'À tester séance 2. Risque : les joueurs n’osent plus rien promettre.',
        ],
    ],

    'scenario' => [
        'name' => 'Le Serment de Pierrecendre',
        'summary' => 'Trois séances : une lanterne éteinte, un gué qui ment, un phare qui réclame un nom.',
    ],

    'chapters' => [
        's1' => 'Séance 1 — La lanterne éteinte',
        's2' => 'Séance 2 — Le gué qui ment',
        's3' => 'Séance 3 — Le nom rendu',
    ],

    // « notes » : la note de chaque fiche dans la scène, par clé de fiche (absente : pas de note).
    'scenes' => [
        'lantern' => [
            'name' => 'La troisième lanterne',
            'description' => 'Au crépuscule, sur [[quay]], la troisième lanterne en partant du nord refuse de s’allumer. L’avis de couvre-feu est encore frais sur le mur.',
            'gm_notes' => 'C’est le signal de [[brannoc]]. Laissez les joueurs le découvrir en observant qui s’approche du quai.',
            'notes' => ['brannoc' => 'arrive par l’eau, sans bruit', 'guard' => 'deux hommes en ronde'],
        ],
        'register' => [
            'name' => 'Le registre refusé',
            'description' => 'À [[hall]], [[ysane]] refuse l’accès au rayon de l’année de la grande brume. [[elzevir]] tousse.',
            'gm_notes' => 'Elzevir cédera s’il est pris à part, hors de la vue d’Ysane. Sinon il tousse et change de sujet.',
            'notes' => ['ysane' => 'derrière le pupitre', 'elzevir' => 'dans les rayons'],
        ],
        'poles' => [
            'name' => 'Les perches déplacées',
            'description' => 'Dans [[marshes]], à marée basse, deux perches manquent et une troisième a été replantée de travers. La brume tient depuis quatre jours.',
            'gm_notes' => 'Un jet d’Esprit à 7 repère la supercherie. En cas d’échec, la marée monte sur un joueur : occasion de dépenser du Souffle.',
            'notes' => ['brannoc' => 'sait, et se tait', 'broken' => 'les observent de loin'],
        ],
        'notebook' => [
            'name' => 'Le carnet de Mornevent',
            'description' => '[[mornevent]] reçoit, à contrecœur, dans le poste de [[guard]]. Trois noms sont rayés dans son carnet.',
            'gm_notes' => 'Il parle s’il n’y a pas de témoin. Les trois noms sont ceux des voyageurs disparus au gué.',
            'notes' => ['marshes' => 'évoquées, pas visitées'],
        ],
        'ward' => [
            'name' => 'La salle aux six lits',
            'description' => 'Chez [[vanne]], deux blessés récents sentent le sel. Elle échange ce qu’elle sait contre du sel et des bandes.',
            'notes' => ['broken' => 'deux d’entre eux, soignés la semaine passée'],
        ],
        'cellar' => [
            'name' => 'La cave du phare',
            'description' => 'La cave de [[lighthouse]] est inondée à marée haute. Dans une caisse à sel : les tuiles retirées de la Halle.',
            'gm_notes' => 'La tuile 1147 est au-dessus de la pile, en évidence. [[stranger]] attend qu’on la lise à voix haute.',
            'notes' => ['stranger' => 'en haut de l’escalier', 'seal' => 'absent de la caisse'],
        ],
        'rising' => [
            'name' => 'Ce qui remonte',
            'description' => '[[drowned]] marchent dans la baie, à hauteur d’homme, droit vers [[city]]. Le compte de la brume est à 6.',
            'gm_notes' => 'Ils s’arrêtent si quelqu’un tient à la place d’Ysane le serment de la tuile 1147, ou si le nom de Gueffroy est dit devant témoin.',
            'notes' => ['ysane' => 'sur le quai, sans son sceau'],
        ],
        'recast' => [
            'name' => 'Le serment refondu',
            'description' => 'À [[hall]], devant la ville : rendre [[seal]] à la Halle, ou le briser.',
            'gm_notes' => 'Deux fins, aucune bonne. Le rendre : la ville tient, Ysane tombe. Le briser : plus aucun serment ne lie personne, et le Fil Gris achète tout.',
            'notes' => ['greythread' => 'présente, attend son tour'],
        ],
    ],

    'secrets' => [
        'ysane_oath' => [
            'title' => 'Ysane a juré une barque par an aux Noyeux',
            'body' => 'Il y a trente ans, Ysane Korr a promis aux Noyeux une barque par an pour que la baie reste calme. La tuile 1147 en porte le texte, et son nom.',
        ],
        'stranger' => [
            'title' => 'L’Inconnu du Phare est Gueffroy, l’allumeur noyé',
            'body' => 'Gueffroy était l’enfant payé pour allumer les lanternes du quai. Noyé il y a six mois, il a été rendu par les Noyeux. Il rallume le phare et attend qu’on dise son nom.',
        ],
        'poles' => [
            'title' => 'Les perches du gué sont déplacées exprès',
            'body' => 'Les Serments brisés déplacent les perches pour que Pierrecendre ait peur de son eau. Trois voyageurs y ont déjà disparu.',
        ],
        'daughter' => [
            'title' => 'La meneuse des Serments brisés est la fille d’Ysane',
            'body' => 'Celle qui mène les Serments brisés est la fille de la gardienne. C’est pour elle qu’Ysane a caché les tuiles, et pour elle qu’elle trahirait la ville.',
        ],
        'bought' => [
            'title' => 'Le Fil Gris a acheté deux gardes du quai',
            'body' => 'Deux hommes de la Garde des Quais sont payés par la Compagnie du Fil Gris. Mornevent l’ignore, et le découvrir le brisera.',
        ],
    ],

    // Date libre, titre, description.
    'timeline' => [
        'ash' => ['Il y a 300 ans', 'La cendre recouvre la baie', 'L’éruption du mont Orvent éteint le volcan et donne à la ville son sol gris.'],
        'first_oath' => ['Il y a 180 ans', 'Premier serment gravé', 'La Halle est bâtie et le premier serment est vitrifié sur une tuile de cendre.'],
        'great_mist' => ['Il y a 30 ans', 'L’année de la grande brume', 'Une brume de huit mois, onze barques perdues, puis plus aucun naufrage pendant trente ans.'],
        'tile_1147' => ['Il y a 30 ans', 'Le serment de la tuile 1147', 'Ysane Korr promet aux Noyeux une barque par an. Deux témoins : Elzevir et Gueffroy.'],
        'drowning' => ['Il y a six mois', 'Gueffroy se noie au quai', 'L’allumeur de lanternes tombe du Quai des Lanternes. Son corps n’est pas retrouvé.'],
        'missing' => ['Le mois dernier', 'Trois voyageurs manquent au gué', 'Mornevent raye trois noms dans son carnet et ne prévient pas le conseil.'],
        'session1' => ['Séance 1', 'La troisième lanterne reste éteinte', 'Les personnages repèrent le signal du Passeur et se voient refuser le rayon de la grande brume.'],
        'poles_moved' => ['Séance 2', 'Les perches sont déplacées', 'Si personne n’intervient, un quatrième voyageur disparaît dans les marches.'],
        'invasion' => ['Séance 3', 'Les Noyeux entrent en ville', 'Au compte de brume 6, ils remontent la baie et marchent jusqu’à la Halle.'],
        'ending' => ['Fin', 'Le sceau rendu ou brisé', 'Rendre le Sceau fait tomber Ysane ; le briser libère la ville de tout serment, et le Fil Gris de toute limite.'],
    ],
];
