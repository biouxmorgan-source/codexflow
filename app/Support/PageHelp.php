<?php

namespace App\Support;

use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Mode d'emploi court des écrans qui ne s'expliquent pas tout seuls : le bouton « ? »
 * à côté du titre (composant x-page-help). Quelques lignes, puis un lien vers la question
 * correspondante de la page « Aide ». Les lignes « gm » ne s'affichent qu'au MJ, les lignes « player » qu'aux autres.
 */
class PageHelp
{
    /** @return list<string> */
    public static function topics(): array
    {
        return ['entity-types', 'fields', 'tags', 'dice', 'maps', 'table', 'reveals', 'entity', 'session', 'scenarios', 'imports', 'members', 'audio', 'graph', 'timeline', 'feedback', 'ai', 'journal', 'character', 'my-sheets', 'campaign-map', 'campaign', 'book'];
    }

    /** @return array{title: string, lines: list<string>, gm: list<string>, player?: list<string>, faq: string} */
    public static function topic(string $topic): array
    {
        return match ($topic) {
            'entity-types' => [
                'title' => __('Types de fiche'),
                'lines' => [
                    __('Le type dit ce qu’est une fiche : PNJ, Lieu, Objet… Les six types standards sont toujours là, ajoutez les vôtres (Faction, Sort, Vaisseau…).'),
                    __('Les cases à remplir sur les fiches (caractéristiques, compteurs…) se créent dans « Champs du jeu », depuis la page d’une campagne, et se rattachent à un ou plusieurs types.'),
                    __('Une fiche a un seul type, ce qu’elle est. Pour la ranger autrement (« acte 1 », « à préparer »), utilisez les tags.'),
                    __('Un type encore utilisé par des fiches ne peut pas être supprimé : changez d’abord le type de ces fiches.'),
                ],
                'gm' => [],
                'faq' => __('Comment définir les caractéristiques de mon jeu ?'),
            ],
            'fields' => [
                'title' => __('Champs du jeu'),
                'lines' => [
                    __('Un champ est une case des fiches : texte, nombre, liste de choix, compteur (PV, munitions, argent), date, lien, document ou référence à une autre fiche.'),
                    __('Les champs appartiennent au jeu : ils apparaissent dans toutes les campagnes qui utilisent ce jeu.'),
                    __('Choisissez les types de fiche concernés : un champ peut ne servir qu’aux personnages, ou aux lieux.'),
                    __('Zone publique : visible des joueurs qui connaissent la fiche. Zone MJ : jamais montrée aux joueurs.'),
                    __('« Modifiable par le joueur » laisse le joueur changer la valeur sur son propre personnage. Les dés peuvent lire les nombres et compteurs publics comme bonus ou seuil.'),
                ],
                'gm' => [],
                'faq' => __('Comment définir les caractéristiques de mon jeu ?'),
            ],
            'tags' => [
                'title' => __('Tags'),
                'lines' => [
                    __('Un tag est une étiquette libre pour classer et filtrer : « acte 1 », « faction », « à préparer ».'),
                    __('Ils s’ajoutent depuis une fiche, une scène, une règle, un document ou un son. Cette page sert à les entretenir, pour toutes vos campagnes.'),
                    __('Différence avec les types : une fiche a un seul type, ce qu’elle est, mais autant de tags que vous voulez, selon votre façon de ranger.'),
                    __('« Fusionner » réunit deux tags en un seul (« PNJ » et « pnj ») ; la couleur se retrouve partout où le tag apparaît.'),
                ],
                'gm' => [],
                'faq' => __('À quoi servent les tags ?'),
            ],
            'dice' => [
                'title' => __('Dés'),
                'lines' => [
                    __('Choisissez les dés (1d20 + 2d6…) puis comment les lire. Brut : chaque dé tel qu’il tombe. Additionner : la somme. Seuil sur le total : la somme comparée à un nombre. Seuil par dé : le nombre de dés qui réussissent.'),
                    __('Le bonus peut venir de la fiche du personnage (une caractéristique entière, sa moitié, son cinquième…) ou de bonus nommés comme « Arme +2 ».'),
                    __('« Enregistrer » range le lancer dans « Mes lancers », pour le relancer d’un geste.'),
                    __('Discret : seuls le MJ et vous voyez le résultat. Sinon toute la table le voit, et l’écran de table l’affiche.'),
                ],
                'gm' => [
                    __('« Demander » envoie un lancer à toute la table ou à certains joueurs : il s’ouvre chez eux, avec un seuil caché si vous le souhaitez.'),
                ],
                'faq' => __('Comment faire lancer les dés ?'),
            ],
            'maps' => [
                'title' => __('Cartes'),
                'lines' => [
                    __('Une carte est une image (plan, région) que vous montrez sur l’écran de table, avec une grille et des jetons si besoin.'),
                    __('Grille : réglez la taille des cases sur l’image, puis « Taille réelle d’une case » (1,5 m, 5 pieds…) pour que la règle et les gabarits mesurent juste.'),
                    __('Jetons : un nouveau jeton arrive masqué, montrez-le quand les joueurs doivent le voir. « Déplacé par » choisit qui peut le bouger : le MJ seul, ou aussi le joueur du personnage, depuis son appareil.'),
                    __('Gabarits de zone : cercle, carré, rectangle, cône ou ligne, pour montrer une zone d’effet.'),
                    __('« Afficher à la table » l’envoie sur l’écran de table. Pour les joueurs absents, la télécommande donne les liens « Rejoindre à distance ».'),
                    __('« Carte de campagne » relie vos cartes entre elles par des points d’intérêt : un clic sur la vue d’ensemble ouvre la sous-carte.'),
                ],
                'gm' => [],
                'faq' => __('Comment utiliser une carte avec une grille et des jetons ?'),
            ],
            'table' => [
                'title' => __('Écran de table et télécommande'),
                'lines' => [
                    __('L’écran de table est une page à ouvrir sur la télé ou un second écran (« Écran ↗ ») : il n’affiche que ce que vous lui envoyez, jamais vos notes.'),
                    __('Depuis une carte, une image, un document ou une fiche, « Afficher à la table » l’y envoie.'),
                    __('La télécommande pilote l’écran depuis votre téléphone : élément suivant de la scène, zoom, déplacement de la carte, jetons, annonce, ambiance, « Vider l’écran ».'),
                    __('« Rejoindre à distance » : un lien (et son QR code) pour que les joueurs suivent l’écran sur leur appareil avec leur compte, et un lien invité sans compte, en lecture seule, que vous pouvez révoquer.'),
                ],
                'gm' => [],
                'faq' => __('Puis-je piloter l’écran de table depuis mon téléphone ?'),
            ],
            'reveals' => [
                'title' => __('Révéler, Donner et Indices'),
                'lines' => [
                    __('« Révéler » (sur une fiche, un document, une règle ou en mode Session) fait connaître l’élément aux personnages choisis ; « Donner » met un objet dans leurs possessions. Ils sont prévenus aussitôt.'),
                    __('Un indice est une information à part (« Morel travaille pour le Culte »), reliée aux fiches, scènes ou documents concernés. Un clic sur un personnage le lui révèle, un autre clic annule.'),
                    __('Ce qui est révélé appartient au personnage, pas au joueur : le joueur le retrouve sur la page de son personnage.'),
                    __('« Historique des révélations » dit qui a appris quoi, quand et pendant quelle séance. « Voir comme », sur un personnage, montre exactement ce que son joueur voit.'),
                ],
                'gm' => [],
                'faq' => __('Comment révéler une information ou donner un objet ?'),
            ],
            'entity' => [
                'title' => __('La fiche'),
                'lines' => [
                    __('Zone publique : ce que les joueurs verront quand vous leur révélerez la fiche. Zone MJ : vos secrets, qui ne quittent jamais le serveur pour un joueur.'),
                    __('Une fiche du monde sert à toutes vos campagnes. « Dans cette campagne » garde un statut, des notes et des valeurs propres à celle-ci, sans toucher au monde ni aux autres campagnes.'),
                    __('Relations, tags, documents et fichiers joints relient la fiche au reste ; « Cité dans » montre les scènes, règles et fiches qui la mentionnent.'),
                    __('« Révéler » ou « Donner » la transmet aux personnages choisis. Pour vérifier ce qu’un joueur voit, ouvrez son personnage et « Voir comme ».'),
                ],
                'gm' => [],
                'faq' => __('Une fiche du monde peut-elle changer dans une seule campagne ?'),
            ],
            'session' => [
                'title' => __('Mode Session'),
                'lines' => [
                    __('C’est votre écran pendant la partie : « Démarrer la session » ouvre une séance, puis la scène en cours affiche ses fiches, documents, règles, indices et sons.'),
                    __('« À jouer » liste ce que vous vouliez placer (une lettre, un orage…) : cochez au fur et à mesure. « Scène jouée, suivante → » passe à la scène d’après.'),
                    __('Les notes de session se prennent vite (touche N) et restent rattachées à la scène.'),
                    __('Le bloc « Écran de table » y montre une carte, un document, une fiche ou une annonce ; « Partager avec les joueurs » le fait suivre sur leur appareil. Le mode focus masque tout le reste.'),
                    __('« Terminer la session » range tout dans la page de la séance, où vous pouvez écrire son résumé.'),
                    __('« Organiser l’écran » vous laisse déplacer les blocs (poignée ⠿ ou flèches), d’une colonne à l’autre, et masquer ceux dont vous ne vous servez pas. Cette disposition n’appartient qu’à vous.'),
                ],
                'gm' => [],
                'faq' => __('À quoi sert le mode Session ?'),
            ],
            'campaign' => [
                'title' => __('Page de la campagne'),
                'lines' => [
                    __('C’est votre point de départ pour préparer : le mode Session, les zones (scénarios, documents, règles, personnages), les outils en icônes et l’univers des fiches.'),
                    __('« Organiser l’écran » fait apparaître une poignée ⠿ sur chaque bloc : glissez-la, ou utilisez les flèches, pour changer l’ordre. « Masquer » retire un bloc, « Afficher » le fait revenir, « Disposition par défaut » remet tout en place.'),
                    __('Cette disposition n’appartient qu’à vous et vaut pour toutes vos campagnes : les joueurs et les autres MJ gardent la leur. Rien n’est effacé.'),
                ],
                'gm' => [],
                'faq' => __('Puis-je ranger mes écrans à ma façon ?'),
            ],
            'scenarios' => [
                'title' => __('Scénarios et scènes'),
                'lines' => [
                    __('Un scénario découpe la campagne ; ses scènes se rangent par chapitre si vous le souhaitez, dans l’ordre où vous comptez les jouer.'),
                    __('Le statut suit l’avancée : Prévue, Disponible, En cours, Jouée ou Ignorée. Le mode Session part de la scène en cours.'),
                    __('Une scène rassemble ce qui sert pour la jouer : fiches avec une note courte, documents, règles, sons, et la liste « À jouer ». Les indices qui la concernent s’affichent à côté.'),
                    __('Après la partie, « Ce qui s’est passé » raconte ce qui a vraiment eu lieu. Si la table a suivi le plan, cochez simplement « Jouée comme prévu ».'),
                    __('Supprimer un scénario ou une scène ne supprime jamais les fiches liées.'),
                ],
                'gm' => [],
                'faq' => __('Comment préparer un scénario ?'),
            ],
            'imports' => [
                'title' => __('Importer depuis un fichier'),
                'lines' => [
                    __('Choisissez d’abord ce que contient le fichier : des fiches, des champs du jeu, des règles, ou des scénarios et leurs scènes. Format CSV (Excel, LibreOffice, Google Sheets) ou JSON.'),
                    __('Le plus simple : télécharger le fichier exemple, ou exporter ce que vous avez déjà, et le remplir sur le même modèle.'),
                    __('Un aperçu montre ligne par ligne ce qui sera créé ou mis à jour : rien n’est enregistré avant « Importer ».'),
                    __('Vous avez le PDF d’un jeu ou d’un scénario ? « Préparez les fichiers avec une IA » donne le prompt à coller dans l’IA de votre choix. N’importez que des contenus que vous avez le droit d’utiliser.'),
                ],
                'gm' => [],
                'faq' => __('Puis-je importer le contenu d’un livre de jeu ou d’un scénario ?'),
            ],
            'members' => [
                'title' => __('Membres'),
                'lines' => [
                    __('Trois rôles. Maître de jeu : prépare et mène avec le propriétaire, avec accès à la zone MJ. Joueur : joue un personnage et voit ce que ce personnage connaît. Spectateur : ne voit que l’écran de table (une télé, un projecteur).'),
                    __('Une invitation est un lien par personne, à envoyer comme vous voulez (message, Discord, courriel). Elle se crée avec son rôle et peut être annulée tant qu’elle n’a pas servi.'),
                    __('Un joueur arrivé dans la campagne reçoit son personnage depuis la page « Personnages ».'),
                    __('Seul le propriétaire de la campagne invite, change les rôles et retire des membres.'),
                ],
                'gm' => [],
                'faq' => __('Comment inviter mes joueurs ?'),
            ],
            'audio' => [
                'title' => __('Sons'),
                'lines' => [
                    __('Téléversez vos musiques et ambiances (mp3, ogg, m4a, wav ou flac, 50 Mo max.) et rangez-les avec des tags.'),
                    __('Un son qui sert dans plusieurs campagnes va dans « Mes sons », votre bibliothèque : ajoutez-le ici avec « Depuis ma bibliothèque », sans le téléverser à nouveau.'),
                    __('Liez un son à une scène : en mode Session, le bloc « Musique » propose d’abord ceux de la scène en cours.'),
                    __('« Ici » joue le son sur votre appareil ; « Table » le joue sur l’écran de table, branché sur les enceintes. « En boucle » pour une ambiance, « Une fois » pour un effet.'),
                    __('Au premier morceau, l’écran de table peut demander « Activer le son » : c’est le navigateur qui l’exige.'),
                ],
                'gm' => [],
                'faq' => __('Comment lancer une musique pendant la partie ?'),
            ],
            'graph' => [
                'title' => __('Graphe des relations'),
                'lines' => [
                    __('Le graphe dessine les fiches et leurs relations (« travaille pour », « rival de »…), ajoutées depuis les fiches.'),
                    __('Cliquez sur une fiche pour la mettre au centre. « Profondeur » montre 1 à 3 relations autour d’elle, « Type de fiche » filtre. Survolez une fiche pour lire ses relations, glissez-la pour la déplacer.'),
                ],
                'gm' => [
                    __('« Voir comme… » montre le réseau tel qu’un personnage le connaît : seulement les fiches qu’il connaît et les relations visibles entre elles.'),
                ],
                'player' => [
                    __('Vous ne voyez que les fiches que votre personnage connaît, et les relations visibles entre elles.'),
                ],
                'faq' => __('Comment voir qui est lié à qui ?'),
            ],
            'timeline' => [
                'title' => __('Chronologie'),
                'lines' => [
                    __('La chronologie range l’histoire du monde, ce qui doit arriver et ce qui s’est passé à la table, avec des dates libres (« 12 mars 1924 », « Jour 3 »).'),
                ],
                'gm' => [
                    __('Un événement noté pendant une séance (« Noter un événement joué ») est rattaché à la séance et à la scène en cours.'),
                    __('Cochez « Visible des joueurs » pour qu’ils le lisent ; sinon il reste en zone MJ. Dans une description, [[Nom]] crée un lien vers une fiche.'),
                ],
                'player' => [
                    __('Vous y lisez ce que le MJ a rendu visible des joueurs : ce que votre groupe sait de l’histoire du monde et de vos aventures.'),
                ],
                'faq' => __('À quoi sert la chronologie ?'),
            ],
            'feedback' => [
                'title' => __('Avis des joueurs'),
                'lines' => [
                    __('« Demander un avis » porte sur toute la campagne ou sur une séance ; chaque joueur reçoit une notification.'),
                    __('Chacun donne une note de 1 à 5 étoiles, ce qui lui a plu et ce qui pourrait être mieux.'),
                    __('« Réponses anonymes » masque les noms, et les joueurs le savent avant de répondre.'),
                    __('« Clore » arrête les réponses, « Rouvrir » les relance.'),
                ],
                'gm' => [],
                'faq' => __('Comment savoir ce que mes joueurs ont pensé de la partie ?'),
            ],
            'ai' => [
                'title' => __('Assistant IA'),
                'lines' => [
                    __('Choisissez une séance : SagaWyn prépare un texte avec vos notes et le contexte de la campagne.'),
                    __('Copiez-le dans l’IA de votre choix, puis collez sa réponse ici : elle devient des propositions (résumé, événements, relations, statuts, notes, révélations).'),
                    __('Chaque proposition s’accepte, se modifie ou se rejette : rien ne change dans la campagne sans vous.'),
                    __('Avec votre propre clé d’API, dans « Préférences », « Analyser directement » évite le copier-coller. Les appels sont facturés par le fournisseur, sur votre compte.'),
                ],
                'gm' => [],
                'faq' => __('Comment une IA peut-elle m’aider après une séance ?'),
            ],
            'journal' => [
                'title' => __('Journal des modifications'),
                'lines' => [
                    __('Tout ce qui a été créé, modifié ou supprimé dans la campagne, son monde et son jeu s’inscrit ici, avec qui l’a fait et quand.'),
                    __('Filtrez par type d’élément ou cherchez par nom ; « Détails » montre ce qui a changé.'),
                    __('Il n’est visible que des MJ, car il contient la zone MJ. Ce qui concerne un personnage (révélations, dons, échanges) se retrouve aussi dans le « Journal » de sa page.'),
                ],
                'gm' => [],
                'faq' => __('Où relire ce qui s’est passé ?'),
            ],
            'character' => [
                'title' => __('Fiche de personnage'),
                'lines' => [
                    __('Compteurs (PV, munitions, argent…) : − et + retirent ou ajoutent le montant choisi. Seuls les champs que le MJ a rendus modifiables bougent.'),
                    __('Connaissances, possessions, règles et documents : ce que le personnage a appris ou reçu. Ils appartiennent au personnage, pas au joueur.'),
                ],
                'gm' => [
                    __('« Voir comme » montre la fiche exactement comme son joueur la voit, sans la zone MJ.'),
                    __('Ce que le joueur ajoute (une connaissance, un objet, un échange) attend votre validation ici. Ses intentions « À jouer » arrivent dans le mode Session.'),
                ],
                'player' => [
                    __('« + Noter » ou « + Ajouter un objet » : le MJ en est informé et le valide. « Donner » ou « Transmettre » propose un objet ou une connaissance à un autre personnage ; le MJ valide l’échange.'),
                    __('Vos notes se partagent avec vous seul, vous et le MJ, certains joueurs ou toute la table. « À jouer » dit au MJ ce que vous voulez tenter.'),
                    __('« Fil de la campagne » montre ce que toute la table sait ; « Journal » retrace ce qui a changé sur votre fiche.'),
                    __('Sans réseau, la fiche déjà ouverte sur l’appareil reste utilisable : réglez vos compteurs, modifiez vos champs et écrivez des notes. Tout part au retour de la connexion, sans effacer ce que le MJ a changé entre-temps.'),
                ],
                'faq' => __('Où trouver la fiche de mon personnage ?'),
            ],
            'my-sheets' => [
                'title' => __('Mes feuilles de personnage'),
                'lines' => [
                    __('Rangez ici les personnages que vous jouez hors de SagaWyn : vous seul les voyez, ni les MJ ni les autres joueurs de vos campagnes.'),
                    __('Ajoutez la feuille PDF de votre personnage et un portrait, puis, avec « Modifier », les champs que vous voulez garder sous la main.'),
                    __('Rangez les champs dans des blocs nommés (Caractéristiques, Compétences, Équipement…) : « + Nouveau bloc » en crée un, « Ranger dans… » déplace un champ d’un bloc à l’autre.'),
                    __('Un champ « Texte » garde une caractéristique, un métier ou de l’équipement. Un « Compteur » (PV, Santé mentale, munitions) se règle pendant la partie avec − et +, du montant choisi.'),
                    __('Chaque champ peut porter une note et une case à cocher, que vous cochez pendant la partie (une compétence réussie, un sort préparé).'),
                    __('« + Nouvelle note » ajoute un bloc de texte libre : faites-en autant que vous voulez (dettes, rumeurs, contacts…) et placez-les comme les autres blocs.'),
                    __('« Organiser les blocs » : glissez un bloc par sa poignée ⠿, ou utilisez les flèches, pour le placer où vous voulez sur trois colonnes.'),
                    __('Le jeu et la table servent à retrouver vos personnages : touchez le nom d’un jeu pour ne voir que les siens.'),
                ],
                'gm' => [],
                'faq' => __('Puis-je garder les personnages que je joue ailleurs ?'),
            ],
            'campaign-map' => [
                'title' => __('Carte de campagne'),
                'lines' => [
                    __('La carte de campagne est la vue d’ensemble : choisissez une de vos cartes avec « En faire la carte de campagne ».'),
                    __('« Placer des points », puis cliquez sur la carte pour poser un point d’intérêt ; glissez-le pour le déplacer. Molette, + et − zooment, glisser le fond déplace la carte : une image 4K (3840 × 2160) reste nette.'),
                    __('Un point peut mener à une sous-carte (une ville, un donjon) : en « Explorer », un clic l’ouvre, et le fil d’Ariane en haut permet de remonter. Les sous-cartes ont elles-mêmes leurs points, sans limite de profondeur.'),
                    __('Si la carte est à l’écran de table, la sous-carte ouverte depuis un point l’y remplace, et la carte d’au-dessus revient quand vous remontez. Dans la sous-carte, « Ce lieu » garde les scènes et fiches du point à portée de clic.'),
                    __('Reliez un point à autant de scènes et de fiches que vous voulez (le lieu, ses PNJ, les scènes qui s’y jouent) : le point montre le chemin parcouru (✓ tout est joué) et ce qui reste à venir.'),
                    __('Tout reste au MJ. « Visible à la table » affiche seulement le nom du point sur l’écran de table, quand la carte y est montrée.'),
                ],
                'gm' => [],
                'faq' => __('Comment faire une grande carte de campagne avec des sous-cartes ?'),
            ],
            'book' => [
                'title' => __('Livre des aventures'),
                'lines' => [
                    __('Le livre rassemble les scènes jouées, scénario par scénario, puis les résumés des séances.'),
                    __('Pour chaque scène : « Ce qui s’est passé » s’il est écrit, sinon ce qui était prévu, quand elle est cochée « Jouée comme prévu » ou passée en « Jouée ».'),
                    __('« Version rédigée » garde un récit écrit par vous ou par une IA : copiez le texte proposé dans l’IA de votre choix, ou utilisez votre propre clé d’API, à vos frais.'),
                    __('« Imprimer ou enregistrer en PDF » ouvre une page simple : choisissez « Enregistrer au format PDF » comme imprimante.'),
                    __('Le livre contient vos notes MJ : vous seul le voyez. Partagez le PDF si vous le souhaitez.'),
                ],
                'gm' => [],
                'faq' => __('Comment garder une trace de toute la campagne ?'),
            ],
            default => throw new InvalidArgumentException("Aide inconnue : {$topic}"),
        };
    }

    /** Ancre d'une question de la page « Aide », dans la langue affichée. */
    public static function anchor(string $question): string
    {
        return Str::slug($question);
    }
}
