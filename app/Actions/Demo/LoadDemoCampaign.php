<?php

namespace App\Actions\Demo;

use App\Actions\Campaigns\CreateCampaign;
use App\Enums\FieldType;
use App\Enums\RuleOrigin;
use App\Enums\RuleStatus;
use App\Enums\SceneStatus;
use App\Enums\Zone;
use App\Models\ActivityLog;
use App\Models\AudioTrack;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\EntityType;
use App\Models\FeedbackRequest;
use App\Models\FieldDefinition;
use App\Models\GameSystem;
use App\Models\MapPoint;
use App\Models\PlayerCharacter;
use App\Models\PlaySession;
use App\Models\Rule;
use App\Models\Scene;
use App\Models\Secret;
use App\Models\SessionNote;
use App\Models\TableMap;
use App\Models\Tag;
use App\Models\TimelineEvent;
use App\Models\ToPlayItem;
use App\Models\User;
use App\Models\World;
use App\Support\Demo\DemoFiles;
use App\Support\Demo\DemoLock;
use App\Support\Dice\DiceFormula;
use App\Support\Dice\DiceRoller;
use App\Support\Dice\SheetValues;
use App\Support\Dice\TableDice;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Campagne de démonstration : « Le Serment de Pierrecendre », pour le jeu inventé « Brume & Serment ».
 *
 * Un contenu entièrement original, écrit pour SagaWyn, qui met en scène chaque fonction : champs
 * libres, zones publique et MJ, relations et graphe, chronologie, scénario et scènes, documents,
 * carte de campagne avec ses points et une sous-carte de combat (jetons, gabarits, initiative),
 * indices (vérité, indice, rumeur, mensonge), règles et contenu maison, tags, prétirés, sons liés aux
 * scènes (dont un de la bibliothèque « Mes sons »), lancers de dés enregistrés qui lisent la fiche,
 * une séance déjà jouée (scènes « jouée comme prévu » ou « ce qui s'est passé », livre des aventures)
 * et une demande d'avis aux joueurs.
 *
 * Cette classe porte la structure (qui est relié à qui, les zones, les jetons, les valeurs chiffrées),
 * identique dans toutes les langues ; le texte vient de resources/demo/{langue}.php, avec les mêmes clés.
 * Une image déposée dans resources/demo/images/{clé}.{png,jpg,webp} remplace celle dessinée par le code.
 */
class LoadDemoCampaign
{
    public const TEXT_PATH = 'demo';

    private User $gm;

    private Campaign $campaign;

    /** @var array<string, mixed> */
    private array $text;

    /** @var array<string, Entity> */
    private array $entities = [];

    /** @var array<string, FieldDefinition> */
    private array $fields = [];

    /** @var array<string, EntityType> */
    private array $types = [];

    private PlaySession $session;

    /** @var array<string, Document> */
    private array $documents = [];

    /** @var array<string, Rule> */
    private array $rules = [];

    /** @var array<string, Scene> */
    private array $scenes = [];

    /** @var array<string, Secret> */
    private array $secrets = [];

    private PlayerCharacter $character;

    public function __construct(private CreateCampaign $createCampaign) {}

    /** Langues dans lesquelles la démonstration existe. */
    public static function locales(): array
    {
        return array_values(array_filter(
            array_keys(Locale::available()),
            fn (string $code) => is_file(self::textFile($code)),
        ));
    }

    /** @return array<string, mixed> le texte de la démonstration dans cette langue */
    public static function text(string $locale): array
    {
        return require self::textFile($locale);
    }

    private static function textFile(string $locale): string
    {
        return resource_path(self::TEXT_PATH.'/'.basename($locale).'.php');
    }

    /**
     * Le nom, ou le nom suivi du premier numéro libre parmi ceux de la requête.
     *
     * @param  Builder<covariant Model>  $query
     */
    private static function unique(Builder $query, string $name): string
    {
        $taken = $query->pluck('name')->map(fn (string $existing) => mb_strtolower($existing))->all();

        if (! in_array(mb_strtolower($name), $taken, true)) {
            return $name;
        }

        $number = 2;
        while (in_array(mb_strtolower("$name ($number)"), $taken, true)) {
            $number++;
        }

        return "$name ($number)";
    }

    /** Charge la démonstration dans le compte donné, dans la langue demandée ou celle de l'interface. */
    public function handle(User $gm, ?string $locale = null): Campaign
    {
        $locale ??= app()->getLocale();
        $this->gm = $gm;
        $this->text = self::text(in_array($locale, self::locales(), true) ? $locale : Locale::DEFAULT);

        // Le journal d'activité raconte ce que fait le MJ en partie : une démonstration n'a rien à y écrire.
        // Le verrou d'un compte démo (DemoLock) protège la démonstration une fois chargée, pas pendant.
        return DemoLock::bypass(fn () => ActivityLog::muted(fn () => DB::transaction(function () {
            $this->campaign = $this->createCampaign->handle($this->gm, [
                // Une démonstration chargée plusieurs fois : « Vehrmund (2) », pour distinguer les copies.
                'name' => self::unique(Campaign::where('user_id', $this->gm->id), $this->text['campaign']['name']),
                'description' => $this->text['campaign']['description'],
                'new_game_name' => self::unique(GameSystem::where('user_id', $this->gm->id), $this->text['game']['name']),
                'new_world_name' => self::unique(World::where('user_id', $this->gm->id), $this->text['world']['name']),
            ]);

            $this->campaign->gameSystem->forceFill(['description' => $this->text['game']['description']])->save();
            $this->campaign->world->forceFill(['description' => $this->text['world']['description']])->save();
            // Les joueurs complètent eux-mêmes le portrait et la présentation de leur personnage.
            $this->campaign->forceFill(['table_theme' => 'parchemin', 'is_demo' => true, 'players_edit_profile' => true])->save();
            $this->cover($this->campaign->gameSystem, 'game', '#5b3a29', '#2b3a42');
            $this->cover($this->campaign->world, 'world', '#41607a', '#1f4f5c');

            $this->types();
            $this->fieldDefinitions();
            $this->entities();
            $this->relations();
            $this->documents();
            $this->references();
            $this->rules();
            $this->scenario();
            $this->secrets();
            $this->map();
            $this->sounds();
            $this->playedSession();
            $this->character();
            $this->dice();
            $this->initiative();
            $this->toPlay();
            $this->timeline();
            $this->colourTags();

            return $this->campaign->fresh();
        })));
    }

    private function types(): void
    {
        foreach (['character', 'place', 'organization', 'item', 'creature'] as $key) {
            $this->types[$key] = EntityType::standard($key);
        }

        // Les types sont communs à tous les jeux du MJ : une démonstration rechargée reprend le sien.
        $type = EntityType::where('user_id', $this->gm->id)->where('name', $this->text['types']['faction'])->first()
            ?? new EntityType(['name' => $this->text['types']['faction']]);
        $type->user_id = $this->gm->id;
        $type->save();
        $this->types['faction'] = $type;

        // Les prétirés sont des fiches de personnage (tag « prétiré ») : ainsi « Nouveau
        // personnage » les propose aux joueurs.
        $this->types['pregen'] = $this->types['character'];
    }

    /**
     * Les champs du jeu : quatre caractéristiques (des nombres, que les lancers de dés reprennent en bonus), un compteur, un profil, deux champs réservés au MJ
     * dont une référence à une fiche ; pour les lieux et créatures, un niveau de danger (un champ
     * commun à deux types) et, pour les lieux, un document de référence.
     */
    private function fieldDefinitions(): void
    {
        $definitions = [
            // clé, groupe, type, options, zone, modifiable par le joueur, types de fiche
            ['body', 'traits', FieldType::Number, null, Zone::Public, false, ['character']],
            ['skill', 'traits', FieldType::Number, null, Zone::Public, false, ['character']],
            ['mind', 'traits', FieldType::Number, null, Zone::Public, false, ['character']],
            ['heart', 'traits', FieldType::Number, null, Zone::Public, false, ['character']],
            ['breath', 'traits', FieldType::Counter, null, Zone::Public, true, ['character']],
            ['oaths', 'traits', FieldType::Number, null, Zone::Public, true, ['character']],
            ['trade', 'profile', FieldType::Text, null, Zone::Public, false, ['character']],
            ['trait', 'profile', FieldType::Text, null, Zone::Public, false, ['character']],
            ['ties', 'profile', FieldType::LongText, null, Zone::Public, true, ['character']],
            ['hidden_oath', 'secrets', FieldType::LongText, null, Zone::GameMaster, false, ['character']],
            ['betrayal', 'secrets', FieldType::Text, null, Zone::GameMaster, false, ['character']],
            ['allegiance', 'secrets', FieldType::EntityRef, null, Zone::GameMaster, false, ['character']],
            ['danger', 'landmarks', FieldType::Select, array_values($this->text['danger_levels']), Zone::Public, false, ['place', 'creature']],
            ['reference', 'landmarks', FieldType::File, null, Zone::Public, false, ['place']],
        ];

        foreach ($definitions as $position => [$key, $group, $type, $options, $zone, $playerEditable, $for]) {
            $field = $this->campaign->gameSystem->fieldDefinitions()->make([
                'name' => $this->text['fields'][$key],
                'group' => $this->text['groups'][$group],
                'type' => $type,
                'options' => $options,
                'zone' => $zone,
                'position' => $position,
                'player_editable' => $playerEditable,
            ]);
            $field->assignTypes(collect($for)->map(fn (string $type) => $this->types[$type]->id)->all())->save();
            $this->fields[$key] = $field;
        }
    }

    /**
     * Structure des fiches : type, couleur du portrait, tags, valeurs chiffrées.
     * Les valeurs textuelles (métier, trait…) viennent du texte de la langue.
     *
     * @return array<string, array{type: string, color?: string, tags: list<string>, scores?: array<string, mixed>, local?: bool}>
     */
    private static function entityStructure(): array
    {
        $scores = fn (int $body, int $skill, int $mind, int $heart, int $breath, int $oaths, ?int $max = null) => [
            'body' => $body, 'skill' => $skill, 'mind' => $mind, 'heart' => $heart,
            'breath' => ['value' => $breath, 'max' => $max ?? $breath], 'oaths' => $oaths,
        ];

        return [
            'city' => ['type' => 'place', 'tags' => ['city', 'act1']],
            'hall' => ['type' => 'place', 'tags' => ['city', 'intrigue']],
            'quay' => ['type' => 'place', 'tags' => ['city', 'act1']],
            'marshes' => ['type' => 'place', 'tags' => ['marshes', 'act2']],
            'lighthouse' => ['type' => 'place', 'tags' => ['marshes', 'act3']],
            'ysane' => ['type' => 'character', 'color' => '#7c3f58', 'tags' => ['intrigue', 'hall'], 'scores' => $scores(2, 2, 5, 4, 4, 31)],
            'brannoc' => ['type' => 'character', 'color' => '#2f5d50', 'tags' => ['quays', 'act1'], 'scores' => $scores(4, 4, 3, 2, 5, 2)],
            'elzevir' => ['type' => 'character', 'color' => '#4a5f7a', 'tags' => ['hall', 'intrigue'], 'scores' => $scores(1, 2, 5, 3, 3, 8)],
            'vanne' => ['type' => 'character', 'color' => '#6b7f45', 'tags' => ['city'], 'scores' => $scores(2, 3, 4, 5, 4, 14)],
            'mornevent' => ['type' => 'character', 'color' => '#8a4b2a', 'tags' => ['guard', 'act2'], 'scores' => $scores(4, 3, 3, 3, 5, 19)],
            'stranger' => ['type' => 'character', 'color' => '#3f3f46', 'tags' => ['act3', 'intrigue'], 'scores' => $scores(3, 2, 2, 5, 2, 1, 6), 'local' => true],
            'drowned' => ['type' => 'creature', 'color' => '#1f4f5c', 'tags' => ['marshes', 'intrigue']],
            'seal' => ['type' => 'item', 'color' => '#9a7b3f', 'tags' => ['intrigue', 'act3']],
            'greythread' => ['type' => 'faction', 'tags' => ['intrigue']],
            'broken' => ['type' => 'faction', 'tags' => ['marshes', 'act2']],
            'guard' => ['type' => 'organization', 'tags' => ['guard']],
            // Quatre prétirés, prêts à être confiés aux joueurs.
            'teska' => ['type' => 'pregen', 'color' => '#2563eb', 'tags' => ['pregen'], 'scores' => $scores(4, 4, 2, 3, 5, 1)],
            'oriel' => ['type' => 'pregen', 'color' => '#9333ea', 'tags' => ['pregen'], 'scores' => $scores(2, 3, 5, 3, 3, 12)],
            'dorn' => ['type' => 'pregen', 'color' => '#b45309', 'tags' => ['pregen'], 'scores' => $scores(5, 3, 3, 2, 6, 4)],
            'lisenn' => ['type' => 'pregen', 'color' => '#0f766e', 'tags' => ['pregen'], 'scores' => $scores(3, 5, 3, 4, 4, 0)],
        ];
    }

    private function entities(): void
    {
        foreach (self::entityStructure() as $key => $structure) {
            $text = $this->text['entities'][$key];

            $entity = new Entity([
                'entity_type_id' => $this->types[$structure['type']]->id,
                'name' => $text['name'],
                'summary' => $text['summary'],
                'description' => $text['description'] ?? null,
                'gm_notes' => $text['gm_notes'] ?? null,
            ]);
            $entity->owner()->associate($this->gm);

            // Une fiche appartient soit au monde, soit à la campagne : seul l'inconnu du phare est propre à celle-ci.
            if ($structure['local'] ?? false) {
                $entity->campaign()->associate($this->campaign);
            } else {
                $entity->world()->associate($this->campaign->world);
            }

            $image = $this->image($key, fn () => isset($structure['color']) ? DemoFiles::portrait($structure['color']) : null);

            if ($image !== null) {
                $entity->image_path = 'entities/'.Str::random(40).'.'.$image['extension'];
                Storage::disk(Entity::FILES_DISK)->put($entity->image_path, $image['contents']);
            }

            $entity->save();

            $values = ($structure['scores'] ?? []) + ($text['fields'] ?? []);

            if ($values !== []) {
                $entity->setFieldValues(collect($values)
                    ->mapWithKeys(fn (mixed $value, string $field) => [$this->fields[$field]->id => $value])
                    ->all());
                $entity->save();
            }

            $entity->tags()->sync($this->tags($structure['tags']));
            $this->entities[$key] = $entity;
        }

        // Contenu maison : les Noyeux de cette table sortent de l'eau ; la version du livret reste au MJ.
        $this->entities['drowned']->forceFill(['is_house' => true, 'official_version' => $this->text['house']['drowned']])->save();

        // Une différence propre à la campagne, qui ne touche pas la fiche du monde.
        $this->entities['quay']->stateIn($this->campaign)->fill($this->text['quay_state'])->save();
    }

    /**
     * Valeurs qui désignent d'autres éléments : danger des lieux et créatures, document de référence
     * des lieux, allégeance réelle (une fiche) de trois personnages. Et une pièce jointe réservée au MJ.
     */
    private function references(): void
    {
        $levels = array_values($this->text['danger_levels']);
        $values = [
            'city' => ['danger' => $levels[0], 'reference' => $this->documents['plan']->id],
            'hall' => ['danger' => $levels[1]],
            'quay' => ['danger' => $levels[1], 'reference' => $this->documents['notice']->id],
            'marshes' => ['danger' => $levels[2], 'reference' => $this->documents['tides']->id],
            'lighthouse' => ['danger' => $levels[3]],
            'drowned' => ['danger' => $levels[3]],
            'brannoc' => ['allegiance' => 'greythread'],
            'vanne' => ['allegiance' => 'broken'],
            'mornevent' => ['allegiance' => 'guard'],
        ];

        foreach ($values as $key => $fields) {
            $entity = $this->entities[$key];
            $entity->setFieldValues(collect($fields)->mapWithKeys(fn (mixed $value, string $field) => [
                $this->fields[$field]->id => $field === 'allegiance' ? '[['.$this->entities[$value]->name.'|'.$this->entities[$value]->id.']]' : $value,
            ])->all());
            $entity->save();
        }

        $text = $this->text['attachments']['seal'];
        $file = $this->image('attachment-seal', fn () => ['extension' => 'pdf', 'contents' => DemoFiles::pdf($text['title'], $text['lines'])]);
        $seal = $this->entities['seal'];
        $path = 'attachments/'.$seal->id.'/'.Str::random(40).'.'.$file['extension'];
        Storage::disk(Entity::FILES_DISK)->put($path, $file['contents']);

        $attachment = $seal->attachments()->make([
            'zone' => Zone::GameMaster,
            'disk' => Entity::FILES_DISK,
            'path' => $path,
            'original_name' => $text['file'].'.'.$file['extension'],
            'mime_type' => self::mime($file['extension']),
            'size' => strlen($file['contents']),
        ]);
        $attachment->owner()->associate($this->gm);
        $attachment->save();
    }

    private function relations(): void
    {
        $relations = [
            'ysane_hall' => ['ysane', 'hall', Zone::Public],
            'elzevir_hall' => ['elzevir', 'hall', Zone::Public],
            'elzevir_ysane' => ['elzevir', 'ysane', Zone::GameMaster],
            'brannoc_marshes' => ['brannoc', 'marshes', Zone::Public],
            'brannoc_greythread' => ['brannoc', 'greythread', Zone::GameMaster],
            'mornevent_guard' => ['mornevent', 'guard', Zone::Public],
            'guard_quay' => ['guard', 'quay', Zone::Public],
            'greythread_guard' => ['greythread', 'guard', Zone::GameMaster],
            'greythread_seal' => ['greythread', 'seal', Zone::GameMaster],
            'broken_marshes' => ['broken', 'marshes', Zone::Public],
            'broken_ysane' => ['broken', 'ysane', Zone::GameMaster],
            'drowned_marshes' => ['drowned', 'marshes', Zone::Public],
            'drowned_ysane' => ['drowned', 'ysane', Zone::GameMaster],
            'stranger_lighthouse' => ['stranger', 'lighthouse', Zone::Public],
            'stranger_quay' => ['stranger', 'quay', Zone::GameMaster],
            'vanne_broken' => ['vanne', 'broken', Zone::GameMaster],
            'hall_city' => ['hall', 'city', Zone::Public],
            'quay_city' => ['quay', 'city', Zone::Public],
            'seal_hall' => ['seal', 'hall', Zone::Public],
            'teska_brannoc' => ['teska', 'brannoc', Zone::Public],
            'oriel_elzevir' => ['oriel', 'elzevir', Zone::Public],
            'dorn_mornevent' => ['dorn', 'mornevent', Zone::Public],
            'lisenn_broken' => ['lisenn', 'broken', Zone::GameMaster],
        ];

        foreach ($relations as $key => [$from, $to, $zone]) {
            [$label, $reverse] = $this->text['relations'][$key];

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
            'tile' => [Zone::GameMaster, ['intrigue', 'hall'], ['ysane', 'elzevir', 'hall']],
            'notice' => [Zone::Public, ['act1'], ['quay']],
            'tides' => [Zone::Public, ['marshes'], ['marshes']],
        ];

        foreach ($documents as $key => [$zone, $tags, $entities]) {
            $text = $this->text['documents'][$key];
            $file = $this->image('document-'.$key, fn () => null);

            $this->documents[$key] = $this->document(
                $text,
                $zone,
                $file ?? ['extension' => 'pdf', 'contents' => DemoFiles::pdf($text['title'], $text['lines'])],
                $tags,
                $entities,
            );
        }

        // La carte du port, rangée dans la campagne : elle sert de fond à la carte de table.
        $plan = $this->image('map', fn () => ['extension' => 'png', 'contents' => DemoFiles::map()]);
        $this->documents['plan'] = $this->document($this->text['documents']['plan'], Zone::Public, $plan, ['city'], ['city']);
    }

    /**
     * @param  array<string, mixed>  $text
     * @param  array{extension: string, contents: string}  $file
     * @param  list<string>  $tags
     * @param  list<string>  $entities
     */
    private function document(array $text, Zone $zone, array $file, array $tags, array $entities): Document
    {
        $path = 'documents/'.Str::random(40).'.'.$file['extension'];
        Storage::disk(Document::DISK)->put($path, $file['contents']);

        $document = new Document([
            'title' => $text['title'],
            'description' => $text['description'],
            'zone' => $zone,
            'disk' => Document::DISK,
            'path' => $path,
            'original_name' => ($text['file'] ?? Str::slug($text['title'])).'.'.$file['extension'],
            'mime_type' => self::mime($file['extension']),
            'size' => strlen($file['contents']),
        ]);
        $document->owner()->associate($this->gm);
        $document->campaign()->associate($this->campaign);
        $document->save();

        $document->tags()->sync($this->tags($tags));
        $document->entities()->attach(collect($entities)->map(fn (string $key) => $this->entities[$key]->id)->all());

        return $document;
    }

    private function rules(): void
    {
        $rules = [
            // clé, portée, origine, statut, tags
            'roll' => ['game', RuleOrigin::Reference, RuleStatus::Available, ['base']],
            'breath' => ['game', RuleOrigin::Reference, RuleStatus::Available, ['base']],
            'breaking' => ['game', RuleOrigin::Reference, RuleStatus::Available, ['oaths']],
            'mist' => ['campaign', RuleOrigin::House, RuleStatus::Adopted, ['house', 'intrigue']],
            'word' => ['campaign', RuleOrigin::Test, RuleStatus::ToTest, ['house']],
        ];

        foreach ($rules as $key => [$scope, $origin, $status, $tags]) {
            $text = $this->text['rules'][$key];

            $rule = new Rule([
                'title' => $text['title'],
                'category' => $text['category'],
                'summary' => $text['summary'],
                'procedure' => $text['procedure'],
                'gm_notes' => $text['gm_notes'] ?? null,
                'source' => $text['source'] ?? null,
                'official_version' => $text['official_version'] ?? null,
                'origin' => $origin,
                'status' => $status,
            ]);
            $rule->owner()->associate($this->gm);

            if ($scope === 'game') {
                $rule->gameSystem()->associate($this->campaign->gameSystem);
            } else {
                $rule->campaign()->associate($this->campaign);
            }

            $rule->save();
            $rule->tags()->sync($this->tags($tags));
            $this->rules[$key] = $rule;
        }

        $this->documents['tile']->rules()->attach($this->rules['breaking']->id);
    }

    private function scenario(): void
    {
        $scenario = $this->campaign->scenarios()->create([
            'name' => $this->text['scenario']['name'],
            'summary' => $this->text['scenario']['summary'],
            'position' => 1,
        ]);

        $scenes = [
            // clé => chapitre, statut, tags, fiches (dans l'ordre), documents, règles
            // Des deux scènes jouées, l'une s'est passée comme prévu, l'autre raconte ce que la table en a fait.
            'lantern' => ['s1', SceneStatus::Played, ['act1'], ['quay', 'brannoc', 'guard'], ['notice'], ['roll']],
            'register' => ['s1', SceneStatus::Played, ['act1', 'hall'], ['hall', 'ysane', 'elzevir'], [], ['breaking']],
            'poles' => ['s2', SceneStatus::InProgress, ['act2', 'marshes'], ['marshes', 'brannoc', 'broken'], ['tides'], ['breath', 'mist']],
            'notebook' => ['s2', SceneStatus::Available, ['act2', 'guard'], ['mornevent', 'guard', 'marshes'], [], []],
            'ward' => ['s2', SceneStatus::Available, ['act2'], ['vanne', 'broken'], [], []],
            'cellar' => ['s3', SceneStatus::Planned, ['act3'], ['lighthouse', 'stranger', 'seal'], ['tile'], []],
            'rising' => ['s3', SceneStatus::Planned, ['act3', 'intrigue'], ['drowned', 'city', 'ysane'], [], ['mist', 'word']],
            'recast' => ['s3', SceneStatus::Planned, ['act3'], ['hall', 'seal', 'greythread'], [], ['breaking']],
        ];

        $position = 0;

        foreach ($scenes as $key => [$chapter, $status, $tags, $entities, $documents, $rules]) {
            $text = $this->text['scenes'][$key];

            $scene = $scenario->scenes()->create([
                'chapter' => $this->text['chapters'][$chapter],
                'name' => $text['name'],
                'status' => $status,
                'position' => ++$position,
                // Une scène n'est vue que du MJ : ses notes suivent la description, en paragraphe à part.
                'description' => $this->link(trim($text['description']."\n\n".($text['gm_notes'] ?? ''))),
                'what_happened' => isset($text['what_happened']) ? $this->link($text['what_happened']) : null,
                'played_as_planned' => $status === SceneStatus::Played && ! isset($text['what_happened']),
            ]);

            $scene->entities()->attach(collect($entities)->mapWithKeys(fn (string $entity, int $i) => [
                $this->entities[$entity]->id => ['note' => $text['notes'][$entity] ?? null, 'position' => $i],
            ])->all());
            $scene->documents()->attach(collect($documents)->mapWithKeys(fn (string $doc, int $i) => [$this->documents[$doc]->id => ['position' => $i]])->all());
            $scene->rules()->attach(collect($rules)->mapWithKeys(fn (string $rule, int $i) => [$this->rules[$rule]->id => ['position' => $i]])->all());
            $scene->tags()->sync($this->tags($tags));

            $this->scenes[$key] = $scene;
        }
    }

    private function secrets(): void
    {
        $secrets = [
            // clé => fiches, documents, scènes
            'ysane_oath' => [['ysane', 'drowned', 'hall'], ['tile'], ['register', 'cellar']],
            'stranger' => [['stranger', 'lighthouse', 'quay'], [], ['cellar', 'rising']],
            'poles' => [['broken', 'marshes', 'mornevent'], ['tides'], ['poles', 'notebook']],
            'daughter' => [['broken', 'ysane'], [], ['rising']],
            'bought' => [['greythread', 'guard', 'mornevent'], [], ['notebook']],
            'burnt' => [['elzevir', 'hall'], ['tile'], ['register']],
        ];

        // Une rumeur, un indice, des vérités et un mensonge : les quatre natures d'indice.
        $kinds = ['ysane_oath' => 'truth', 'stranger' => 'truth', 'poles' => 'clue', 'daughter' => 'truth', 'bought' => 'rumour', 'burnt' => 'lie'];

        foreach ($secrets as $key => [$entities, $documents, $scenes]) {
            $secret = new Secret($this->text['secrets'][$key] + ['kind' => $kinds[$key]]);
            $secret->owner()->associate($this->gm);
            $secret->campaign()->associate($this->campaign);
            $secret->save();

            $secret->entities()->attach(collect($entities)->map(fn (string $k) => $this->entities[$k]->id)->all());
            $secret->documents()->attach(collect($documents)->map(fn (string $k) => $this->documents[$k]->id)->all());
            $secret->scenes()->attach(collect($scenes)->map(fn (string $k) => $this->scenes[$k]->id)->all());
            $this->secrets[$key] = $secret;
        }
    }

    /** La carte de table : le plan du port, une grille, une échelle et des jetons. */
    private function map(): void
    {
        $document = $this->documents['plan'];
        $size = @getimagesizefromstring((string) Storage::disk($document->disk)->get($document->path));
        [$width, $height] = $size === false ? [1600, 1100] : [$size[0], $size[1]];

        $map = new TableMap([
            'name' => $this->text['map']['name'],
            'grid_enabled' => true,
            'grid_size' => TableMap::defaultGridSize($width),
            'grid_offset_x' => 0,
            'grid_offset_y' => 0,
            'grid_color' => '#1c1917',
            'scale_value' => 5,
            'scale_unit' => $this->text['map']['unit'],
        ]);
        $map->campaign()->associate($this->campaign);
        $map->document()->associate($document);
        $map->forceFill(['width' => $width, 'height' => $height])->save();

        // Positions en fractions de l'image : elles tiennent si une autre image remplace le plan dessiné.
        // Décalés en quinconce : à l'écran, les noms s'écrivent sous le jeton et ne doivent pas se chevaucher.
        $tokens = [
            ['teska', 0.24, 0.71, 1, '#1d4ed8', false],
            ['dorn', 0.33, 0.82, 1, '#b45309', false],
            ['oriel', 0.41, 0.71, 1, '#7e22ce', false],
            ['lisenn', 0.5, 0.82, 1, '#0f766e', false],
            ['brannoc', 0.64, 0.69, 1, '#15803d', false],
            ['guard', 0.19, 0.55, 1, '#57534e', false],
            ['drowned', 0.79, 0.9, 2, '#b91c1c', true],
        ];

        foreach ($tokens as [$key, $x, $y, $tokenSize, $color, $hidden]) {
            $token = $map->tokens()->make([
                'label' => $this->entities[$key]->name,
                'color' => $color,
                'x' => round($x * $width, 2),
                'y' => round($y * $height, 2),
                'size' => $tokenSize,
                'hidden' => $hidden,
                'show_label' => true,
            ]);
            $token->entity()->associate($this->entities[$key]);
            $token->save();
        }

        // Carte de campagne : le plan du port, avec les lieux des scènes, déjà jouées ou à venir.
        // Le phare mène à une sous-carte : sa cave, prête pour le combat de la séance 3.
        $map->is_campaign_map = true;
        $cellar = $this->cellarMap();
        // Chaque point relie le lieu, ceux qu'on y croise et les scènes qui s'y jouent.
        $points = [
            // fiches (le lieu d'abord), scènes, x, y, couleur, visible à la table, sous-carte
            [['quay', 'brannoc', 'guard'], ['lantern'], 0.3, 0.62, '#1d4ed8', true, null],
            [['hall', 'ysane', 'elzevir', 'seal'], ['register', 'recast'], 0.63, 0.2, '#7e22ce', true, null],
            [['marshes', 'broken', 'drowned'], ['poles', 'notebook'], 0.14, 0.24, '#15803d', false, null],
            [['lighthouse', 'stranger'], ['cellar', 'rising'], 0.9, 0.62, '#b91c1c', false, $cellar],
        ];

        foreach ($points as [$entities, $scenes, $x, $y, $color, $shown, $target]) {
            $place = $entities[0];
            $point = $map->points()->make([
                'label' => $this->entities[$place]->name,
                'note' => $this->text['map']['notes'][$place] ?? null,
                'color' => $color,
                'x' => round($x * $width, 2),
                'y' => round($y * $height, 2),
                'shown_at_table' => $shown,
            ]);
            $point->target()->associate($target);
            $point->save();
            $this->linkPoint($point, $entities, $scenes);
        }

        $map->ruler = ['x1' => round(0.24 * $width, 2), 'y1' => round(0.71 * $height, 2), 'x2' => round(0.64 * $width, 2), 'y2' => round(0.69 * $height, 2)];
        $map->setView($width / 2, $height / 2, 1.1);
        $map->save();
    }

    /**
     * Sous-carte du phare : la cave inondée, avec grille, jetons (dont l'inconnu, caché), deux
     * gabarits de zone (l'eau qui monte, montrée ; le souffle des Noyeux, encore caché) et ses propres points.
     */
    private function cellarMap(): TableMap
    {
        $text = $this->text['map']['cellar'];
        $plan = $this->image('map-cellar', fn () => ['extension' => 'png', 'contents' => DemoFiles::cellar()]);
        $document = $this->document($this->text['documents']['cellar'], Zone::GameMaster, $plan, ['marshes', 'act3'], ['lighthouse']);
        $this->documents['cellar'] = $document;
        $size = @getimagesizefromstring($plan['contents']);
        [$width, $height] = $size === false ? [1200, 900] : [$size[0], $size[1]];

        $map = new TableMap([
            'name' => $text['name'],
            'grid_enabled' => true,
            'grid_size' => TableMap::defaultGridSize($width),
            'grid_offset_x' => 0,
            'grid_offset_y' => 0,
            'grid_color' => '#e7e5e4',
            'scale_value' => 1.5,
            'scale_unit' => $this->text['map']['unit'],
        ]);
        $map->campaign()->associate($this->campaign);
        $map->document()->associate($document);
        $map->forceFill(['width' => $width, 'height' => $height])->save();
        $cell = (float) $map->grid_size;

        $tokens = [
            ['teska', 0.16, 0.2, 1, '#1d4ed8', false],
            ['dorn', 0.24, 0.3, 1, '#b45309', false],
            ['oriel', 0.12, 0.38, 1, '#7e22ce', false],
            ['lisenn', 0.28, 0.16, 1, '#0f766e', false],
            ['stranger', 0.86, 0.18, 1, '#3f3f46', true],
            ['drowned', 0.62, 0.72, 2, '#b91c1c', false],
        ];

        foreach ($tokens as [$key, $x, $y, $tokenSize, $color, $hidden]) {
            $token = $map->tokens()->make([
                'label' => $this->entities[$key]->name,
                'color' => $color,
                'x' => round($x * $width, 2),
                'y' => round($y * $height, 2),
                'size' => $tokenSize,
                'hidden' => $hidden,
                'show_label' => true,
            ]);
            $token->entity()->associate($this->entities[$key]);
            $token->save();
        }

        // Tailles en cases : l'eau monte sur trois cases autour du puits ; le cône des Noyeux en fait quatre.
        $map->templates()->createMany([
            ['shape' => 'circle', 'size' => 3, 'x' => round(0.55 * $width, 2), 'y' => round(0.62 * $height, 2), 'angle' => 0, 'color' => '#1d4ed8', 'hidden' => false],
            ['shape' => 'cone', 'size' => 4, 'x' => round(0.62 * $width, 2), 'y' => round(0.72 * $height, 2), 'angle' => 200, 'color' => '#b91c1c', 'hidden' => true],
        ]);

        foreach ([
            // fiches, scènes, x, y, couleur, visible à la table
            [['seal', 'ysane'], ['cellar'], 0.42, 0.82, '#b45309', false, 'crate'],
            [['stranger'], ['cellar', 'rising'], 0.86, 0.12, '#57534e', false, 'stairs'],
        ] as [$entities, $scenes, $x, $y, $color, $shown, $key]) {
            $point = $map->points()->make([
                'label' => $text['points'][$key][0],
                'note' => $text['points'][$key][1],
                'color' => $color,
                'x' => round($x * $width, 2),
                'y' => round($y * $height, 2),
                'shown_at_table' => $shown,
            ]);
            $point->save();
            $this->linkPoint($point, $entities, $scenes);
        }

        $map->setView($width / 2, $height / 2, 1);
        $map->save();

        return $map;
    }

    /**
     * @param  list<string>  $entities
     * @param  list<string>  $scenes
     */
    private function linkPoint(MapPoint $point, array $entities, array $scenes): void
    {
        $point->entities()->attach(collect($entities)->mapWithKeys(fn (string $key, int $i) => [$this->entities[$key]->id => ['position' => $i]])->all());
        $point->scenes()->attach(collect($scenes)->mapWithKeys(fn (string $key, int $i) => [$this->scenes[$key]->id => ['position' => $i]])->all());
    }

    /**
     * Ambiances sonores liées aux scènes où elles se jouent. L'orage vient de la bibliothèque
     * personnelle du MJ (« Mes sons ») : un seul fichier, ajouté à la campagne sans copie.
     */
    private function sounds(): void
    {
        $tracks = [
            // clé => scènes, son de la bibliothèque
            'tide' => [['lantern', 'poles', 'ward'], false],
            'mist' => [['poles', 'notebook', 'rising'], false],
            'storm' => [['cellar', 'recast'], true],
        ];

        foreach ($tracks as $key => [$scenes, $library]) {
            // Une démonstration rechargée reprend le son déjà rangé dans la bibliothèque.
            $track = $library
                ? AudioTrack::where('user_id', $this->gm->id)->whereNull('campaign_id')->where('title', $this->text['sounds'][$key])->first()
                : null;

            if ($track === null) {
                $track = $this->storeSound($key, $library ? null : $this->campaign);
            }

            if ($library) {
                $this->campaign->libraryTracks()->syncWithoutDetaching([$track->id]);
            }

            foreach ($scenes as $scene) {
                $this->scenes[$scene]->audioTracks()->attach($track, ['position' => $this->scenes[$scene]->audioTracks()->count()]);
            }
        }
    }

    private function storeSound(string $key, ?Campaign $campaign): AudioTrack
    {
        $file = $this->sound($key);
        $path = 'audio/'.Str::random(40).'.'.$file['extension'];
        Storage::disk(AudioTrack::DISK)->put($path, $file['contents']);

        $track = new AudioTrack([
            'title' => $this->text['sounds'][$key],
            'loop' => true,
            'disk' => AudioTrack::DISK,
            'path' => $path,
            'original_name' => $this->text['sounds'][$key].'.'.$file['extension'],
            'mime_type' => $file['mime'],
            'size' => strlen($file['contents']),
        ]);
        $track->owner()->associate($this->gm);
        $track->campaign()->associate($campaign);
        $track->save();
        $track->tags()->sync($this->tags(['ambience']));

        return $track;
    }

    /**
     * Son fourni dans resources/demo/sounds, sinon l'ambiance synthétisée par le code.
     *
     * @return array{extension: string, mime: string, contents: string}
     */
    private function sound(string $key): array
    {
        foreach (['mp3' => 'audio/mpeg', 'ogg' => 'audio/ogg', 'm4a' => 'audio/mp4', 'wav' => 'audio/wav'] as $extension => $mime) {
            $path = resource_path(self::TEXT_PATH.'/sounds/'.$key.'.'.$extension);

            if (is_file($path)) {
                return ['extension' => $extension, 'mime' => $mime, 'contents' => (string) file_get_contents($path)];
            }
        }

        return ['extension' => 'wav', 'mime' => 'audio/wav', 'contents' => DemoFiles::ambience($key)];
    }

    /**
     * La séance 1, déjà jouée : son résumé, les événements de la chronologie qui s'y rattachent,
     * et une demande d'avis ouverte, pour voir ces pages remplies avant sa première partie.
     */
    private function playedSession(): void
    {
        $start = now()->subWeek()->setTime(20, 30);

        $this->session = $this->campaign->playSessions()->create([
            'number' => 1,
            'started_at' => $start,
            'ended_at' => $start->copy()->addHours(3)->addMinutes(40),
            'summary' => $this->link($this->text['session']['summary']),
        ]);

        // Les notes prises pendant la séance, scène par scène.
        $minutes = 0;

        foreach ($this->text['session']['notes'] as $key => $body) {
            $note = new SessionNote(['body' => $this->link($body)]);
            $note->playSession()->associate($this->session);
            $note->scene()->associate($this->scenes[$key] ?? null);
            $note->author()->associate($this->gm);
            $note->forceFill(['created_at' => $this->session->started_at->copy()->addMinutes($minutes += 50)])->save();
        }

        $feedback = new FeedbackRequest(['anonymous' => true]);
        $feedback->campaign()->associate($this->campaign);
        $feedback->playSession()->associate($this->session);
        $feedback->author()->associate($this->gm);
        $feedback->save();
    }

    /**
     * Un personnage joueur tiré du prétiré Teska, encore sans joueur : sa fiche montre ce qu'un
     * joueur reçoit (fiche, document, règle, secret, information, objets) et le MJ peut « Voir comme » lui.
     * Le MJ le confie à un joueur invité depuis la page Personnages.
     */
    private function character(): void
    {
        $sheet = $this->entities['teska']->copyToCampaign($this->campaign);
        $sheet->setFieldValues([$this->fields['breath']->id => ['value' => 3, 'max' => 5]]);
        $sheet->save();

        $this->character = $this->campaign->playerCharacters()->create([
            'entity_id' => $sheet->id,
            'source_entity_id' => $this->entities['teska']->id,
            'is_active' => true,
        ]);

        $text = $this->text['character'];
        $grants = [
            // nature, élément, scène où il a été donné
            ['entity', ['entity_id' => $this->entities['brannoc']->id], 'lantern'],
            ['document', ['document_id' => $this->documents['notice']->id], 'lantern'],
            ['rule', ['rule_id' => $this->rules['roll']->id], 'lantern'],
            ['possession', ['title' => $text['lantern'][0], 'body' => $text['lantern'][1], 'quantity' => 1], 'lantern'],
            ['possession', ['title' => $text['coins'][0], 'body' => $text['coins'][1], 'quantity' => 12], null],
            ['information', ['title' => $text['rumour'][0], 'body' => $text['rumour'][1]], 'register'],
            ['information', ['secret_id' => $this->secrets['poles']->id, 'title' => $this->secrets['poles']->title, 'body' => $this->secrets['poles']->body], 'register'],
        ];

        foreach ($grants as [$kind, $data, $scene]) {
            $this->character->grants()->create($data + [
                'kind' => $kind,
                'play_session_id' => $this->session->id,
                'scene_id' => $scene !== null ? $this->scenes[$scene]->id : null,
                'granted_by' => $this->gm->id,
            ]);
        }
    }

    /**
     * Dés à la table : les lancers préparés par le MJ (1 d6 + la caractéristique lue sur la fiche,
     * contre une difficulté), un lancer à lui, et trois lancers de la séance 1 dont une demande du MJ.
     */
    private function dice(): void
    {
        $text = $this->text['dice'];
        $trait = fn (string $field, array $extra = []) => [
            'pool' => [['count' => 1, 'sides' => 6]],
            'reading' => 'above',
            'bonus_from' => [['field' => $this->fields[$field]->id, 'take' => 'value', 'name' => $this->fields[$field]->name], ...$extra],
        ];
        $presets = [
            'body' => $trait('body'),
            'skill' => $trait('skill'),
            'mind' => $trait('mind'),
            'heart' => $trait('heart'),
            // Agir pour tenir un serment : un bonus nommé s'ajoute à celui de la fiche.
            'oath' => $trait('heart', [['fixed' => 1, 'name' => $text['oath_bonus']]]),
            'reroll' => ['pool' => [['count' => 1, 'sides' => 6]], 'reading' => 'raw'],
        ];

        $this->campaign->forceFill(['dice_presets' => TableDice::cleanPresets(collect($presets)
            ->map(fn (array $formula, string $key) => ['id' => $key, 'label' => $text['presets'][$key], 'formula' => $formula])
            ->values()->all())])->save();

        // « Mes lancers » du MJ, que lui seul retrouve sur son plateau.
        DB::table('campaign_memberships')->where('campaign_id', $this->campaign->id)->where('user_id', $this->gm->id)->update(['dice_presets' => json_encode(TableDice::cleanPresets([
            ['id' => 'drowned', 'label' => $text['presets']['drowned'], 'formula' => ['pool' => [['count' => 2, 'sides' => 6]], 'reading' => 'sum']],
        ]))]);

        // La même graine à chaque chargement : la séance 1 garde les mêmes dés.
        $roller = new DiceRoller(new Randomizer(new Mt19937(1147)));
        $sheet = fn (array $formula, int $threshold) => SheetValues::apply(DiceFormula::fromArray($formula), $this->campaign, $this->character)
            ->completedWith($threshold, 0);
        $at = $this->session->started_at->copy();

        $request = $this->campaign->diceRequests()->create([
            'user_id' => $this->gm->id,
            'label' => $text['rolls']['elzevir'],
            'formula' => DiceFormula::fromArray($presets['heart'] + ['threshold' => 7])->toArray(),
            'visibility' => TableDice::TABLE,
            'character_ids' => [$this->character->id],
            'closed_at' => $at->copy()->addMinutes(95),
        ]);

        foreach ([
            // libellé, formule, seuil, personnage, demande, visibilité, minutes après le début
            ['follow', $presets['skill'], 7, true, null, TableDice::TABLE, 35],
            ['elzevir', $presets['heart'], 7, true, $request, TableDice::TABLE, 94],
            ['patrol', $presets['reroll'], null, false, null, TableDice::GM, 120],
        ] as [$key, $formula, $threshold, $forCharacter, $answering, $visibility, $minutes]) {
            $formula = $forCharacter ? $sheet($formula, $threshold) : DiceFormula::fromArray($formula);
            $roll = $this->campaign->diceRolls()->make([
                'label' => $text['rolls'][$key],
                'formula' => $formula->toArray(),
                'result' => $roller->roll($formula),
                'visibility' => $visibility,
            ]);
            $roll->user()->associate($this->gm);
            $roll->character()->associate($forCharacter ? $this->character : null);
            $roll->request()->associate($answering);
            $roll->forceFill(['created_at' => $at->copy()->addMinutes($minutes), 'updated_at' => $at->copy()->addMinutes($minutes)])->save();
        }
    }

    /**
     * Ordre d'initiative préparé pour la cave du phare : les quatre prétirés, montrés à la table, et les
     * Noyeux, encore cachés ; la marée agit à part, sans valeur. Le MJ le fait apparaître sur l'écran de table.
     */
    private function initiative(): void
    {
        $entries = [
            // fiche, valeur (null : sans ordre), couleur, montrée aux joueurs
            ['teska', 5, '#1d4ed8', true],
            ['lisenn', 4, '#0f766e', true],
            ['drowned', 4, '#b91c1c', false],
            ['dorn', 3, '#b45309', true],
            ['oriel', 2, '#7e22ce', true],
        ];

        $state = ['shown' => false, 'round' => 1, 'turn' => null, 'next' => 1, 'entries' => []];

        foreach ($entries as [$key, $value, $color, $visible]) {
            $entity = $key === 'teska' ? $this->character->entity : $this->entities[$key];
            $state['entries'][] = ['id' => $state['next']++, 'label' => $entity->name, 'entity_id' => $entity->id, 'color' => $color, 'value' => (float) $value, 'ordered' => true, 'visible' => $visible];
        }

        $state['entries'][] = ['id' => $state['next']++, 'label' => $this->text['dice']['tide'], 'entity_id' => null, 'color' => '#57534e', 'value' => null, 'ordered' => false, 'visible' => true];

        $this->campaign->forceFill(['table_initiative' => $state])->save();
    }

    /** La liste « À jouer » : des idées à placer, liées à une scène, une règle ou un personnage ; une déjà faite. */
    private function toPlay(): void
    {
        $items = [
            // clé => scène, règle, pour le personnage, fait
            'curfew' => ['lantern', null, false, true],
            'bell' => ['poles', null, false, false],
            'mist' => [null, 'mist', false, false],
            'debt' => [null, null, true, false],
        ];

        $position = 0;

        foreach ($items as $key => [$scene, $rule, $forCharacter, $done]) {
            $item = new ToPlayItem([
                'body' => $this->text['to_play'][$key],
                'position' => ++$position,
                'done_at' => $done ? $this->session->ended_at : null,
            ]);
            $item->campaign()->associate($this->campaign);
            $item->scene()->associate($scene !== null ? $this->scenes[$scene] : null);
            $item->rule()->associate($rule !== null ? $this->rules[$rule] : null);
            $item->character()->associate($forCharacter ? $this->character : null);
            $item->save();
        }

        // Les fiches épinglées, toujours sous la main en mode Session.
        foreach (['ysane', 'brannoc', 'quay'] as $position => $key) {
            $this->campaign->pins()->attach($this->entities[$key]->id, ['position' => $position]);
        }
    }

    private function timeline(): void
    {
        $events = [
            'ash' => ['world', Zone::Public],
            'first_oath' => ['world', Zone::Public],
            'great_mist' => ['world', Zone::Public],
            'tile_1147' => ['world', Zone::GameMaster],
            'drowning' => ['played', Zone::Public],
            'missing' => ['played', Zone::GameMaster],
            'session1' => ['played', Zone::Public],
            'poles_moved' => ['planned', Zone::GameMaster],
            'invasion' => ['planned', Zone::GameMaster],
            'ending' => ['planned', Zone::GameMaster],
        ];

        $position = 0;

        foreach ($events as $key => [$kind, $zone]) {
            [$date, $title, $description] = $this->text['timeline'][$key];

            $event = new TimelineEvent([
                'kind' => $kind,
                'date_label' => $date,
                'title' => $title,
                'description' => $description,
                'zone' => $zone,
            ]);
            $event->campaign()->associate($this->campaign);
            $event->forceFill([
                'user_id' => $this->gm->id,
                'position' => ++$position,
                'play_session_id' => $key === 'session1' ? $this->session->id : null,
            ])->save();
        }
    }

    /** Une couleur par tag, pour que la page Tags et les pastilles le montrent. */
    private function colourTags(): void
    {
        $colours = [
            'city' => 'stone', 'act1' => 'green', 'act2' => 'amber', 'act3' => 'red', 'intrigue' => 'violet',
            'hall' => 'blue', 'quays' => 'teal', 'marshes' => 'teal', 'guard' => 'stone', 'pregen' => 'pink',
            'base' => 'blue', 'oaths' => 'violet', 'house' => 'amber', 'ambience' => 'teal',
        ];

        foreach ($colours as $key => $colour) {
            Tag::where('user_id', $this->gm->id)->where('name', $this->text['tags'][$key])->whereNull('color')->update(['color' => $colour]);
        }
    }

    /** Couverture du jeu ou du monde : l'image fournie, sinon celle dessinée par le code. */
    private function cover(GameSystem|World $item, string $key, string $sky, string $sea): void
    {
        $image = $this->image($key, fn () => DemoFiles::cover($sky, $sea));
        $item->image_path = 'images/'.Str::random(40).'.'.$image['extension'];
        Storage::disk($item::IMAGE_DISK)->put($item->image_path, $image['contents']);
        $item->save();
    }

    /**
     * @param  list<string>  $keys
     * @return list<int>
     */
    private function tags(array $keys): array
    {
        return Tag::idsFromInput($this->gm, collect($keys)->map(fn (string $key) => $this->text['tags'][$key])->implode(', '));
    }

    /** Remplace « [[clé]] » par le lien interne « [[Nom|id]] » attendu par l'application. */
    private function link(string $text): string
    {
        return preg_replace_callback('/\[\[([a-z0-9_]+)\]\]/', function (array $match): string {
            $entity = $this->entities[$match[1]] ?? null;

            return $entity === null ? $match[0] : '[['.$entity->name.'|'.$entity->id.']]';
        }, $text) ?? $text;
    }

    /**
     * Image fournie dans resources/demo/images, sinon celle dessinée par le code.
     *
     * @param  callable(): (string|array{extension: string, contents: string}|null)  $fallback
     * @return array{extension: string, contents: string}|null
     */
    private function image(string $key, callable $fallback): ?array
    {
        foreach (['webp', 'jpg', 'jpeg', 'png', 'pdf'] as $extension) {
            $path = resource_path(self::TEXT_PATH.'/images/'.$key.'.'.$extension);

            if (is_file($path)) {
                return ['extension' => $extension === 'jpeg' ? 'jpg' : $extension, 'contents' => (string) file_get_contents($path)];
            }
        }

        $generated = $fallback();

        return is_string($generated) ? ['extension' => 'png', 'contents' => $generated] : $generated;
    }

    private static function mime(string $extension): string
    {
        return match ($extension) {
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };
    }
}
