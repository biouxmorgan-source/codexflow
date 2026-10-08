<?php

namespace App\Support\Archive;

use App\Models\Campaign;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

/**
 * Archive complète d'une campagne (.zip) : son jeu (champs, règles, documents), son monde
 * (fiches, relations, documents) et son contenu préparé, avec les fichiers.
 *
 * Par défaut, comme pour la duplication, rien de ce qui appartient à la table n'est exporté : ni membres,
 * ni personnages joueurs et leurs fiches, ni connaissances, notes, messages, séances ou journal.
 *
 * Sauvegarde complète ($complete, réservée au propriétaire) : la table en plus, telle que le MJ la voit
 * dans l'application. Personnages et leurs fiches, ce qu'ils ont reçu, séances et notes, notes des
 * joueurs partagées (jamais les notes « Moi seul »), messages et journal. Des joueurs, seul le nom
 * figure, jamais l'adresse e-mail.
 *
 * Les identifiants du fichier sont ceux de la base d'origine ; ils ne servent qu'à relier
 * les éléments entre eux (et dans les liens [[Nom|id]]) et sont tous recalculés à l'import.
 */
final class CampaignExport
{
    public const FORMAT = 'codexflow-campaign';

    public const TEMPLATE_FORMAT = 'codexflow-template';

    public const VERSION = 1;

    private ZipArchive $zip;

    private int $files = 0;

    public function __construct(private Campaign $campaign, private bool $complete = false) {}

    /** Écrit l'archive dans un fichier temporaire et renvoie son chemin. */
    public function write(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'codexflow-export-');
        $this->zip = new ZipArchive;

        if ($this->zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Impossible de créer l’archive.');
        }

        $data = $this->data();
        $this->zip->addFromString('campagne.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        $this->zip->close();

        return $path;
    }

    /** @return array<string, mixed> */
    private function data(): array
    {
        $campaign = $this->campaign;
        $gameSystem = DB::table('game_systems')->find($campaign->game_system_id);
        $world = $campaign->world_id ? DB::table('worlds')->find($campaign->world_id) : null;

        // Fiches des personnages joueurs : elles restent à la table d'origine, sauf sauvegarde complète.
        $playerSheets = DB::table('player_characters')->where('campaign_id', $campaign->id)->pluck('entity_id')->all();

        $entities = DB::table('entities')
            ->where(fn ($q) => $q->where('campaign_id', $campaign->id)->when($world, fn ($q) => $q->orWhere('world_id', $world->id)))
            ->when(! $this->complete, fn ($q) => $q->whereNotIn('id', $playerSheets))
            ->orderBy('id')
            ->get();
        $entityIds = $entities->pluck('id')->all();

        $documents = DB::table('documents')
            ->where(fn ($q) => $q->where('campaign_id', $campaign->id)
                ->orWhere(fn ($q) => $q->where('game_system_id', $campaign->game_system_id)->whereNull('campaign_id')->whereNull('world_id'))
                ->when($world, fn ($q) => $q->orWhere('world_id', $world->id)))
            ->orderBy('id')
            ->get();
        $documentIds = $documents->pluck('id')->all();

        $rules = DB::table('rules')
            ->where(fn ($q) => $q->where('campaign_id', $campaign->id)
                ->orWhere(fn ($q) => $q->where('game_system_id', $campaign->game_system_id)->whereNull('campaign_id')))
            ->orderBy('id')
            ->get();
        $ruleIds = $rules->pluck('id')->all();

        $scenarios = DB::table('scenarios')->where('campaign_id', $campaign->id)->orderBy('position')->orderBy('id')->get();
        $scenes = DB::table('scenes')->whereIn('scenario_id', $scenarios->pluck('id'))->orderBy('position')->orderBy('id')->get();
        $sceneIds = $scenes->pluck('id')->all();

        $fields = DB::table('field_definitions')->where('game_system_id', $campaign->game_system_id)->orderBy('position')->orderBy('id')->get();

        $tagLinks = [
            'entities' => $this->pivot('entity_tag', 'entity_id', $entityIds, 'tag_id'),
            'documents' => $this->pivot('document_tag', 'document_id', $documentIds, 'tag_id'),
            'rules' => $this->pivot('rule_tag', 'rule_id', $ruleIds, 'tag_id'),
            'scenes' => $this->pivot('scene_tag', 'scene_id', $sceneIds, 'tag_id'),
        ];
        $tagIds = collect($tagLinks)->flatMap(fn (Collection $links) => $links->flatten())->unique()->all();

        $typeIds = $entities->pluck('entity_type_id')->merge($fields->pluck('entity_type_id'))->filter()->unique()->all();

        $relations = DB::table('entity_relations')
            ->where(fn ($q) => $q->whereNull('campaign_id')->orWhere('campaign_id', $campaign->id))
            ->whereIn('from_entity_id', $entityIds)
            ->whereIn('to_entity_id', $entityIds)
            ->orderBy('id')
            ->get();

        $documentEntities = $this->pivot('document_entity', 'document_id', $documentIds, 'entity_id');
        $documentRules = $this->pivot('document_rule', 'document_id', $documentIds, 'rule_id');

        $secrets = DB::table('secrets')->where('campaign_id', $campaign->id)->orderBy('id')->get();
        $secretIds = $secrets->pluck('id')->all();
        $secretLinks = [
            'entities' => $this->pivot('entity_secret', 'secret_id', $secretIds, 'entity_id'),
            'documents' => $this->pivot('document_secret', 'secret_id', $secretIds, 'document_id'),
            'scenes' => $this->pivot('scene_secret', 'secret_id', $secretIds, 'scene_id'),
        ];

        $maps = DB::table('table_maps')->where('campaign_id', $campaign->id)->whereIn('document_id', $documentIds)->orderBy('id')->get();
        $tokens = DB::table('map_tokens')->whereIn('table_map_id', $maps->pluck('id'))->orderBy('id')->get()->groupBy('table_map_id');

        $sceneEntities = DB::table('scene_entity')->whereIn('scene_id', $sceneIds)->whereIn('entity_id', $entityIds)->orderBy('position')->get()->groupBy('scene_id');
        $sceneDocuments = DB::table('document_scene')->whereIn('scene_id', $sceneIds)->whereIn('document_id', $documentIds)->orderBy('position')->get()->groupBy('scene_id');
        $sceneRules = DB::table('rule_scene')->whereIn('scene_id', $sceneIds)->whereIn('rule_id', $ruleIds)->orderBy('position')->get()->groupBy('scene_id');

        return [
            'format' => self::FORMAT,
            'version' => self::VERSION,
            'app_version' => config('codexflow.version'),
            'exported_at' => now()->toIso8601String(),
            'game_system' => [
                'name' => $gameSystem->name,
                'description' => $gameSystem->description,
                'image' => $this->file('local', $gameSystem->image_path),
            ],
            'world' => $world ? [
                'name' => $world->name,
                'description' => $world->description,
                'image' => $this->file('local', $world->image_path),
            ] : null,
            'campaign' => ['name' => $campaign->name, 'description' => $campaign->description, 'table_theme' => $campaign->table_theme, 'exchanges_need_approval' => $campaign->exchanges_need_approval],
            'entity_types' => DB::table('entity_types')->whereIn('id', $typeIds)->orderBy('id')->get(['id', 'key', 'name'])
                ->map(fn ($type) => (array) $type)->all(),
            'tags' => DB::table('tags')->whereIn('id', $tagIds)->orderBy('id')->get(['id', 'name', 'color'])
                ->map(fn ($tag) => (array) $tag)->all(),
            'field_definitions' => $fields->map(fn ($field) => [
                'id' => $field->id,
                'entity_type_id' => $field->entity_type_id,
                'group' => $field->group,
                'name' => $field->name,
                'type' => $field->type,
                'options' => json_decode((string) $field->options, true),
                'zone' => $field->zone,
                'position' => $field->position,
                'player_editable' => (bool) $field->player_editable,
            ])->all(),
            'entities' => $entities->map(fn ($entity) => [
                'id' => $entity->id,
                'in_world' => $entity->world_id !== null,
                'entity_type_id' => $entity->entity_type_id,
                'name' => $entity->name,
                'summary' => $entity->summary,
                'description' => $entity->description,
                'gm_notes' => $entity->gm_notes,
                'field_values' => json_decode((string) $entity->field_values, true) ?: [],
                'image' => $this->file('local', $entity->image_path),
                'tags' => $tagLinks['entities']->get($entity->id, collect())->all(),
                'attachments' => DB::table('attachments')->where('entity_id', $entity->id)->orderBy('id')->get()
                    ->map(fn ($file) => [
                        'zone' => $file->zone,
                        'original_name' => $file->original_name,
                        'file' => $this->file($file->disk, $file->path),
                    ])
                    ->filter(fn (array $file) => $file['file'] !== null)
                    ->values()->all(),
            ])->all(),
            'relations' => $relations->map(fn ($relation) => [
                'from' => $relation->from_entity_id,
                'to' => $relation->to_entity_id,
                'in_world' => $relation->campaign_id === null,
                'label' => $relation->label,
                'reverse_label' => $relation->reverse_label,
                'zone' => $relation->zone,
            ])->all(),
            'entity_states' => DB::table('campaign_entity_states')->where('campaign_id', $campaign->id)->whereIn('entity_id', $entityIds)->orderBy('id')->get()
                ->map(fn ($state) => [
                    'entity_id' => $state->entity_id,
                    'status' => $state->status,
                    'gm_notes' => $state->gm_notes,
                    'overrides' => json_decode((string) $state->overrides, true) ?: [],
                ])->all(),
            'documents' => $documents->map(fn ($document) => [
                'id' => $document->id,
                'scope' => $document->campaign_id ? 'campaign' : ($document->world_id ? 'world' : 'game_system'),
                'title' => $document->title,
                'description' => $document->description,
                'zone' => $document->zone,
                'original_name' => $document->original_name,
                'file' => $this->file($document->disk, $document->path),
                'tags' => $tagLinks['documents']->get($document->id, collect())->all(),
                'entities' => $documentEntities->get($document->id, collect())->all(),
                'rules' => $documentRules->get($document->id, collect())->all(),
            ])->filter(fn (array $document) => $document['file'] !== null)->values()->all(),
            'rules' => $rules->map(fn ($rule) => [
                'id' => $rule->id,
                'in_campaign' => $rule->campaign_id !== null,
                ...collect((array) $rule)->only(['title', 'category', 'summary', 'procedure', 'gm_notes', 'source', 'origin', 'status', 'zone'])
                    // Un modèle part chez d'autres MJ : les liens gardent le nom, pas l'identifiant d'une fiche d'ici.
                    ->map(fn ($value) => is_string($value) ? preg_replace('/\[\[([^\[\]|\n]+?)\|\d+\]\]/u', '[[$1]]', $value) : $value)
                    ->all(),
                'tags' => $tagLinks['rules']->get($rule->id, collect())->all(),
            ])->all(),
            'scenarios' => $scenarios->map(fn ($scenario) => [
                'name' => $scenario->name,
                'summary' => $scenario->summary,
                'scenes' => $scenes->where('scenario_id', $scenario->id)->values()->map(fn ($scene) => [
                    'id' => $scene->id,
                    'chapter' => $scene->chapter,
                    'name' => $scene->name,
                    'description' => $scene->description,
                    'status' => $scene->status,
                    'tags' => $tagLinks['scenes']->get($scene->id, collect())->all(),
                    'entities' => $sceneEntities->get($scene->id, collect())->map(fn ($row) => ['id' => $row->entity_id, 'note' => $row->note])->values()->all(),
                    'documents' => $sceneDocuments->get($scene->id, collect())->pluck('document_id')->all(),
                    'rules' => $sceneRules->get($scene->id, collect())->pluck('rule_id')->all(),
                ])->all(),
            ])->all(),
            'pins' => DB::table('campaign_pins')->where('campaign_id', $campaign->id)->whereIn('entity_id', $entityIds)->orderBy('position')->pluck('entity_id')->all(),
            'to_play' => DB::table('to_play_items')->where('campaign_id', $campaign->id)->whereNull('done_at')->whereNull('player_character_id')
                ->orderBy('position')->orderBy('id')->get()
                ->map(fn ($item) => ['body' => $item->body, 'scene_id' => $item->scene_id, 'rule_id' => $item->rule_id])->all(),
            'secrets' => $secrets->map(fn ($secret) => [
                'id' => $secret->id,
                'title' => $secret->title,
                'body' => $secret->body,
                'kind' => $secret->kind,
                'entities' => $secretLinks['entities']->get($secret->id, collect())->all(),
                'documents' => $secretLinks['documents']->get($secret->id, collect())->all(),
                'scenes' => $secretLinks['scenes']->get($secret->id, collect())->all(),
            ])->all(),
            'maps' => $maps->map(fn ($map) => [
                ...collect((array) $map)->only(['document_id', 'name', 'width', 'height', 'grid_enabled', 'grid_size', 'grid_offset_x', 'grid_offset_y', 'grid_color', 'scale_value', 'scale_unit'])->all(),
                'tokens' => $tokens->get($map->id, collect())->map(fn ($token) => collect((array) $token)->only(['entity_id', 'label', 'color', 'x', 'y', 'size', 'hidden', 'show_label'])->all())->values()->all(),
            ])->all(),
            'timeline' => DB::table('timeline_events')->where('campaign_id', $campaign->id)->orderBy('position')->orderBy('id')->get()
                ->map(fn ($event) => collect((array) $event)->only(['kind', 'date_label', 'title', 'description', 'zone', 'scene_id', ...($this->complete ? ['play_session_id'] : [])])->all())->all(),
            ...($this->complete ? ['table' => $this->table($playerSheets)] : []),
        ];
    }

    /**
     * La table, pour une sauvegarde complète : ce que le MJ en voit dans l'application.
     *
     * @param  list<int>  $playerSheets
     * @return array<string, mixed>
     */
    private function table(array $playerSheets): array
    {
        $campaign = $this->campaign;
        $names = fn (iterable $ids) => DB::table('users')->whereIn('id', collect($ids)->filter()->unique())->pluck('name', 'id');

        $characters = DB::table('player_characters')->where('campaign_id', $campaign->id)->orderBy('id')->get();
        $characterIds = $characters->pluck('id')->all();
        $sessions = DB::table('play_sessions')->where('campaign_id', $campaign->id)->orderBy('number')->get();
        $notes = DB::table('character_notes')->whereIn('player_character_id', $characterIds)->where('visibility', '!=', 'private')->orderBy('id')->get();
        $shares = $this->pivot('character_note_shares', 'character_note_id', $notes->pluck('id')->all(), 'player_character_id');
        $messages = DB::table('messages')->where('campaign_id', $campaign->id)->orderBy('id')->get();
        $journal = DB::table('activity_logs')->where('campaign_id', $campaign->id)->orderBy('id')->get();
        $people = $names($characters->pluck('user_id')->merge($messages->pluck('sender_id'))->merge($notes->pluck('user_id'))->merge($journal->pluck('user_id')));

        return [
            'player_sheets' => $playerSheets,
            'characters' => $characters->map(fn ($character) => [
                'id' => $character->id,
                'entity_id' => $character->entity_id,
                'player' => $people[$character->user_id] ?? null,
                'is_active' => (bool) $character->is_active,
                'locked' => (bool) $character->locked,
                'sheet' => $this->file('local', $character->sheet_path),
                'sheet_name' => $character->sheet_name,
            ])->all(),
            'grants' => DB::table('character_grants')->whereIn('player_character_id', $characterIds)->orderBy('id')->get()
                ->map(fn ($grant) => [
                    'character_id' => $grant->player_character_id,
                    ...collect((array) $grant)->only(['kind', 'entity_id', 'document_id', 'rule_id', 'secret_id', 'play_session_id', 'scene_id', 'title', 'body', 'quantity', 'added_by_player', 'validated_at', 'created_at'])->all(),
                ])->all(),
            'sessions' => $sessions->map(fn ($session) => collect((array) $session)->only(['id', 'number', 'title', 'started_at', 'ended_at'])->all())->all(),
            'session_notes' => DB::table('session_notes')->whereIn('play_session_id', $sessions->pluck('id'))->orderBy('id')->get()
                ->map(fn ($note) => collect((array) $note)->only(['play_session_id', 'scene_id', 'body', 'created_at'])->all())->all(),
            'character_notes' => $notes->map(fn ($note) => [
                'character_id' => $note->player_character_id,
                'author' => $people[$note->user_id] ?? null,
                'shared_with' => $shares->get($note->id, collect())->all(),
                ...collect((array) $note)->only(['play_session_id', 'visibility', 'body', 'created_at'])->all(),
            ])->all(),
            'messages' => $messages->map(fn ($message) => [
                'sender' => $people[$message->sender_id] ?? null,
                ...collect((array) $message)->only(['player_character_id', 'sender_character_id', 'body', 'entity_id', 'document_id', 'rule_id', 'created_at'])->all(),
            ])->all(),
            'journal' => $journal->map(fn ($entry) => [
                'user' => $people[$entry->user_id] ?? null,
                ...collect((array) $entry)->only(['event', 'subject_type', 'subject_id', 'subject_label', 'created_at'])->all(),
            ])->all(),
        ];
    }

    /**
     * Modèle de jeu (.json, sans fichier) : la structure à partager, sans contenu de campagne.
     * Types de fiche, champs, étiquettes utilisées dans la campagne et, au choix, les règles du jeu.
     *
     * @return array<string, mixed>
     */
    public static function template(Campaign $campaign, bool $withRules): array
    {
        $fields = DB::table('field_definitions')->where('game_system_id', $campaign->game_system_id)->orderBy('position')->orderBy('id')->get();
        $rules = $withRules
            ? DB::table('rules')->where('game_system_id', $campaign->game_system_id)->whereNull('campaign_id')->orderBy('id')->get()
            : collect();

        // Étiquettes de la campagne et des règles exportées : jamais celles des autres campagnes.
        $entityIds = DB::table('entities')->where('campaign_id', $campaign->id)->when($campaign->world_id, fn ($q) => $q->orWhere('world_id', $campaign->world_id))->pluck('id');
        $tagIds = DB::table('entity_tag')->whereIn('entity_id', $entityIds)->pluck('tag_id')
            ->merge(DB::table('rule_tag')->whereIn('rule_id', DB::table('rules')->where('campaign_id', $campaign->id)->pluck('id')->merge($rules->pluck('id')))->pluck('tag_id'))
            ->merge(DB::table('scene_tag')->whereIn('scene_id', DB::table('scenes')->whereIn('scenario_id', DB::table('scenarios')->where('campaign_id', $campaign->id)->select('id'))->pluck('id'))->pluck('tag_id'))
            ->unique();
        $ruleTags = DB::table('rule_tag')->whereIn('rule_id', $rules->pluck('id'))->get()->groupBy('rule_id');

        return [
            'format' => self::TEMPLATE_FORMAT,
            'version' => self::VERSION,
            'app_version' => config('codexflow.version'),
            'name' => DB::table('game_systems')->where('id', $campaign->game_system_id)->value('name'),
            'entity_types' => DB::table('entity_types')
                ->whereIn('id', $fields->pluck('entity_type_id')->filter())
                ->orWhere('user_id', $campaign->user_id)
                ->orderBy('id')->get(['id', 'key', 'name'])->map(fn ($type) => (array) $type)->all(),
            'tags' => DB::table('tags')->whereIn('id', $tagIds)->orderBy('id')->get(['id', 'name', 'color'])->map(fn ($tag) => (array) $tag)->all(),
            'field_definitions' => $fields->map(fn ($field) => [
                'id' => $field->id,
                'entity_type_id' => $field->entity_type_id,
                'group' => $field->group,
                'name' => $field->name,
                'type' => $field->type,
                'options' => json_decode((string) $field->options, true),
                'zone' => $field->zone,
                'position' => $field->position,
                'player_editable' => (bool) $field->player_editable,
            ])->all(),
            'rules' => $rules->map(fn ($rule) => [
                'id' => $rule->id,
                ...collect((array) $rule)->only(['title', 'category', 'summary', 'procedure', 'gm_notes', 'source', 'origin', 'status', 'zone'])
                    // Un modèle part chez d'autres MJ : les liens gardent le nom, pas l'identifiant d'une fiche d'ici.
                    ->map(fn ($value) => is_string($value) ? preg_replace('/\[\[([^\[\]|\n]+?)\|\d+\]\]/u', '[[$1]]', $value) : $value)
                    ->all(),
                'tags' => $ruleTags->get($rule->id, collect())->pluck('tag_id')->all(),
            ])->all(),
        ];
    }

    /**
     * Lignes d'une table pivot, regroupées par propriétaire.
     *
     * @param  list<int>  $ids
     * @return Collection<int, Collection<int, int>>
     */
    private function pivot(string $table, string $owner, array $ids, string $other): Collection
    {
        return DB::table($table)->whereIn($owner, $ids)->orderBy($other)->get([$owner, $other])
            ->groupBy($owner)
            ->map(fn (Collection $rows) => $rows->pluck($other)->values());
    }

    /** Ajoute un fichier à l'archive et renvoie son nom dans l'archive (null s'il n'existe plus). */
    private function file(string $disk, ?string $path): ?string
    {
        if ($path === null || ! Storage::disk($disk)->exists($path)) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $name = 'fichiers/'.(++$this->files).($extension !== '' ? '.'.$extension : '');
        config("filesystems.disks.{$disk}.driver") === 'local'
            ? $this->zip->addFile(Storage::disk($disk)->path($path), $name)
            : $this->zip->addFromString($name, (string) Storage::disk($disk)->get($path));

        return $name;
    }
}
