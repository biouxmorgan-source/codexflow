<?php

namespace App\Actions\Demo;

use App\Actions\Campaigns\CreateCampaign;
use App\Enums\FieldType;
use App\Enums\RuleOrigin;
use App\Enums\RuleStatus;
use App\Enums\SceneStatus;
use App\Enums\Zone;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Models\Rule;
use App\Models\Scene;
use App\Models\Secret;
use App\Models\TableMap;
use App\Models\Tag;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Support\Demo\DemoFiles;
use App\Support\TableTheme;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Campagne de démonstration : « Le Serment de Pierrecendre », pour le jeu inventé « Brume & Serment ».
 *
 * Un contenu entièrement original, écrit pour CodexFlow, qui met en scène chaque fonction : champs
 * libres, zones publique et MJ, relations et graphe, chronologie, scénario et scènes, documents,
 * carte avec grille et jetons, secrets, règles, tags, prétirés. Le MJ peut ensuite l'exporter en
 * archive pour la confier à quelqu'un, ou la dupliquer pour s'en servir de base.
 *
 * @phpstan-type DemoEntity array{type: string, name: string, summary: string, description?: ?string, gm_notes?: ?string, color?: ?string, tags?: ?string, fields?: array<string, mixed>, local?: bool}
 */
class LoadDemoCampaign
{
    public const GAME = 'Brume & Serment';

    public const WORLD = 'Vehrmund';

    public const CAMPAIGN = 'Le Serment de Pierrecendre';

    private User $gm;

    private Campaign $campaign;

    /** @var array<string, Entity> */
    private array $entities = [];

    /** @var array<string, FieldDefinition> */
    private array $fields = [];

    /** @var array<string, EntityType> */
    private array $types = [];

    /** @var array<string, Document> */
    private array $documents = [];

    /** @var array<string, Rule> */
    private array $rules = [];

    /** @var array<string, Scene> */
    private array $scenes = [];

    public function __construct(private CreateCampaign $createCampaign) {}

    /** Charge la démonstration dans le compte donné et renvoie la campagne créée. */
    public function handle(User $gm): Campaign
    {
        $this->gm = $gm;

        // Le journal d'activité raconte ce que fait le MJ en partie : une démonstration n'a rien à y écrire.
        return ActivityLog::muted(fn () => DB::transaction(function () {
            $this->campaign = $this->createCampaign->handle($this->gm, [
                'name' => self::CAMPAIGN,
                'description' => 'Campagne de démonstration : trois séances dans la ville-port de Pierrecendre, où un serment oublié revient réclamer son dû. Tout le contenu est original et libre de droits.',
                'new_game_name' => self::GAME,
                'new_world_name' => self::WORLD,
            ]);

            $this->campaign->gameSystem->forceFill([
                'description' => "Jeu d'enquête et de serments, inventé pour la démonstration. Quatre caractéristiques notées de 1 à 5, des serments qui pèsent sur les jets, aucune mécanique propriétaire.",
            ])->save();
            $this->campaign->world->forceFill([
                'description' => 'Un archipel de marches noyées et de ports bâtis sur la cendre, où une parole donnée vaut contrat.',
            ])->save();
            $this->campaign->forceFill(['table_theme' => 'parchemin'])->save();

            $this->types();
            $this->fieldDefinitions();
            $this->entities();
            $this->relations();
            $this->documents();
            $this->rules();
            $this->scenario();
            $this->secrets();
            $this->map();
            $this->timeline();

            return $this->campaign->fresh();
        }));
    }

    private function types(): void
    {
        foreach (['character', 'place', 'organization', 'item', 'creature'] as $key) {
            $this->types[$key] = EntityType::standard($key);
        }

        foreach (['Faction' => 'faction', 'Prétiré' => 'pregen'] as $name => $key) {
            $type = new EntityType(['name' => $name]);
            $type->user_id = $this->gm->id;
            $type->save();
            $this->types[$key] = $type;
        }
    }

    /** Les champs du jeu : quatre caractéristiques, un profil, et deux champs réservés au MJ. */
    private function fieldDefinitions(): void
    {
        $scores = ['1', '2', '3', '4', '5'];
        $definitions = [
            ['Corps', 'Caractéristiques', FieldType::Select, $scores, Zone::Public, false],
            ['Adresse', 'Caractéristiques', FieldType::Select, $scores, Zone::Public, false],
            ['Esprit', 'Caractéristiques', FieldType::Select, $scores, Zone::Public, false],
            ['Cœur', 'Caractéristiques', FieldType::Select, $scores, Zone::Public, false],
            ['Souffle', 'Caractéristiques', FieldType::Counter, null, Zone::Public, true],
            ['Serments tenus', 'Caractéristiques', FieldType::Number, null, Zone::Public, true],
            ['Métier', 'Profil', FieldType::Text, null, Zone::Public, false],
            ['Trait marquant', 'Profil', FieldType::Text, null, Zone::Public, false],
            ['Attaches', 'Profil', FieldType::LongText, null, Zone::Public, true],
            ['Serment caché', 'Secrets', FieldType::LongText, null, Zone::GameMaster, false],
            ['Ce qui le ferait trahir', 'Secrets', FieldType::Text, null, Zone::GameMaster, false],
        ];

        foreach ($definitions as $position => [$name, $group, $type, $options, $zone, $playerEditable]) {
            foreach (['character', 'pregen'] as $for) {
                $definition = $this->campaign->gameSystem->fieldDefinitions()->create([
                    'name' => $name,
                    'group' => $group,
                    'type' => $type,
                    'options' => $options,
                    'zone' => $zone,
                    'entity_type_id' => $this->types[$for]->id,
                    'position' => $position,
                    'player_editable' => $playerEditable,
                ]);

                $this->fields[$for.'.'.$name] = $definition;
            }
        }
    }

    private function entities(): void
    {
        foreach ($this->entityData() as $data) {
            $entity = new Entity([
                'entity_type_id' => $this->types[$data['type']]->id,
                'name' => $data['name'],
                'summary' => $data['summary'],
                'description' => $data['description'] ?? null,
                'gm_notes' => $data['gm_notes'] ?? null,
            ]);
            $entity->owner()->associate($this->gm);

            // Une fiche appartient soit au monde, soit à la campagne : seul l'inconnu du phare est propre à celle-ci.
            if ($data['local'] ?? false) {
                $entity->campaign()->associate($this->campaign);
            } else {
                $entity->world()->associate($this->campaign->world);
            }

            if (isset($data['color'])) {
                $entity->image_path = 'entities/'.Str::random(40).'.png';
                Storage::disk(Entity::FILES_DISK)->put($entity->image_path, DemoFiles::portrait($data['color']));
            }

            $entity->save();

            if (isset($data['fields'])) {
                $prefix = $data['type'] === 'pregen' ? 'pregen.' : 'character.';
                $entity->setFieldValues(collect($data['fields'])
                    ->mapWithKeys(fn (mixed $value, string $name) => [$this->fields[$prefix.$name]->id => $value])
                    ->all());
                $entity->save();
            }

            if (isset($data['tags'])) {
                $entity->tags()->sync(Tag::idsFromInput($this->gm, $data['tags']));
            }

            $this->entities[$data['name']] = $entity;
        }

        // Une différence propre à la campagne, qui ne touche pas la fiche du monde.
        $this->entities['Le Quai des Lanternes']->stateIn($this->campaign)
            ->fill(['status' => 'sous couvre-feu', 'notes' => 'Fermé la nuit depuis la noyade de Gueffroy.'])->save();
    }

    /** @return list<array<string, mixed>> */
    private function entityData(): array
    {
        return [
            [
                'type' => 'place', 'name' => 'Pierrecendre', 'tags' => 'ville, acte 1',
                'summary' => 'Ville-port bâtie sur la coulée de cendre d’un volcan éteint.',
                'description' => "Quinze mille âmes, deux collines et une baie en demi-lune. On y vit du sel, du verre et des serments : tout contrat passé à la Halle y est gravé sur une tuile de cendre vitrifiée.\n\nLa ville sent le varech et le soufre froid. Les rues hautes appartiennent aux maisons de négoce, les rues basses à ceux qui travaillent l’eau.",
                'gm_notes' => 'Le vrai pouvoir est à la Halle, pas à la Garde. Si les joueurs menacent la Garde, Mornevent cède ; si ils menacent la Halle, toute la ville se referme.',
            ],
            [
                'type' => 'place', 'name' => 'La Halle des Serments', 'tags' => 'ville, intrigue',
                'summary' => 'Bâtiment de pierre claire où les serments de la ville sont gravés et conservés.',
                'description' => 'Une nef sans dieu, remplie d’étagères de tuiles vitrifiées. Chaque tuile porte un serment, son jour et ses témoins. On y entre tête nue, on en sort lié.',
                'gm_notes' => 'Les tuiles de l’année de la grande brume ont été retirées. Elzevir sait où elles sont : dans la cave du phare, pas à la Halle.',
            ],
            [
                'type' => 'place', 'name' => 'Le Quai des Lanternes', 'tags' => 'ville, acte 1',
                'summary' => 'Le quai des pêcheurs, éclairé toute la nuit par des lanternes à huile de poisson.',
                'description' => 'Trente lanternes, allumées au crépuscule par un gamin payé à la semaine. Quand l’une s’éteint, les anciens rentrent chez eux sans finir leur verre.',
                'gm_notes' => 'La troisième lanterne en partant du nord n’est jamais rallumée : c’est le signal du Passeur.',
            ],
            [
                'type' => 'place', 'name' => 'Les Marches noyées', 'tags' => 'marches, acte 2',
                'summary' => 'Marais salés qui séparent Pierrecendre du continent, praticables à marée basse.',
                'description' => 'Trois heures de chemin sûr par marée, douze heures d’attente sinon. Des perches plantées marquent le gué ; quelqu’un les déplace.',
                'gm_notes' => 'Les perches sont déplacées par les Serments brisés, pour que les voyageurs se perdent et disparaissent.',
            ],
            [
                'type' => 'place', 'name' => 'Le Phare d’Orvent', 'tags' => 'marches, acte 3',
                'summary' => 'Phare abandonné sur la pointe sud, dont la lanterne s’allume encore certaines nuits.',
                'description' => 'Trente-deux mètres de pierre, un escalier en spirale, une cave inondée à marée haute.',
                'gm_notes' => 'Les tuiles disparues de la Halle sont dans la cave, dans une caisse à sel. L’Inconnu les garde.',
            ],
            [
                'type' => 'character', 'name' => 'Dame Ysane Korr', 'color' => '#7c3f58', 'tags' => 'intrigue, halle',
                'summary' => 'Gardienne des serments : elle grave les tuiles et témoigne des contrats.',
                'description' => 'Soixante ans, mains brûlées par le four à vitrifier, une mémoire que personne n’ose contredire.',
                'gm_notes' => 'C’est elle qui a fait retirer les tuiles de l’année de la grande brume : son propre nom est sur l’une d’elles. Elle n’est pas méchante, elle est terrifiée.',
                'fields' => [
                    'Corps' => '2', 'Adresse' => '2', 'Esprit' => '5', 'Cœur' => '4',
                    'Souffle' => ['value' => 4, 'max' => 4], 'Serments tenus' => 31,
                    'Métier' => 'Gardienne des serments', 'Trait marquant' => 'Ne regarde jamais deux fois la même personne dans les yeux',
                    'Serment caché' => 'A juré, il y a trente ans, de laisser les Noyeux prendre une barque par an. La ville n’a plus de naufrages depuis.',
                    'Ce qui le ferait trahir' => 'La sécurité de sa petite-fille',
                ],
            ],
            [
                'type' => 'character', 'name' => 'Brannoc le Passeur', 'color' => '#2f5d50', 'tags' => 'quais, acte 1',
                'summary' => 'Passe les gens et les caisses par les marches, à l’heure qui l’arrange.',
                'description' => 'Grand, lent, parle peu et compte vite. Connaît le gué par cœur, même déplacé.',
                'gm_notes' => 'Il sait que les perches bougent. Il se taira jusqu’à ce qu’on lui propose de racheter sa dette au Fil Gris.',
                'fields' => [
                    'Corps' => '4', 'Adresse' => '4', 'Esprit' => '3', 'Cœur' => '2',
                    'Souffle' => ['value' => 5, 'max' => 5], 'Serments tenus' => 2,
                    'Métier' => 'Passeur', 'Trait marquant' => 'Ne jure jamais, ce qui en ville passe pour une insulte',
                    'Serment caché' => 'Doit onze ans de passages gratuits à la Compagnie du Fil Gris.',
                    'Ce qui le ferait trahir' => 'L’effacement de sa dette',
                ],
            ],
            [
                'type' => 'character', 'name' => 'Maître Elzevir', 'color' => '#4a5f7a', 'tags' => 'halle, intrigue',
                'summary' => 'Archiviste de la Halle, qui sait lire les tuiles les plus anciennes.',
                'description' => 'Petit, poudré de cendre, incapable de mentir sans tousser.',
                'gm_notes' => 'Il a recopié les tuiles retirées avant qu’on les emporte. Sa copie est dans la doublure de son manteau.',
                'fields' => [
                    'Corps' => '1', 'Adresse' => '2', 'Esprit' => '5', 'Cœur' => '3',
                    'Souffle' => ['value' => 3, 'max' => 3], 'Serments tenus' => 8,
                    'Métier' => 'Archiviste', 'Trait marquant' => 'Tousse quand il ment',
                    'Serment caché' => 'A juré à Ysane de ne jamais parler de l’année de la grande brume.',
                    'Ce qui le ferait trahir' => 'Qu’on lui promette que les tuiles seront remises à leur place',
                ],
            ],
            [
                'type' => 'character', 'name' => 'Sœur Vanne', 'color' => '#6b7f45', 'tags' => 'ville',
                'summary' => 'Soigne les noyés et les brûlés, sans demander de quel côté ils sont.',
                'description' => 'Tient une salle de six lits au-dessus d’une corderie.',
                'gm_notes' => 'Elle a soigné deux Serments brisés la semaine dernière. Elle ne le dira qu’en échange de sel et de bandes.',
                'fields' => [
                    'Corps' => '2', 'Adresse' => '3', 'Esprit' => '4', 'Cœur' => '5',
                    'Souffle' => ['value' => 4, 'max' => 4], 'Serments tenus' => 14,
                    'Métier' => 'Soigneuse', 'Trait marquant' => 'Appelle tout le monde « petit »',
                ],
            ],
            [
                'type' => 'character', 'name' => 'Capitaine Hald Mornevent', 'color' => '#8a4b2a', 'tags' => 'garde, acte 2',
                'summary' => 'Commande la Garde des Quais, vingt-deux hommes et une barque.',
                'description' => 'Compétent, fatigué, parfaitement conscient qu’il n’a pas les moyens de sa charge.',
                'gm_notes' => 'Il couvre la disparition de trois voyageurs pour ne pas affoler la ville. Il acceptera de l’aide s’on la lui offre sans public.',
                'fields' => [
                    'Corps' => '4', 'Adresse' => '3', 'Esprit' => '3', 'Cœur' => '3',
                    'Souffle' => ['value' => 5, 'max' => 5], 'Serments tenus' => 19,
                    'Métier' => 'Capitaine de la Garde', 'Trait marquant' => 'Note tout dans un carnet qu’il ne relit jamais',
                    'Serment caché' => 'A promis au conseil que personne ne disparaîtrait sous son commandement.',
                    'Ce qui le ferait trahir' => 'Sauver la face devant le conseil',
                ],
            ],
            [
                'type' => 'character', 'name' => 'L’Inconnu du Phare', 'color' => '#3f3f46', 'tags' => 'acte 3, intrigue',
                'local' => true,
                'summary' => 'Celui qui rallume la lanterne du phare d’Orvent. Personne ne l’a vu de près.',
                'gm_notes' => 'C’est Gueffroy, le gamin aux lanternes, noyé il y a six mois et rendu par les Noyeux. Il garde les tuiles et n’attend qu’une chose : qu’on lise son nom à voix haute.',
                'fields' => [
                    'Corps' => '3', 'Adresse' => '2', 'Esprit' => '2', 'Cœur' => '5',
                    'Souffle' => ['value' => 2, 'max' => 6], 'Serments tenus' => 1,
                    'Métier' => 'Allumeur de lanternes', 'Trait marquant' => 'Sent le sel froid',
                    'Serment caché' => 'A juré, en mourant, de rallumer les lanternes jusqu’à ce qu’on lui rende son nom.',
                ],
            ],
            [
                'type' => 'creature', 'name' => 'Les Noyeux', 'color' => '#1f4f5c', 'tags' => 'marches, intrigue',
                'summary' => 'Ce qui remonte des marches quand la brume tient plus de trois jours.',
                'description' => 'On les décrit comme des silhouettes qui marchent sous l’eau peu profonde, à hauteur d’homme.',
                'gm_notes' => 'Ils ne tuent pas : ils réclament. Un Noyeux relâche sa prise si on tient à sa place le serment qu’il est venu chercher.',
            ],
            [
                'type' => 'item', 'name' => 'Le Sceau de cendre', 'color' => '#9a7b3f', 'tags' => 'intrigue, acte 3',
                'summary' => 'Le poinçon qui grave les tuiles de la Halle. Sans lui, aucun serment n’est valide.',
                'description' => 'Un cylindre de verre noir, lourd, gravé en creux des armes de la ville.',
                'gm_notes' => 'Ysane l’a caché. Le rendre public clôt la campagne par la négociation ; le détruire la clôt par la rupture.',
            ],
            [
                'type' => 'faction', 'name' => 'La Compagnie du Fil Gris', 'tags' => 'intrigue',
                'summary' => 'Maison de négoce qui achète des dettes et revend des services.',
                'description' => 'Trois comptoirs, aucun navire en propre, et un carnet de dettes plus épais que le registre de la ville.',
                'gm_notes' => 'Veut le Sceau de cendre : qui grave les serments fixe le prix des dettes.',
            ],
            [
                'type' => 'faction', 'name' => 'Les Serments brisés', 'tags' => 'marches, acte 2',
                'summary' => 'Ceux qui ont rompu un serment et vivent désormais hors de la ville, dans les marches.',
                'gm_notes' => 'Ils déplacent les perches pour que la ville ait enfin peur de l’eau. Leur meneuse est la fille d’Ysane.',
            ],
            [
                'type' => 'organization', 'name' => 'La Garde des Quais', 'tags' => 'garde',
                'summary' => 'Vingt-deux hommes chargés du port, des lanternes et du couvre-feu.',
                'gm_notes' => 'Deux d’entre eux sont payés par le Fil Gris. Mornevent ne le sait pas.',
            ],
            // Quatre prétirés, prêts à être confiés aux joueurs.
            [
                'type' => 'pregen', 'name' => 'Teska la Rameuse', 'color' => '#2563eb', 'tags' => 'prétiré',
                'summary' => 'Rame depuis l’enfance, connaît la baie mieux que la Garde.',
                'description' => 'Vous avez juré à votre frère de ne jamais quitter Pierrecendre. Il est parti le mois dernier.',
                'fields' => [
                    'Corps' => '4', 'Adresse' => '4', 'Esprit' => '2', 'Cœur' => '3',
                    'Souffle' => ['value' => 5, 'max' => 5], 'Serments tenus' => 1,
                    'Métier' => 'Rameuse', 'Trait marquant' => 'Dit tout, tout de suite',
                    'Attaches' => 'Son frère, parti sans un mot. Brannoc, qui lui doit une barque.',
                ],
            ],
            [
                'type' => 'pregen', 'name' => 'Oriel Chantegrèle', 'color' => '#9333ea', 'tags' => 'prétiré',
                'summary' => 'Témoin de métier : on le paie pour assister aux serments et s’en souvenir.',
                'description' => 'Vous avez témoigné de deux cents serments. Vous en avez oublié un seul, exprès.',
                'fields' => [
                    'Corps' => '2', 'Adresse' => '3', 'Esprit' => '5', 'Cœur' => '3',
                    'Souffle' => ['value' => 3, 'max' => 3], 'Serments tenus' => 12,
                    'Métier' => 'Témoin', 'Trait marquant' => 'Répète les phrases importantes à voix basse',
                    'Attaches' => 'Maître Elzevir, son maître d’apprentissage. Le Fil Gris, qui l’emploie trop souvent.',
                ],
            ],
            [
                'type' => 'pregen', 'name' => 'Dorn Fer-Froid', 'color' => '#b45309', 'tags' => 'prétiré',
                'summary' => 'Ancien garde, renvoyé pour avoir refusé d’appliquer un couvre-feu.',
                'description' => 'Vous avez juré de ne plus jamais obéir à un ordre que vous ne comprenez pas.',
                'fields' => [
                    'Corps' => '5', 'Adresse' => '3', 'Esprit' => '3', 'Cœur' => '2',
                    'Souffle' => ['value' => 6, 'max' => 6], 'Serments tenus' => 4,
                    'Métier' => 'Garde renvoyé', 'Trait marquant' => 'Se place toujours entre la porte et les autres',
                    'Attaches' => 'Mornevent, qui l’a renvoyé à regret. Sœur Vanne, qui l’a recousu deux fois.',
                ],
            ],
            [
                'type' => 'pregen', 'name' => 'Lisenn aux Deux Noms', 'color' => '#0f766e', 'tags' => 'prétiré',
                'summary' => 'Vient des marches, vit en ville sous un nom qui n’est pas le sien.',
                'description' => 'Vous avez rompu un serment. Personne ici ne le sait encore.',
                'fields' => [
                    'Corps' => '3', 'Adresse' => '5', 'Esprit' => '3', 'Cœur' => '4',
                    'Souffle' => ['value' => 4, 'max' => 4], 'Serments tenus' => 0,
                    'Métier' => 'Guide des marches', 'Trait marquant' => 'Ne dort jamais deux nuits au même endroit',
                    'Attaches' => 'Les Serments brisés, qu’elle a quittés. Lisenn, la morte dont elle porte le nom.',
                ],
            ],
        ];
    }

    private function relations(): void
    {
        $relations = [
            ['Dame Ysane Korr', 'La Halle des Serments', 'garde', 'gardée par', Zone::Public],
            ['Maître Elzevir', 'La Halle des Serments', 'travaille à', 'emploie', Zone::Public],
            ['Maître Elzevir', 'Dame Ysane Korr', 'a juré le silence à', 'tient par un serment', Zone::GameMaster],
            ['Brannoc le Passeur', 'Les Marches noyées', 'connaît le gué de', 'traversées par', Zone::Public],
            ['Brannoc le Passeur', 'La Compagnie du Fil Gris', 'doit une dette à', 'détient la dette de', Zone::GameMaster],
            ['Capitaine Hald Mornevent', 'La Garde des Quais', 'commande', 'commandée par', Zone::Public],
            ['La Garde des Quais', 'Le Quai des Lanternes', 'surveille', 'surveillé par', Zone::Public],
            ['La Compagnie du Fil Gris', 'La Garde des Quais', 'achète deux hommes de', null, Zone::GameMaster],
            ['La Compagnie du Fil Gris', 'Le Sceau de cendre', 'convoite', 'convoité par', Zone::GameMaster],
            ['Les Serments brisés', 'Les Marches noyées', 'vivent dans', 'abritent', Zone::Public],
            ['Les Serments brisés', 'Dame Ysane Korr', 'sont menés par sa fille', null, Zone::GameMaster],
            ['Les Noyeux', 'Les Marches noyées', 'remontent de', null, Zone::Public],
            ['Les Noyeux', 'Dame Ysane Korr', 'ont un serment avec', null, Zone::GameMaster],
            ['L’Inconnu du Phare', 'Le Phare d’Orvent', 'rallume', 'rallumé par', Zone::Public],
            ['L’Inconnu du Phare', 'Le Quai des Lanternes', 'allumait les lanternes de', null, Zone::GameMaster],
            ['Sœur Vanne', 'Les Serments brisés', 'en a soigné deux', null, Zone::GameMaster],
            ['La Halle des Serments', 'Pierrecendre', 'se dresse à', 'abrite', Zone::Public],
            ['Le Quai des Lanternes', 'Pierrecendre', 'borde', 'ouvre sur', Zone::Public],
            ['Le Sceau de cendre', 'La Halle des Serments', 'grave les tuiles de', null, Zone::Public],
            ['Teska la Rameuse', 'Brannoc le Passeur', 'lui a prêté une barque', 'lui doit une barque', Zone::Public],
            ['Oriel Chantegrèle', 'Maître Elzevir', 'a été son apprenti', 'a formé', Zone::Public],
            ['Dorn Fer-Froid', 'Capitaine Hald Mornevent', 'a servi sous', 'a renvoyé', Zone::Public],
            ['Lisenn aux Deux Noms', 'Les Serments brisés', 'les a quittés', null, Zone::GameMaster],
        ];

        foreach ($relations as [$from, $to, $label, $reverse, $zone]) {
            $relation = new EntityRelation(['label' => $label, 'reverse_label' => $reverse, 'zone' => $zone]);
            $relation->owner()->associate($this->gm);
            $relation->from()->associate($this->entities[$from]);
            $relation->to()->associate($this->entities[$to]);
            $relation->save();
        }
    }

    private function documents(): void
    {
        $documents = [
            [
                'key' => 'tuile', 'title' => 'Tuile 1147 — serment de la grande brume', 'zone' => Zone::GameMaster,
                'description' => 'Le relevé qu’Elzevir a recopié avant que les tuiles ne quittent la Halle.',
                'tags' => 'intrigue, halle',
                'lines' => [
                    'Relevé de la tuile 1147, Halle des Serments de Pierrecendre.',
                    '',
                    'Jurante : Ysane Korr, gardienne.',
                    'Serment : « Une barque par an, et la baie restera calme. »',
                    'Temoins : Elzevir, archiviste. Gueffroy, allumeur de lanternes.',
                    '',
                    'Note de l archiviste : la tuile a ete retiree du rayon le 3 du mois du sel.',
                ],
            ],
            [
                'key' => 'avis', 'title' => 'Avis de couvre-feu', 'zone' => Zone::Public,
                'description' => 'Placardé sur le Quai des Lanternes. À montrer aux joueurs dès la première scène.',
                'tags' => 'acte 1',
                'lines' => [
                    'Par ordre du capitaine Hald Mornevent, Garde des Quais.',
                    '',
                    'Le Quai des Lanternes est ferme de la derniere lanterne a l aube.',
                    'Nul ne prend la mer sans un billet de la Garde.',
                    'Toute lanterne eteinte doit etre signalee au poste.',
                    '',
                    'Cet avis vaut serment : qui l enfreint repond devant la Halle.',
                ],
            ],
            [
                'key' => 'maree', 'title' => 'Table des marées des Marches noyées', 'zone' => Zone::Public,
                'description' => 'Aide de jeu : trois heures de gué par marée basse.',
                'tags' => 'marches',
                'lines' => [
                    'Marches noyees — passage du gue',
                    '',
                    'Maree basse : trois heures de chemin sur, perches visibles.',
                    'Maree montante : une heure de sursis, eau a mi-cuisse.',
                    'Maree haute : aucun passage. Douze heures d attente.',
                    '',
                    'Les perches sont replantees chaque mois par la Garde.',
                ],
            ],
        ];

        foreach ($documents as $data) {
            $path = 'documents/'.Str::random(40).'.pdf';
            Storage::disk(Document::DISK)->put($path, DemoFiles::pdf($data['title'], $data['lines']));

            $document = new Document([
                'title' => $data['title'],
                'description' => $data['description'],
                'zone' => $data['zone'],
                'disk' => Document::DISK,
                'path' => $path,
                'original_name' => Str::slug($data['title']).'.pdf',
                'mime_type' => 'application/pdf',
                'size' => Storage::disk(Document::DISK)->size($path),
            ]);
            $document->owner()->associate($this->gm);
            $document->campaign()->associate($this->campaign);
            $document->save();
            $document->tags()->sync(Tag::idsFromInput($this->gm, $data['tags']));

            $this->documents[$data['key']] = $document;
        }

        // La carte du port, rangée dans la campagne : elle sert de fond à la carte de table.
        $path = 'documents/'.Str::random(40).'.png';
        Storage::disk(Document::DISK)->put($path, DemoFiles::map());
        $map = new Document([
            'title' => 'Plan du port de Pierrecendre',
            'description' => 'Le port, ses quais et la pointe du phare. Montrable à la table.',
            'zone' => Zone::Public,
            'disk' => Document::DISK,
            'path' => $path,
            'original_name' => 'plan-du-port.png',
            'mime_type' => 'image/png',
            'size' => Storage::disk(Document::DISK)->size($path),
        ]);
        $map->owner()->associate($this->gm);
        $map->campaign()->associate($this->campaign);
        $map->save();
        $map->tags()->sync(Tag::idsFromInput($this->gm, 'ville'));
        $this->documents['plan'] = $map;

        $this->documents['tuile']->entities()->attach([
            $this->entities['Dame Ysane Korr']->id,
            $this->entities['Maître Elzevir']->id,
            $this->entities['La Halle des Serments']->id,
        ]);
        $this->documents['avis']->entities()->attach($this->entities['Le Quai des Lanternes']->id);
        $this->documents['maree']->entities()->attach($this->entities['Les Marches noyées']->id);
        $this->documents['plan']->entities()->attach($this->entities['Pierrecendre']->id);
    }

    private function rules(): void
    {
        $rules = [
            [
                'key' => 'jet', 'scope' => 'game', 'title' => 'Jet de serment', 'category' => 'Base',
                'summary' => 'Caractéristique + 1 d6 contre une difficulté de 4 à 9.',
                'procedure' => "1. Annoncez la caractéristique employée et ce que le personnage veut obtenir.\n2. Lancez 1 d6 et ajoutez la caractéristique.\n3. 4 pour une tâche de métier, 7 pour une tâche difficile, 9 pour l’impossible.\n4. Si le personnage agit pour tenir un serment, ajoutez +1 par serment tenu, jusqu’à +3.",
                'source' => 'Livret de base, p. 12', 'origin' => RuleOrigin::Reference, 'status' => RuleStatus::Available,
                'tags' => 'base',
            ],
            [
                'key' => 'souffle', 'scope' => 'game', 'title' => 'Souffle', 'category' => 'Base',
                'summary' => 'Le Souffle remplace les points de vie : il se dépense pour tenir, pas pour encaisser.',
                'procedure' => "Dépensez 1 Souffle pour relancer un dé, pour continuer malgré une blessure, ou pour refuser un Noyeux.\nÀ 0, le personnage s’arrête : il n’est pas mort, il ne peut plus rien promettre jusqu’au prochain repos.",
                'source' => 'Livret de base, p. 18', 'origin' => RuleOrigin::Reference, 'status' => RuleStatus::Available,
                'tags' => 'base',
            ],
            [
                'key' => 'rupture', 'scope' => 'game', 'title' => 'Rompre un serment', 'category' => 'Serments',
                'summary' => 'Rompre un serment donne un avantage immédiat et un prix durable.',
                'procedure' => "Le joueur décrit ce que la rupture lui permet : il l’obtient, sans jet.\nPuis il perd tous ses serments tenus, et la table note qui l’a appris.",
                'gm_notes' => 'Ne jamais refuser une rupture. Le prix se paie dans la fiction, par la réaction de ceux qui apprennent.',
                'source' => 'Livret de base, p. 24', 'origin' => RuleOrigin::Reference, 'status' => RuleStatus::Available,
                'tags' => 'serments',
            ],
            [
                'key' => 'brume', 'scope' => 'campaign', 'title' => 'Compte de la brume', 'category' => 'Maison',
                'summary' => 'Règle maison : la brume monte d’un cran à chaque séance, jusqu’à ce que les Noyeux marchent en ville.',
                'procedure' => "Tenez un compte de 0 à 6, visible de la table.\n+1 à chaque fin de séance, +1 chaque fois qu’un serment est rompu devant témoin.\nÀ 3, le gué devient incertain. À 6, les Noyeux entrent dans Pierrecendre.",
                'gm_notes' => 'Compte à l’écran de table, en secret jusqu’à 3.',
                'origin' => RuleOrigin::House, 'status' => RuleStatus::Adopted,
                'tags' => 'maison, intrigue',
            ],
            [
                'key' => 'parole', 'scope' => 'campaign', 'title' => 'Parole donnée à la table', 'category' => 'Maison',
                'summary' => 'À tester : une promesse faite par le joueur à voix haute compte comme serment.',
                'procedure' => 'Quand un joueur promet quelque chose à un personnage, notez-le. S’il la tient, +1 serment tenu ; sinon, la rupture s’applique.',
                'gm_notes' => 'À tester séance 2. Risque : les joueurs n’osent plus rien promettre.',
                'origin' => RuleOrigin::Test, 'status' => RuleStatus::ToTest,
                'tags' => 'maison',
            ],
        ];

        foreach ($rules as $data) {
            $rule = new Rule([
                'title' => $data['title'],
                'category' => $data['category'],
                'summary' => $data['summary'],
                'procedure' => $data['procedure'],
                'gm_notes' => $data['gm_notes'] ?? null,
                'source' => $data['source'] ?? null,
                'origin' => $data['origin'],
                'status' => $data['status'],
            ]);
            $rule->owner()->associate($this->gm);

            if ($data['scope'] === 'game') {
                $rule->gameSystem()->associate($this->campaign->gameSystem);
            } else {
                $rule->campaign()->associate($this->campaign);
            }

            $rule->save();
            $rule->tags()->sync(Tag::idsFromInput($this->gm, $data['tags']));
            $this->rules[$data['key']] = $rule;
        }

        $this->documents['tuile']->rules()->attach($this->rules['rupture']->id);
    }

    private function scenario(): void
    {
        $scenario = $this->campaign->scenarios()->create([
            'name' => 'Le Serment de Pierrecendre',
            'summary' => 'Trois séances : une lanterne éteinte, un gué qui ment, un phare qui réclame un nom.',
            'position' => 1,
        ]);

        foreach ($this->sceneData() as $position => $data) {
            $scene = $scenario->scenes()->create([
                'chapter' => $data['chapter'],
                'name' => $data['name'],
                'status' => $data['status'],
                'position' => $position + 1,
                'description' => $this->link($data['description']),
                'gm_notes' => isset($data['gm_notes']) ? $this->link($data['gm_notes']) : null,
            ]);

            $scene->entities()->attach(collect($data['entities'])
                ->mapWithKeys(fn (?string $note, string $name) => [
                    $this->entities[$name]->id => ['note' => $note, 'position' => array_search($name, array_keys($data['entities']), true)],
                ])->all());

            if (isset($data['documents'])) {
                $scene->documents()->attach(collect($data['documents'])
                    ->mapWithKeys(fn (string $key, int $i) => [$this->documents[$key]->id => ['position' => $i]])->all());
            }

            if (isset($data['rules'])) {
                $scene->rules()->attach(collect($data['rules'])
                    ->mapWithKeys(fn (string $key, int $i) => [$this->rules[$key]->id => ['position' => $i]])->all());
            }

            if (isset($data['tags'])) {
                $scene->tags()->sync(Tag::idsFromInput($this->gm, $data['tags']));
            }

            $this->scenes[$data['name']] = $scene;
        }
    }

    /** @return list<array<string, mixed>> */
    private function sceneData(): array
    {
        return [
            [
                'chapter' => 'Séance 1 — La lanterne éteinte', 'name' => 'La troisième lanterne',
                'status' => SceneStatus::Played, 'tags' => 'acte 1',
                'description' => 'Au crépuscule, sur [[Le Quai des Lanternes]], la troisième lanterne en partant du nord refuse de s’allumer. L’avis de couvre-feu est encore frais sur le mur.',
                'gm_notes' => 'C’est le signal de [[Brannoc le Passeur]]. Laissez les joueurs le découvrir en observant qui s’approche du quai.',
                'entities' => ['Le Quai des Lanternes' => null, 'Brannoc le Passeur' => 'arrive par l’eau, sans bruit', 'La Garde des Quais' => 'deux hommes en ronde'],
                'documents' => ['avis'],
                'rules' => ['jet'],
            ],
            [
                'chapter' => 'Séance 1 — La lanterne éteinte', 'name' => 'Le registre refusé',
                'status' => SceneStatus::Played, 'tags' => 'acte 1, halle',
                'description' => 'À [[La Halle des Serments]], [[Dame Ysane Korr]] refuse l’accès au rayon de l’année de la grande brume. [[Maître Elzevir]] tousse.',
                'gm_notes' => 'Elzevir cédera s’il est pris à part, hors de la vue d’Ysane. Sinon il tousse et change de sujet.',
                'entities' => ['La Halle des Serments' => null, 'Dame Ysane Korr' => 'derrière le pupitre', 'Maître Elzevir' => 'dans les rayons'],
                'rules' => ['rupture'],
            ],
            [
                'chapter' => 'Séance 2 — Le gué qui ment', 'name' => 'Les perches déplacées',
                'status' => SceneStatus::InProgress, 'tags' => 'acte 2, marches',
                'description' => 'Dans [[Les Marches noyées]], à marée basse, deux perches manquent et une troisième a été replantée de travers. La brume tient depuis quatre jours.',
                'gm_notes' => 'Un jet d’Esprit à 7 repère la supercherie. En cas d’échec, la marée monte sur un joueur : occasion de dépenser du Souffle.',
                'entities' => ['Les Marches noyées' => null, 'Brannoc le Passeur' => 'sait, et se tait', 'Les Serments brisés' => 'les observent de loin'],
                'documents' => ['maree'],
                'rules' => ['souffle', 'brume'],
            ],
            [
                'chapter' => 'Séance 2 — Le gué qui ment', 'name' => 'Le carnet de Mornevent',
                'status' => SceneStatus::Available, 'tags' => 'acte 2, garde',
                'description' => '[[Capitaine Hald Mornevent]] reçoit, à contrecœur, dans le poste de [[La Garde des Quais]]. Trois noms sont rayés dans son carnet.',
                'gm_notes' => 'Il parle s’il n’y a pas de témoin. Les trois noms sont ceux des voyageurs disparus au gué.',
                'entities' => ['Capitaine Hald Mornevent' => null, 'La Garde des Quais' => null, 'Les Marches noyées' => 'évoquées, pas visitées'],
            ],
            [
                'chapter' => 'Séance 2 — Le gué qui ment', 'name' => 'La salle aux six lits',
                'status' => SceneStatus::Available, 'tags' => 'acte 2',
                'description' => 'Chez [[Sœur Vanne]], deux blessés récents sentent le sel. Elle échange ce qu’elle sait contre du sel et des bandes.',
                'entities' => ['Sœur Vanne' => null, 'Les Serments brisés' => 'deux d’entre eux, soignés la semaine passée'],
            ],
            [
                'chapter' => 'Séance 3 — Le nom rendu', 'name' => 'La cave du phare',
                'status' => SceneStatus::Planned, 'tags' => 'acte 3',
                'description' => 'La cave du [[Le Phare d’Orvent]] est inondée à marée haute. Dans une caisse à sel : les tuiles retirées de la Halle.',
                'gm_notes' => 'La tuile 1147 est au-dessus de la pile, en évidence. [[L’Inconnu du Phare]] attend qu’on la lise à voix haute.',
                'entities' => ['Le Phare d’Orvent' => null, 'L’Inconnu du Phare' => 'en haut de l’escalier', 'Le Sceau de cendre' => 'absent de la caisse'],
                'documents' => ['tuile'],
            ],
            [
                'chapter' => 'Séance 3 — Le nom rendu', 'name' => 'Ce qui remonte',
                'status' => SceneStatus::Planned, 'tags' => 'acte 3, intrigue',
                'description' => '[[Les Noyeux]] marchent dans la baie, à hauteur d’homme, droit vers [[Pierrecendre]]. Le compte de la brume est à 6.',
                'gm_notes' => 'Ils s’arrêtent si quelqu’un tient à la place d’Ysane le serment de la tuile 1147, ou si le nom de Gueffroy est dit devant témoin.',
                'entities' => ['Les Noyeux' => null, 'Pierrecendre' => null, 'Dame Ysane Korr' => 'sur le quai, sans son sceau'],
                'rules' => ['brume', 'parole'],
            ],
            [
                'chapter' => 'Séance 3 — Le nom rendu', 'name' => 'Le serment refondu',
                'status' => SceneStatus::Planned, 'tags' => 'acte 3',
                'description' => 'À [[La Halle des Serments]], devant la ville : rendre le [[Le Sceau de cendre]] à la Halle, ou le briser.',
                'gm_notes' => 'Deux fins, aucune bonne. Le rendre : la ville tient, Ysane tombe. Le briser : plus aucun serment ne lie personne, et le Fil Gris achète tout.',
                'entities' => ['La Halle des Serments' => null, 'Le Sceau de cendre' => null, 'La Compagnie du Fil Gris' => 'présente, attend son tour'],
                'rules' => ['rupture'],
            ],
        ];
    }

    private function secrets(): void
    {
        $secrets = [
            [
                'title' => 'Ysane a juré une barque par an aux Noyeux',
                'body' => 'Il y a trente ans, Ysane Korr a promis aux Noyeux une barque par an pour que la baie reste calme. La tuile 1147 en porte le texte, et son nom.',
                'entities' => ['Dame Ysane Korr', 'Les Noyeux', 'La Halle des Serments'],
                'documents' => ['tuile'],
                'scenes' => ['Le registre refusé', 'La cave du phare'],
            ],
            [
                'title' => 'L’Inconnu du Phare est Gueffroy, l’allumeur noyé',
                'body' => 'Gueffroy était l’enfant payé pour allumer les lanternes du quai. Noyé il y a six mois, il a été rendu par les Noyeux. Il rallume le phare et attend qu’on dise son nom.',
                'entities' => ['L’Inconnu du Phare', 'Le Phare d’Orvent', 'Le Quai des Lanternes'],
                'scenes' => ['La cave du phare', 'Ce qui remonte'],
            ],
            [
                'title' => 'Les perches du gué sont déplacées exprès',
                'body' => 'Les Serments brisés déplacent les perches pour que Pierrecendre ait peur de son eau. Trois voyageurs y ont déjà disparu.',
                'entities' => ['Les Serments brisés', 'Les Marches noyées', 'Capitaine Hald Mornevent'],
                'documents' => ['maree'],
                'scenes' => ['Les perches déplacées', 'Le carnet de Mornevent'],
            ],
            [
                'title' => 'La meneuse des Serments brisés est la fille d’Ysane',
                'body' => 'Celle qui mène les Serments brisés est la fille de la gardienne. C’est pour elle qu’Ysane a caché les tuiles, et pour elle qu’elle trahirait la ville.',
                'entities' => ['Les Serments brisés', 'Dame Ysane Korr'],
                'scenes' => ['Ce qui remonte'],
            ],
            [
                'title' => 'Le Fil Gris a acheté deux gardes du quai',
                'body' => 'Deux hommes de la Garde des Quais sont payés par la Compagnie du Fil Gris. Mornevent l’ignore, et le découvrir le brisera.',
                'entities' => ['La Compagnie du Fil Gris', 'La Garde des Quais', 'Capitaine Hald Mornevent'],
                'scenes' => ['Le carnet de Mornevent'],
            ],
        ];

        foreach ($secrets as $data) {
            $secret = new Secret(['title' => $data['title'], 'body' => $data['body']]);
            $secret->owner()->associate($this->gm);
            $secret->campaign()->associate($this->campaign);
            $secret->save();

            $secret->entities()->attach(collect($data['entities'])->map(fn (string $name) => $this->entities[$name]->id)->all());

            if (isset($data['documents'])) {
                $secret->documents()->attach(collect($data['documents'])->map(fn (string $key) => $this->documents[$key]->id)->all());
            }

            $secret->scenes()->attach(collect($data['scenes'])->map(fn (string $name) => $this->scenes[$name]->id)->all());
        }
    }

    /** La carte de table : le plan du port, une grille, une échelle et des jetons. */
    private function map(): void
    {
        $document = $this->documents['plan'];
        $size = @getimagesizefromstring((string) Storage::disk($document->disk)->get($document->path));
        [$width, $height] = $size === false ? [1600, 1100] : [$size[0], $size[1]];

        $map = new TableMap([
            'name' => 'Le port de Pierrecendre',
            'grid_enabled' => true,
            'grid_size' => TableMap::defaultGridSize($width),
            'grid_offset_x' => 0,
            'grid_offset_y' => 0,
            'grid_color' => '#1c1917',
            'scale_value' => 5,
            'scale_unit' => 'm',
        ]);
        $map->campaign()->associate($this->campaign);
        $map->document()->associate($document);
        $map->forceFill(['width' => $width, 'height' => $height])->save();

        $tokens = [
            // Décalés en quinconce : à l'écran, les noms s'écrivent sous le jeton et ne doivent pas se chevaucher.
            ['Teska la Rameuse', 380, 780, 1, '#1d4ed8', false],
            ['Dorn Fer-Froid', 520, 900, 1, '#b45309', false],
            ['Oriel Chantegrèle', 660, 780, 1, '#7e22ce', false],
            ['Lisenn aux Deux Noms', 800, 900, 1, '#0f766e', false],
            ['Brannoc le Passeur', 1020, 760, 1, '#15803d', false],
            ['La Garde des Quais', 300, 600, 1, '#57534e', false],
            ['Les Noyeux', 1260, 990, 2, '#b91c1c', true],
        ];

        foreach ($tokens as [$name, $x, $y, $tokenSize, $color, $hidden]) {
            $token = $map->tokens()->make([
                'label' => $name,
                'color' => $color,
                'x' => $x,
                'y' => $y,
                'size' => $tokenSize,
                'hidden' => $hidden,
                'show_label' => true,
            ]);
            $token->entity()->associate($this->entities[$name]);
            $token->save();
        }

        $map->ruler = ['x1' => 380, 'y1' => 780, 'x2' => 1020, 'y2' => 760];
        $map->setView($width / 2, $height / 2, 1.1);
        $map->save();
    }

    private function timeline(): void
    {
        $events = [
            ['world', 'Il y a 300 ans', 'La cendre recouvre la baie', 'L’éruption du mont Orvent éteint le volcan et donne à la ville son sol gris.', Zone::Public],
            ['world', 'Il y a 180 ans', 'Premier serment gravé', 'La Halle est bâtie et le premier serment est vitrifié sur une tuile de cendre.', Zone::Public],
            ['world', 'Il y a 30 ans', 'L’année de la grande brume', 'Une brume de huit mois, onze barques perdues, puis plus aucun naufrage pendant trente ans.', Zone::Public],
            ['world', 'Il y a 30 ans', 'Le serment de la tuile 1147', 'Ysane Korr promet aux Noyeux une barque par an. Deux témoins : Elzevir et Gueffroy.', Zone::GameMaster],
            ['played', 'Il y a six mois', 'Gueffroy se noie au quai', 'L’allumeur de lanternes tombe du Quai des Lanternes. Son corps n’est pas retrouvé.', Zone::Public],
            ['played', 'Le mois dernier', 'Trois voyageurs manquent au gué', 'Mornevent raye trois noms dans son carnet et ne prévient pas le conseil.', Zone::GameMaster],
            ['played', 'Séance 1', 'La troisième lanterne reste éteinte', 'Les personnages repèrent le signal du Passeur et se voient refuser le rayon de la grande brume.', Zone::Public],
            ['planned', 'Séance 2', 'Les perches sont déplacées', 'Si personne n’intervient, un quatrième voyageur disparaît dans les marches.', Zone::GameMaster],
            ['planned', 'Séance 3', 'Les Noyeux entrent en ville', 'Au compte de brume 6, ils remontent la baie et marchent jusqu’à la Halle.', Zone::GameMaster],
            ['planned', 'Fin', 'Le sceau rendu ou brisé', 'Rendre le Sceau fait tomber Ysane ; le briser libère la ville de tout serment, et le Fil Gris de toute limite.', Zone::GameMaster],
        ];

        foreach ($events as $position => [$kind, $date, $title, $description, $zone]) {
            $event = new TimelineEvent([
                'kind' => $kind,
                'date_label' => $date,
                'title' => $title,
                'description' => $description,
                'zone' => $zone,
            ]);
            $event->campaign()->associate($this->campaign);
            $event->forceFill(['user_id' => $this->gm->id, 'position' => $position + 1])->save();
        }
    }

    /** Remplace « [[Nom]] » par le lien interne « [[Nom|id]] » attendu par l'application. */
    private function link(string $text): string
    {
        return preg_replace_callback('/\[\[([^\[\]|\n]+)\]\]/u', function (array $match): string {
            $entity = $this->entities[$match[1]] ?? null;

            return $entity === null ? $match[0] : '[['.$entity->name.'|'.$entity->id.']]';
        }, $text) ?? $text;
    }

    /** Thème de l'écran de table proposé par la démonstration. */
    public static function theme(): string
    {
        return array_key_exists('parchemin', TableTheme::THEMES) ? 'parchemin' : TableTheme::DEFAULT;
    }
}
