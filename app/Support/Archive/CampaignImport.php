<?php

namespace App\Support\Archive;

use App\Enums\CampaignRole;
use App\Enums\CampaignStatus;
use App\Enums\FieldType;
use App\Enums\RuleOrigin;
use App\Enums\RuleStatus;
use App\Enums\SceneStatus;
use App\Enums\Zone;
use App\Livewire\Entities\Show as EntityShow;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\CharacterGrant;
use App\Models\Document;
use App\Models\Entity;
use App\Models\Secret;
use App\Models\Tag;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Support\EntityLinks;
use App\Support\TableTheme;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use ZipArchive;

/**
 * Recrée une campagne à partir d'une archive de CampaignExport, pour l'utilisateur qui l'importe :
 * un nouveau jeu, un nouveau monde (s'il y en avait un) et la campagne, dont il devient le MJ.
 *
 * Le fichier vient de l'extérieur : chaque valeur est vérifiée (types, longueurs, listes fermées),
 * seuls les fichiers référencés sont lus, et leur type réel est contrôlé avant d'être enregistré.
 * Tous les identifiants sont recalculés, y compris dans les valeurs de champs et les liens [[Nom|id]].
 */
final class CampaignImport
{
    private const MAX_JSON = 50 * 1024 * 1024;

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    private ZipArchive $zip;

    /** @var list<array{0: string, 1: string}> fichiers écrits, effacés si l'import échoue */
    private array $written = [];

    /** @var array<string, array<int, int>> ancien id => nouvel id, par nature */
    private array $map = ['types' => [], 'tags' => [], 'fields' => [], 'entities' => [], 'documents' => [], 'rules' => [], 'scenes' => [], 'secrets' => [], 'sessions' => [], 'characters' => []];

    /** @var array<int, FieldType> type de chaque champ créé, par nouvel identifiant */
    private array $fieldTypes = [];

    private string $now;

    public function __construct(private User $user) {}

    /** @throws ArchiveException */
    public function handle(string $path): Campaign
    {
        $this->zip = new ZipArchive;

        if ($this->zip->open($path, ZipArchive::RDONLY) !== true) {
            throw new ArchiveException(__('Ce fichier n’est pas une archive LoreMundi.'));
        }

        try {
            $data = $this->manifest();
            $this->now = now()->toDateTimeString();

            return ActivityLog::muted(fn () => DB::transaction(fn () => $this->import($data)));
        } catch (Throwable $e) {
            foreach ($this->written as [$disk, $file]) {
                Storage::disk($disk)->delete($file);
            }

            throw $e;
        } finally {
            $this->zip->close();
        }
    }

    /**
     * Ajoute un modèle de jeu (CampaignExport::template) au jeu de la campagne : types de fiche,
     * étiquettes, champs et règles absents. Ce qui existe déjà (même nom) n'est pas touché.
     *
     * @return array{fields: int, rules: int}
     *
     * @throws ArchiveException
     */
    public function template(string $json, Campaign $campaign): array
    {
        $data = json_decode($json, true);

        if (! is_array($data) || ($data['format'] ?? null) !== CampaignExport::TEMPLATE_FORMAT) {
            throw new ArchiveException(__('Ce fichier n’est pas un modèle LoreMundi.'));
        }

        if (! is_int($data['version'] ?? null) || $data['version'] > CampaignExport::VERSION) {
            throw new ArchiveException(__('Ce modèle vient d’une version plus récente de LoreMundi.'));
        }

        $this->now = now()->toDateTimeString();

        return ActivityLog::muted(fn () => DB::transaction(function () use ($data, $campaign) {
            $this->entityTypes($this->list($data['entity_types'] ?? []));
            $this->tags($this->list($data['tags'] ?? []));
            $this->fields($this->list($data['field_definitions'] ?? []), $campaign->game_system_id);
            $this->rules(array_map(fn (array $rule) => ['in_campaign' => false] + $rule, $this->list($data['rules'] ?? [])), $campaign);

            return ['fields' => count($this->map['fields']), 'rules' => count($this->map['rules'])];
        }));
    }

    /** @return array<string, mixed> */
    private function manifest(): array
    {
        $stat = $this->zip->statName('campagne.json');

        if ($stat === false || $stat['size'] > self::MAX_JSON) {
            throw new ArchiveException(__('Ce fichier n’est pas une archive LoreMundi.'));
        }

        $data = json_decode((string) $this->zip->getFromName('campagne.json'), true);

        if (! is_array($data) || ($data['format'] ?? null) !== CampaignExport::FORMAT) {
            throw new ArchiveException(__('Ce fichier n’est pas une archive LoreMundi.'));
        }

        if (! is_int($data['version'] ?? null) || $data['version'] > CampaignExport::VERSION) {
            throw new ArchiveException(__('Cette archive vient d’une version plus récente de LoreMundi.'));
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    private function import(array $data): Campaign
    {
        $campaignData = $this->array($data['campaign'] ?? null);
        $systemData = $this->array($data['game_system'] ?? null);
        $worldData = isset($data['world']) ? $this->array($data['world']) : null;

        $gameSystem = $this->user->gameSystems()->create([
            'name' => $this->string($systemData['name'] ?? null, 255) ?? __('Jeu importé'),
            'description' => $this->string($systemData['description'] ?? null, 20000),
            'image_path' => $this->image($systemData['image'] ?? null, 'images'),
        ]);

        $world = $worldData === null ? null : $this->user->worlds()->create([
            'name' => $this->string($worldData['name'] ?? null, 255) ?? __('Monde importé'),
            'description' => $this->string($worldData['description'] ?? null, 20000),
            'image_path' => $this->image($worldData['image'] ?? null, 'images'),
        ]);

        $campaign = new Campaign([
            'name' => $this->string($campaignData['name'] ?? null, 255) ?? __('Campagne importée'),
            'description' => $this->string($campaignData['description'] ?? null, 5000),
            'status' => CampaignStatus::Active,
        ]);
        $campaign->owner()->associate($this->user);
        $campaign->gameSystem()->associate($gameSystem);
        $campaign->world()->associate($world);
        $campaign->table_theme = array_key_exists($campaignData['table_theme'] ?? null, TableTheme::THEMES)
            ? $campaignData['table_theme']
            : TableTheme::DEFAULT;
        $campaign->exchanges_need_approval = (bool) ($campaignData['exchanges_need_approval'] ?? true);
        $campaign->save();
        $campaign->members()->attach($this->user, ['role' => CampaignRole::GameMaster->value]);

        $this->entityTypes($this->list($data['entity_types'] ?? []));
        $this->tags($this->list($data['tags'] ?? []));
        $this->fields($this->list($data['field_definitions'] ?? []), $gameSystem->id);
        $this->entities($this->list($data['entities'] ?? []), $campaign, $world?->id);
        $this->relations($this->list($data['relations'] ?? []), $campaign, $world !== null);
        $this->states($this->list($data['entity_states'] ?? []), $campaign);
        $this->rules($this->list($data['rules'] ?? []), $campaign);
        $this->documents($this->list($data['documents'] ?? []), $campaign, $world?->id);
        $this->remapFieldValues();
        $this->scenarios($this->list($data['scenarios'] ?? []), $campaign);
        $table = isset($data['table']) ? $this->array($data['table']) : null;

        if ($table !== null) {
            $this->sessions($this->list($table['sessions'] ?? []), $campaign);
        }

        $this->campaignItems($data, $campaign);

        if ($table !== null) {
            $this->table($table, $campaign);
        }

        return $campaign;
    }

    /** @param list<array<string, mixed>> $types */
    private function entityTypes(array $types): void
    {
        $standard = DB::table('entity_types')->whereNull('user_id')->whereNotNull('key')->pluck('id', 'key');
        $fallback = $standard['character'] ?? DB::table('entity_types')->whereNull('user_id')->value('id');

        foreach ($types as $type) {
            $id = $this->int($type['id'] ?? null);
            $key = $this->string($type['key'] ?? null, 255);
            $name = $this->string($type['name'] ?? null, 255);

            if ($id === null) {
                continue;
            }

            if ($key !== null && isset($standard[$key])) {
                $this->map['types'][$id] = $standard[$key];
            } elseif ($name !== null) {
                $this->map['types'][$id] = DB::table('entity_types')->where('user_id', $this->user->id)->whereRaw('lower(name) = ?', [mb_strtolower($name)])->value('id')
                    ?? DB::table('entity_types')->insertGetId(['user_id' => $this->user->id, 'name' => $name, 'created_at' => $this->now, 'updated_at' => $this->now]);
            } else {
                $this->map['types'][$id] = $fallback;
            }
        }

        $this->map['types'][0] = $fallback;
    }

    /** @param list<array<string, mixed>> $tags */
    private function tags(array $tags): void
    {
        foreach ($tags as $tag) {
            $id = $this->int($tag['id'] ?? null);
            $name = $this->string($tag['name'] ?? null, 60);

            if ($id === null || $name === null) {
                continue;
            }

            $color = in_array($tag['color'] ?? null, array_keys(Tag::COLORS), true) ? $tag['color'] : null;
            $this->map['tags'][$id] = DB::table('tags')->where('user_id', $this->user->id)->whereRaw('lower(name) = ?', [mb_strtolower($name)])->value('id')
                ?? DB::table('tags')->insertGetId(['user_id' => $this->user->id, 'name' => $name, 'color' => $color, 'created_at' => $this->now, 'updated_at' => $this->now]);
        }
    }

    /** @param list<array<string, mixed>> $fields */
    private function fields(array $fields, int $gameSystemId): void
    {
        // Champs déjà présents dans le jeu (import d'un modèle) : gardés tels quels.
        $seen = DB::table('field_definitions')->where('game_system_id', $gameSystemId)->get(['entity_type_id', 'name'])
            ->mapWithKeys(fn ($field) => [$field->entity_type_id.'|'.mb_strtolower($field->name) => true])->all();
        $position = (int) DB::table('field_definitions')->where('game_system_id', $gameSystemId)->max('position');

        foreach ($fields as $field) {
            $id = $this->int($field['id'] ?? null);
            $name = $this->string($field['name'] ?? null, 100);
            $type = FieldType::tryFrom((string) ($field['type'] ?? ''));
            $typeId = isset($field['entity_type_id']) ? ($this->map['types'][$this->int($field['entity_type_id']) ?? -1] ?? null) : null;

            // Un champ lié à un type inconnu, ou en double, est ignoré.
            if ($id === null || $name === null || $type === null || (isset($field['entity_type_id']) && $typeId === null) || isset($seen[$typeId.'|'.mb_strtolower($name)])) {
                continue;
            }
            $seen[$typeId.'|'.mb_strtolower($name)] = true;

            $options = is_array($field['options'] ?? null) ? json_encode($field['options'], JSON_UNESCAPED_UNICODE) : null;

            $this->map['fields'][$id] = DB::table('field_definitions')->insertGetId([
                'game_system_id' => $gameSystemId,
                'entity_type_id' => $typeId,
                'group' => $this->string($field['group'] ?? null, 100),
                'name' => $name,
                'type' => $type->value,
                'options' => $options !== false && $options !== null && strlen($options) <= 20000 ? $options : null,
                'zone' => $this->zone($field['zone'] ?? null, Zone::Public),
                'position' => ++$position,
                'player_editable' => (bool) ($field['player_editable'] ?? false),
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
            $this->fieldTypes[$this->map['fields'][$id]] = $type;
        }
    }

    /** @param list<array<string, mixed>> $entities */
    private function entities(array $entities, Campaign $campaign, ?int $worldId): void
    {
        $created = [];

        foreach ($entities as $entity) {
            $id = $this->int($entity['id'] ?? null);
            $name = $this->string($entity['name'] ?? null, 255);

            if ($id === null || $name === null || isset($this->map['entities'][$id])) {
                continue;
            }

            $inWorld = $worldId !== null && ($entity['in_world'] ?? false) === true;
            $newId = DB::table('entities')->insertGetId([
                'user_id' => $this->user->id,
                'world_id' => $inWorld ? $worldId : null,
                'campaign_id' => $inWorld ? null : $campaign->id,
                'entity_type_id' => $this->map['types'][$this->int($entity['entity_type_id'] ?? null) ?? 0] ?? $this->map['types'][0],
                'name' => $name,
                'summary' => $this->string($entity['summary'] ?? null, 500),
                'description' => $this->string($entity['description'] ?? null, 20000),
                'gm_notes' => $this->string($entity['gm_notes'] ?? null, 20000),
                'field_values' => json_encode($this->fieldValues($entity['field_values'] ?? null), JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT),
                'image_path' => $this->image($entity['image'] ?? null, 'entities'),
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
            $this->map['entities'][$id] = $newId;
            $created[$newId] = $entity;
        }

        // Les liens entre fiches ne peuvent être recalculés qu'une fois toutes les fiches créées.
        foreach ($created as $newId => $entity) {
            DB::table('entities')->where('id', $newId)->update([
                'summary' => $this->links($this->string($entity['summary'] ?? null, 500)),
                'description' => $this->links($this->string($entity['description'] ?? null, 20000)),
                'gm_notes' => $this->links($this->string($entity['gm_notes'] ?? null, 20000)),
            ]);
            $this->attachTags('entity_tag', 'entity_id', $newId, $entity['tags'] ?? []);

            foreach ($this->list($entity['attachments'] ?? []) as $attachment) {
                $file = $this->extract($attachment['file'] ?? null, explode(',', EntityShow::ATTACHMENT_MIMES), 'attachments/'.$newId);

                if ($file !== null) {
                    DB::table('attachments')->insert([
                        'user_id' => $this->user->id,
                        'entity_id' => $newId,
                        'zone' => $this->zone($attachment['zone'] ?? null, Zone::GameMaster),
                        'disk' => Entity::FILES_DISK,
                        'path' => $file['path'],
                        'original_name' => $this->string($attachment['original_name'] ?? null, 255) ?? basename($file['path']),
                        'mime_type' => $file['mime'],
                        'size' => $file['size'],
                        'created_at' => $this->now,
                        'updated_at' => $this->now,
                    ]);
                }
            }
        }
    }

    /** @param list<array<string, mixed>> $relations */
    private function relations(array $relations, Campaign $campaign, bool $hasWorld): void
    {
        foreach ($relations as $relation) {
            $from = $this->entity($relation['from'] ?? null);
            $to = $this->entity($relation['to'] ?? null);
            $label = $this->string($relation['label'] ?? null, 255);

            if ($from === null || $to === null || $label === null || $from === $to) {
                continue;
            }

            // Une relation de monde ne peut relier que des fiches du monde.
            $inWorld = $hasWorld && ($relation['in_world'] ?? false) === true
                && DB::table('entities')->whereIn('id', [$from, $to])->whereNotNull('world_id')->count() === 2;

            DB::table('entity_relations')->insert([
                'user_id' => $this->user->id,
                'from_entity_id' => $from,
                'to_entity_id' => $to,
                'campaign_id' => $inWorld ? null : $campaign->id,
                'label' => $label,
                'reverse_label' => $this->string($relation['reverse_label'] ?? null, 255),
                'zone' => $this->zone($relation['zone'] ?? null, Zone::Public),
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
        }
    }

    /** @param list<array<string, mixed>> $states */
    private function states(array $states, Campaign $campaign): void
    {
        $done = [];

        foreach ($states as $state) {
            $entityId = $this->entity($state['entity_id'] ?? null);

            if ($entityId === null || isset($done[$entityId])) {
                continue;
            }
            $done[$entityId] = true;

            DB::table('campaign_entity_states')->insert([
                'campaign_id' => $campaign->id,
                'entity_id' => $entityId,
                'status' => $this->string($state['status'] ?? null, 60),
                'gm_notes' => $this->links($this->string($state['gm_notes'] ?? null, 20000)),
                'overrides' => json_encode($this->fieldValues($state['overrides'] ?? null), JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT),
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
        }
    }

    /** @param list<array<string, mixed>> $rules */
    private function rules(array $rules, Campaign $campaign): void
    {
        $existing = DB::table('rules')->where('game_system_id', $campaign->game_system_id)->whereNull('campaign_id')
            ->pluck('title')->mapWithKeys(fn (string $title) => [mb_strtolower($title) => true])->all();

        foreach ($rules as $rule) {
            $id = $this->int($rule['id'] ?? null);
            $title = $this->string($rule['title'] ?? null, 255);
            $inCampaign = ($rule['in_campaign'] ?? false) === true;

            if ($id === null || $title === null || (! $inCampaign && isset($existing[mb_strtolower($title)]))) {
                continue;
            }
            if (! $inCampaign) {
                $existing[mb_strtolower($title)] = true;
            }

            $newId = DB::table('rules')->insertGetId([
                'user_id' => $this->user->id,
                'game_system_id' => $inCampaign ? null : $campaign->game_system_id,
                'campaign_id' => $inCampaign ? $campaign->id : null,
                'title' => $title,
                'category' => $this->string($rule['category'] ?? null, 100),
                'summary' => $this->links($this->string($rule['summary'] ?? null, 2000)),
                'procedure' => $this->links($this->string($rule['procedure'] ?? null, 20000)),
                'gm_notes' => $this->links($this->string($rule['gm_notes'] ?? null, 20000)),
                'source' => $this->string($rule['source'] ?? null, 255),
                'origin' => RuleOrigin::tryFrom((string) ($rule['origin'] ?? ''))?->value ?? RuleOrigin::Reference->value,
                'status' => RuleStatus::tryFrom((string) ($rule['status'] ?? ''))?->value ?? RuleStatus::Available->value,
                'zone' => $this->zone($rule['zone'] ?? null, Zone::Public),
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
            $this->map['rules'][$id] = $newId;
            $this->attachTags('rule_tag', 'rule_id', $newId, $rule['tags'] ?? []);
        }
    }

    /** @param list<array<string, mixed>> $documents */
    private function documents(array $documents, Campaign $campaign, ?int $worldId): void
    {
        foreach ($documents as $document) {
            $id = $this->int($document['id'] ?? null);
            $file = $this->extract($document['file'] ?? null, explode(',', Document::MIMES), 'documents');

            if ($id === null || $file === null) {
                continue;
            }

            $scope = $document['scope'] ?? 'campaign';
            $originalName = $this->string($document['original_name'] ?? null, 255) ?? basename($file['path']);

            $newId = DB::table('documents')->insertGetId([
                'user_id' => $this->user->id,
                'game_system_id' => $scope === 'game_system' ? $campaign->game_system_id : null,
                'world_id' => $scope === 'world' && $worldId !== null ? $worldId : null,
                'campaign_id' => $scope === 'game_system' || ($scope === 'world' && $worldId !== null) ? null : $campaign->id,
                'title' => $this->string($document['title'] ?? null, 255) ?? $originalName,
                'description' => $this->string($document['description'] ?? null, 5000),
                'zone' => $this->zone($document['zone'] ?? null, Zone::GameMaster),
                'disk' => Document::DISK,
                'path' => $file['path'],
                'original_name' => $originalName,
                'mime_type' => $file['mime'],
                'size' => $file['size'],
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
            $this->map['documents'][$id] = $newId;
            $this->attachTags('document_tag', 'document_id', $newId, $document['tags'] ?? []);

            foreach (['entities' => ['document_entity', 'entity_id'], 'rules' => ['document_rule', 'rule_id']] as $key => [$table, $column]) {
                $rows = collect($this->ids($document[$key] ?? [], $key))
                    ->map(fn (int $other) => ['document_id' => $newId, $column => $other, 'created_at' => $this->now, 'updated_at' => $this->now]);
                DB::table($table)->insertOrIgnore($rows->all());
            }
        }
    }

    /** @param list<array<string, mixed>> $scenarios */
    private function scenarios(array $scenarios, Campaign $campaign): void
    {
        foreach ($scenarios as $position => $scenario) {
            $scenarioId = DB::table('scenarios')->insertGetId([
                'campaign_id' => $campaign->id,
                'name' => $this->string($scenario['name'] ?? null, 255) ?? __('Scénario'),
                'summary' => $this->string($scenario['summary'] ?? null, 5000),
                'position' => $position + 1,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);

            foreach ($this->list($scenario['scenes'] ?? []) as $scenePosition => $scene) {
                $id = $this->int($scene['id'] ?? null);

                $sceneId = DB::table('scenes')->insertGetId([
                    'scenario_id' => $scenarioId,
                    'chapter' => $this->string($scene['chapter'] ?? null, 100),
                    'name' => $this->string($scene['name'] ?? null, 255) ?? __('Scène'),
                    'description' => $this->links($this->string($scene['description'] ?? null, 20000)),
                    'status' => SceneStatus::tryFrom((string) ($scene['status'] ?? ''))?->value ?? SceneStatus::Planned->value,
                    'position' => $scenePosition + 1,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ]);

                if ($id !== null) {
                    $this->map['scenes'][$id] = $sceneId;
                }

                $this->attachTags('scene_tag', 'scene_id', $sceneId, $scene['tags'] ?? []);

                $entities = [];
                foreach ($this->list($scene['entities'] ?? []) as $row) {
                    $entityId = $this->entity($row['id'] ?? null);
                    if ($entityId !== null && ! isset($entities[$entityId])) {
                        $entities[$entityId] = ['scene_id' => $sceneId, 'entity_id' => $entityId, 'note' => $this->links($this->string($row['note'] ?? null, 150), 150), 'position' => count($entities)];
                    }
                }
                DB::table('scene_entity')->insert(array_values($entities));

                foreach (['documents' => ['document_scene', 'document_id'], 'rules' => ['rule_scene', 'rule_id']] as $key => [$table, $column]) {
                    $rows = collect($this->ids($scene[$key] ?? [], $key))->values()
                        ->map(fn (int $other, int $i) => ['scene_id' => $sceneId, $column => $other, 'position' => $i]);
                    DB::table($table)->insertOrIgnore($rows->all());
                }
            }
        }
    }

    /** Épingles, « À jouer », secrets, cartes et chronologie. @param array<string, mixed> $data */
    private function campaignItems(array $data, Campaign $campaign): void
    {
        $pins = collect($this->ids($data['pins'] ?? [], 'entities'))->values()
            ->map(fn (int $entityId, int $i) => ['campaign_id' => $campaign->id, 'entity_id' => $entityId, 'position' => $i, 'created_at' => $this->now, 'updated_at' => $this->now]);
        DB::table('campaign_pins')->insertOrIgnore($pins->all());

        foreach ($this->list($data['to_play'] ?? []) as $position => $item) {
            $body = $this->string($item['body'] ?? null, 500);
            $ruleId = isset($item['rule_id']) ? ($this->map['rules'][$this->int($item['rule_id']) ?? -1] ?? null) : null;

            if ($body === null && $ruleId === null) {
                continue;
            }

            DB::table('to_play_items')->insert([
                'campaign_id' => $campaign->id,
                'scene_id' => isset($item['scene_id']) ? ($this->map['scenes'][$this->int($item['scene_id']) ?? -1] ?? null) : null,
                'rule_id' => $ruleId,
                'body' => $this->links($body, 500) ?? '',
                'position' => $position,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
        }

        foreach ($this->list($data['secrets'] ?? []) as $secret) {
            $title = $this->string($secret['title'] ?? null, 255);

            if ($title === null) {
                continue;
            }

            $secretId = $this->map['secrets'][$this->int($secret['id'] ?? null) ?? -1] = DB::table('secrets')->insertGetId([
                'campaign_id' => $campaign->id,
                'user_id' => $this->user->id,
                'title' => $title,
                'body' => $this->links($this->string($secret['body'] ?? null, 20000)),
                'kind' => in_array($secret['kind'] ?? null, Secret::KINDS, true) ? $secret['kind'] : 'truth',
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);

            foreach (['entities' => 'entity', 'documents' => 'document', 'scenes' => 'scene'] as $key => $single) {
                $rows = collect($this->ids($secret[$key] ?? [], $key))->map(fn (int $other) => ['secret_id' => $secretId, "{$single}_id" => $other]);
                DB::table("{$single}_secret")->insertOrIgnore($rows->all());
            }
        }

        foreach ($this->list($data['maps'] ?? []) as $map) {
            $documentId = $this->map['documents'][$this->int($map['document_id'] ?? null) ?? -1] ?? null;
            $width = $this->int($map['width'] ?? null);
            $height = $this->int($map['height'] ?? null);

            if ($documentId === null || ! $width || ! $height) {
                continue;
            }

            $mapId = DB::table('table_maps')->insertGetId([
                'campaign_id' => $campaign->id,
                'document_id' => $documentId,
                'name' => $this->string($map['name'] ?? null, 255) ?? __('Carte'),
                'width' => min($width, 100000),
                'height' => min($height, 100000),
                'grid_enabled' => (bool) ($map['grid_enabled'] ?? false),
                'grid_size' => $this->number($map['grid_size'] ?? null, 5, 2000) ?? 50,
                'grid_offset_x' => $this->number($map['grid_offset_x'] ?? null, 0, 2000) ?? 0,
                'grid_offset_y' => $this->number($map['grid_offset_y'] ?? null, 0, 2000) ?? 0,
                'grid_color' => preg_match('/^#[0-9a-f]{6}$/i', (string) ($map['grid_color'] ?? '')) ? $map['grid_color'] : '#1c1917',
                'scale_value' => $this->number($map['scale_value'] ?? null, 0, 100000),
                'scale_unit' => $this->string($map['scale_unit'] ?? null, 20),
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);

            foreach ($this->list($map['tokens'] ?? []) as $token) {
                $label = $this->string($token['label'] ?? null, 100);
                $entityId = isset($token['entity_id']) ? $this->entity($token['entity_id']) : null;

                if ($label === null || (isset($token['entity_id']) && $entityId === null)) {
                    continue;
                }

                DB::table('map_tokens')->insert([
                    'table_map_id' => $mapId,
                    'entity_id' => $entityId,
                    'label' => $label,
                    'color' => preg_match('/^#[0-9a-f]{6}$/i', (string) ($token['color'] ?? '')) ? $token['color'] : '#b45309',
                    'x' => $this->number($token['x'] ?? null, 0, $width) ?? 0,
                    'y' => $this->number($token['y'] ?? null, 0, $height) ?? 0,
                    'size' => $this->number($token['size'] ?? null, 0.5, 10) ?? 1,
                    'hidden' => (bool) ($token['hidden'] ?? true),
                    'show_label' => (bool) ($token['show_label'] ?? true),
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ]);
            }
        }

        foreach ($this->list($data['timeline'] ?? []) as $position => $event) {
            $title = $this->string($event['title'] ?? null, 255);
            $kind = in_array($event['kind'] ?? null, TimelineEvent::KINDS, true) ? $event['kind'] : null;

            if ($title === null || $kind === null) {
                continue;
            }

            DB::table('timeline_events')->insert([
                'campaign_id' => $campaign->id,
                'user_id' => $this->user->id,
                'kind' => $kind,
                'date_label' => $this->string($event['date_label'] ?? null, 100),
                'title' => $title,
                'description' => $this->links($this->string($event['description'] ?? null, 20000)),
                'zone' => $this->zone($event['zone'] ?? null, Zone::GameMaster),
                'scene_id' => $this->map['scenes'][$this->int($event['scene_id'] ?? null) ?? -1] ?? null,
                'play_session_id' => $this->map['sessions'][$this->int($event['play_session_id'] ?? null) ?? -1] ?? null,
                'position' => $position + 1,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
        }
    }

    /**
     * Séances d'une sauvegarde complète. Une seule peut rester ouverte, comme dans l'application.
     *
     * @param  list<array<string, mixed>>  $sessions
     */
    private function sessions(array $sessions, Campaign $campaign): void
    {
        $open = false;

        foreach ($sessions as $session) {
            $id = $this->int($session['id'] ?? null);
            $number = $this->int($session['number'] ?? null);
            $startedAt = $this->date($session['started_at'] ?? null);

            if ($id === null || $number === null || $startedAt === null) {
                continue;
            }

            $endedAt = $this->date($session['ended_at'] ?? null);

            if ($endedAt === null && $open) {
                $endedAt = $startedAt;
            }
            $open = $open || $endedAt === null;

            $this->map['sessions'][$id] = DB::table('play_sessions')->insertGetId([
                'campaign_id' => $campaign->id,
                'number' => $number,
                'title' => $this->string($session['title'] ?? null, 255),
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
        }
    }

    /**
     * La table d'une sauvegarde complète : personnages (sans joueur, à confier de nouveau), ce qu'ils
     * ont reçu, notes de séance et notes partagées des joueurs. Les comptes ne voyagent pas : ces notes
     * sont attribuées à la personne qui importe. Messages et journal restent consultables dans l'archive.
     *
     * @param  array<string, mixed>  $table
     */
    private function table(array $table, Campaign $campaign): void
    {
        foreach ($this->list($table['characters'] ?? []) as $character) {
            $id = $this->int($character['id'] ?? null);
            $entityId = $this->entity($character['entity_id'] ?? null);

            if ($id === null || $entityId === null || DB::table('player_characters')->where('entity_id', $entityId)->exists()) {
                continue;
            }

            $sheet = $this->extract($character['sheet'] ?? null, ['pdf'], 'character-sheets/'.$campaign->id);
            $this->map['characters'][$id] = DB::table('player_characters')->insertGetId([
                'campaign_id' => $campaign->id,
                'entity_id' => $entityId,
                'user_id' => null,
                'is_active' => (bool) ($character['is_active'] ?? true),
                'locked' => (bool) ($character['locked'] ?? false),
                'sheet_path' => $sheet['path'] ?? null,
                'sheet_name' => $sheet ? ($this->string($character['sheet_name'] ?? null, 255) ?? 'fiche.pdf') : null,
                'sheet_size' => $sheet['size'] ?? null,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
        }

        $character = fn (mixed $id) => $this->map['characters'][$this->int($id) ?? -1] ?? null;
        $mapped = fn (string $kind, mixed $id) => $id === null ? null : ($this->map[$kind][$this->int($id) ?? -1] ?? false);

        foreach ($this->list($table['grants'] ?? []) as $grant) {
            $characterId = $character($grant['character_id'] ?? null);
            $kind = is_string($grant['kind'] ?? null) && array_key_exists($grant['kind'], CharacterGrant::KINDS) ? $grant['kind'] : null;
            $targets = [
                'entity_id' => $mapped('entities', $grant['entity_id'] ?? null),
                'document_id' => $mapped('documents', $grant['document_id'] ?? null),
                'rule_id' => $mapped('rules', $grant['rule_id'] ?? null),
                'secret_id' => $mapped('secrets', $grant['secret_id'] ?? null),
            ];

            // Ce qui a été donné doit exister dans la copie ; sinon l'élément est abandonné.
            if ($characterId === null || $kind === null || in_array(false, $targets, true)) {
                continue;
            }

            DB::table('character_grants')->insert([
                'player_character_id' => $characterId,
                'kind' => $kind,
                ...$targets,
                'play_session_id' => $mapped('sessions', $grant['play_session_id'] ?? null) ?: null,
                'scene_id' => $mapped('scenes', $grant['scene_id'] ?? null) ?: null,
                'title' => $this->string($grant['title'] ?? null, 255),
                'body' => $this->links($this->string($grant['body'] ?? null, 20000)),
                'quantity' => $this->int($grant['quantity'] ?? null),
                'added_by_player' => (bool) ($grant['added_by_player'] ?? false),
                'validated_at' => $this->date($grant['validated_at'] ?? null),
                'granted_by' => $this->user->id,
                'created_at' => $this->date($grant['created_at'] ?? null) ?? $this->now,
                'updated_at' => $this->now,
            ]);
        }

        foreach ($this->list($table['session_notes'] ?? []) as $note) {
            $sessionId = $mapped('sessions', $note['play_session_id'] ?? null);
            $body = $this->string($note['body'] ?? null, 20000);

            if (! $sessionId || $body === null) {
                continue;
            }

            DB::table('session_notes')->insert([
                'play_session_id' => $sessionId,
                'user_id' => $this->user->id,
                'scene_id' => $mapped('scenes', $note['scene_id'] ?? null) ?: null,
                'body' => $this->links($body),
                'created_at' => $this->date($note['created_at'] ?? null) ?? $this->now,
                'updated_at' => $this->now,
            ]);
        }

        foreach ($this->list($table['character_notes'] ?? []) as $note) {
            $characterId = $character($note['character_id'] ?? null);
            $body = $this->string($note['body'] ?? null, 20000);
            $visibility = in_array($note['visibility'] ?? null, ['group', 'players', 'gm'], true) ? $note['visibility'] : null;

            if ($characterId === null || $body === null || $visibility === null) {
                continue;
            }

            $noteId = DB::table('character_notes')->insertGetId([
                'player_character_id' => $characterId,
                'user_id' => $this->user->id,
                'play_session_id' => $mapped('sessions', $note['play_session_id'] ?? null) ?: null,
                'visibility' => $visibility,
                'body' => $this->links($body),
                'created_at' => $this->date($note['created_at'] ?? null) ?? $this->now,
                'updated_at' => $this->now,
            ]);

            $shares = collect($this->array($note['shared_with'] ?? []))->map($character)->filter()->unique()
                ->map(fn (int $shared) => ['character_note_id' => $noteId, 'player_character_id' => $shared]);
            DB::table('character_note_shares')->insertOrIgnore($shares->all());
        }
    }

    private function date(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Valeurs de champs avec les identifiants de définitions recalculés ; les champs inconnus sont abandonnés.
     *
     * @return array<string, mixed>
     */
    private function fieldValues(mixed $values): array
    {
        $result = [];

        foreach (is_array($values) ? $values : [] as $key => $value) {
            $fieldId = $this->map['fields'][(int) $key] ?? null;

            if ($fieldId !== null && (is_scalar($value) || $value === null || is_array($value))) {
                $result[(string) $fieldId] = is_string($value) ? mb_substr($value, 0, 20000) : $value;
            }
        }

        return $result;
    }

    /**
     * Valeurs de champs qui désignent une fiche ou un document : recalculées une fois les fiches
     * et les documents créés. Un document absent de l'archive laisse le champ vide.
     */
    private function remapFieldValues(): void
    {
        $fields = array_filter($this->fieldTypes, fn (FieldType $type) => in_array($type, [FieldType::LongText, FieldType::EntityRef, FieldType::File], true));

        if ($fields === [] || $this->map['entities'] === []) {
            return;
        }

        foreach (DB::table('entities')->whereIn('id', array_values($this->map['entities']))->get(['id', 'field_values']) as $entity) {
            $values = json_decode((string) $entity->field_values, true) ?: [];
            $changed = false;

            foreach ($values as $key => $value) {
                $type = $fields[(int) $key] ?? null;

                if ($type === FieldType::File) {
                    $document = $this->map['documents'][is_numeric($value) ? (int) $value : -1] ?? null;
                    $values[$key] = $document;
                    $changed = true;
                } elseif ($type !== null && is_string($value)) {
                    $values[$key] = $this->links($value);
                    $changed = true;
                }
            }

            if ($changed) {
                DB::table('entities')->where('id', $entity->id)->update([
                    'field_values' => json_encode(array_filter($values, fn ($value) => $value !== null), JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT),
                ]);
            }
        }
    }

    /** Liens [[Nom|id]] vers les nouvelles fiches ; un lien vers une fiche absente garde seulement le nom. */
    private function links(?string $text, ?int $max = null): ?string
    {
        if ($text === null) {
            return null;
        }

        $text = (string) preg_replace_callback(EntityLinks::PATTERN, function (array $match) {
            $name = $match[1];

            if (! isset($match[2])) {
                return "[[{$name}]]";
            }

            $id = $this->map['entities'][(int) $match[2]] ?? null;

            return $id === null ? "[[{$name}]]" : "[[{$name}|{$id}]]";
        }, $text);

        // Un nouvel identifiant peut être plus long que l'ancien : la colonne garde sa limite.
        return $max === null ? $text : mb_substr($text, 0, $max);
    }

    /** @param mixed $tags anciens identifiants d'étiquettes */
    private function attachTags(string $table, string $column, int $id, mixed $tags): void
    {
        $rows = collect($this->ids($tags, 'tags'))->map(fn (int $tagId) => [$column => $id, 'tag_id' => $tagId]);
        DB::table($table)->insertOrIgnore($rows->all());
    }

    /**
     * Nouveaux identifiants des éléments cités, sans doublon ni élément inconnu.
     *
     * @return list<int>
     */
    private function ids(mixed $ids, string $kind): array
    {
        return collect(is_array($ids) ? $ids : [])
            ->map(fn ($id) => $this->map[$kind][$this->int($id) ?? -1] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function entity(mixed $id): ?int
    {
        return $this->map['entities'][$this->int($id) ?? -1] ?? null;
    }

    private function image(mixed $name, string $directory): ?string
    {
        return $this->extract($name, self::IMAGE_EXTENSIONS, $directory)['path'] ?? null;
    }

    /**
     * Copie un fichier de l'archive sur le disque, s'il a une extension permise et un contenu du type attendu.
     *
     * @param  list<string>  $extensions
     * @return array{path: string, mime: string, size: int}|null
     */
    private function extract(mixed $name, array $extensions, string $directory): ?array
    {
        if (! is_string($name) || ! preg_match('#^fichiers/\d+\.([a-z0-9]{1,8})$#', $name, $match) || ! in_array($match[1], $extensions, true)) {
            return null;
        }

        $content = $this->zip->getFromName($name);

        if ($content === false || $content === '') {
            return null;
        }

        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->buffer($content);
        $isImage = str_starts_with($mime, 'image/') && $mime !== 'image/svg+xml';
        $expected = match (true) {
            in_array($match[1], self::IMAGE_EXTENSIONS, true) => $isImage,
            $match[1] === 'pdf' => $mime === 'application/pdf',
            // Documents bureautiques et texte : jamais de HTML, SVG ou script déguisé.
            default => ! preg_match('#(html|javascript|svg)#', $mime)
                && (! str_contains($mime, 'xml') || in_array($match[1], ['docx', 'xlsx', 'odt', 'ods'], true)),
        };

        if (! $expected) {
            return null;
        }

        $path = trim($directory, '/').'/'.Str::random(40).'.'.$match[1];
        Storage::disk(Entity::FILES_DISK)->put($path, $content);
        $this->written[] = [Entity::FILES_DISK, $path];

        return ['path' => $path, 'mime' => $mime, 'size' => strlen($content)];
    }

    private function string(mixed $value, int $max): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : mb_substr($value, 0, $max);
    }

    private function int(mixed $value): ?int
    {
        return is_int($value) || (is_string($value) && ctype_digit($value)) ? (int) $value : null;
    }

    private function number(mixed $value, float $min, float $max): ?float
    {
        return is_numeric($value) ? max($min, min($max, (float) $value)) : null;
    }

    private function zone(mixed $value, Zone $default): string
    {
        return Zone::tryFrom((string) $value)?->value ?? $default->value;
    }

    /** @return array<string, mixed> */
    private function array(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /** @return list<array<string, mixed>> */
    private function list(mixed $value): array
    {
        return is_array($value) ? array_values(array_filter($value, 'is_array')) : [];
    }
}
