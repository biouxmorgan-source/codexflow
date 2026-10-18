<?php

namespace App\Support;

use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Mode d'emploi court des écrans qui ne s'expliquent pas tout seuls : le bouton « ? »
 * à côté du titre (composant x-page-help). Quelques lignes, puis un lien vers la question
 * correspondante de la page « Aide ». Les lignes « gm » ne s'affichent qu'au MJ.
 */
class PageHelp
{
    /** @return list<string> */
    public static function topics(): array
    {
        return ['entity-types', 'fields', 'tags', 'dice', 'maps', 'table', 'reveals', 'entity', 'session', 'scenarios', 'imports', 'members'];
    }

    /** @return array{title: string, lines: list<string>, gm: list<string>, faq: string} */
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
                ],
                'gm' => [],
                'faq' => __('À quoi sert le mode Session ?'),
            ],
            'scenarios' => [
                'title' => __('Scénarios et scènes'),
                'lines' => [
                    __('Un scénario découpe la campagne ; ses scènes se rangent par chapitre si vous le souhaitez, dans l’ordre où vous comptez les jouer.'),
                    __('Le statut suit l’avancée : Prévue, Disponible, En cours, Jouée ou Ignorée. Le mode Session part de la scène en cours.'),
                    __('Une scène rassemble ce qui sert pour la jouer : fiches avec une note courte, documents, règles, sons, et la liste « À jouer ». Les indices qui la concernent s’affichent à côté.'),
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
            default => throw new InvalidArgumentException("Aide inconnue : {$topic}"),
        };
    }

    /** Ancre d'une question de la page « Aide », dans la langue affichée. */
    public static function anchor(string $question): string
    {
        return Str::slug($question);
    }
}
