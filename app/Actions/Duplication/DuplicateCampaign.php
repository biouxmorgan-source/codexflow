<?php

namespace App\Actions\Duplication;

use App\Enums\CampaignRole;
use App\Enums\CampaignStatus;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\Rule;
use App\Models\Secret;
use App\Models\TimelineEvent;
use App\Models\ToPlayItem;
use App\Models\User;
use App\Support\TableTheme;
use Illuminate\Support\Facades\DB;

/**
 * Duplique une campagne pour rejouer son contenu avec une autre table : « Nom (copie) »,
 * même propriétaire, même jeu et même monde (partagés, pas copiés : les fiches du monde sont réutilisées).
 *
 * Copié : le contenu préparé propre à la campagne — fiches de campagne (sauf celles des personnages
 * joueurs), leurs relations et liens, états de campagne des fiches du monde, scénarios et scènes
 * (statuts remis à « Prévue »), documents (fichiers copiés) et règles de campagne avec leurs liens,
 * secrets et leurs liens (sans les révélations), chronologie du monde et événements prévus,
 * épingles, cartes et leurs jetons, et éléments « À jouer » du MJ non encore joués.
 *
 * Non copié : les membres autres que le propriétaire, les invitations, les personnages joueurs et
 * tout ce qui leur appartient (connaissances, possessions, notes), les séances et leurs notes,
 * les événements « joués » de la chronologie,
 * les messages, les notifications, le journal, l'écran de table et la progression des scènes.
 *
 * Le journal n'est pas alimenté : la copie démarre avec un historique vierge, et des centaines
 * d'entrées « créé » n'apprendraient rien au MJ.
 */
class DuplicateCampaign
{
    public function __construct(
        private DuplicateEntity $entities,
        private DuplicateScenario $scenarios,
    ) {}

    public function handle(Campaign $campaign, User $owner): Campaign
    {
        return FileCopies::run(fn (FileCopies $files) => ActivityLog::muted(fn () => DB::transaction(
            fn () => $this->copy($campaign, $owner, $files)
        )));
    }

    private function copy(Campaign $source, User $owner, FileCopies $files): Campaign
    {
        $campaign = new Campaign([
            'name' => DuplicateEntity::copyName($source->name),
            'description' => $source->description,
            'status' => CampaignStatus::Active,
        ]);
        $campaign->owner()->associate($owner);
        $campaign->gameSystem()->associate($source->game_system_id);
        $campaign->world()->associate($source->world_id);
        $campaign->table_theme = $source->table_theme ?? TableTheme::DEFAULT;
        $campaign->exchanges_need_approval = $source->exchanges_need_approval ?? true;
        $campaign->save();
        $campaign->members()->attach($owner, ['role' => CampaignRole::GameMaster->value]);

        // Correspondances ancien id => nouvel id. Ce qui est partagé (monde, jeu) garde son id ;
        // ce qui est propre à la campagne et n'a pas été copié n'y figure pas : ses liens sont abandonnés.
        $entities = $this->copyEntities($source, $campaign, $files);
        $documents = $this->copyDocuments($source, $campaign, $files);
        $rules = $this->copyRules($source, $campaign);

        $this->copyLinks($source, $entities, $documents, $rules);
        $secrets = $this->copySecrets($source, $campaign, $owner, $entities, $documents);

        foreach ($source->entityStates()->get() as $state) {
            $copy = $state->replicate();
            $copy->setRelations([]);
            $copy->campaign_id = $campaign->id;
            $copy->save();
        }

        $scenes = [];

        foreach ($source->scenarios()->get() as $scenario) {
            [, $map] = $this->scenarios->copyInto($scenario, $campaign, $scenario->name, $scenario->position, $entities, $documents, $rules, $secrets);
            $scenes += $map;
        }

        $this->copyTimeline($source, $campaign, $owner, $scenes);

        $this->copyMaps($source, $campaign, $entities, $documents);

        foreach ($source->pins()->get() as $pin) {
            if (isset($entities[$pin->id])) {
                $campaign->pins()->attach($entities[$pin->id], ['position' => $pin->pivot->position]);
            }
        }

        // « À jouer » sans scène (ceux des scènes ont suivi leur scénario).
        $items = $source->toPlayItems()->pending()->whereNull('scene_id')->whereNull('player_character_id')->get();

        foreach ($items as $item) {
            if ($item->rule_id !== null && ! isset($rules[$item->rule_id])) {
                continue;
            }

            $copy = new ToPlayItem($item->only(['body', 'position']));
            $copy->campaign()->associate($campaign);
            $copy->rule_id = $item->rule_id === null ? null : $rules[$item->rule_id];
            $copy->save();
        }

        return $campaign;
    }

    /** @return array<int, int> */
    private function copyEntities(Campaign $source, Campaign $campaign, FileCopies $files): array
    {
        $map = $source->world_id === null ? [] : Entity::where('world_id', $source->world_id)->pluck('id', 'id')->all();

        // Les fiches des personnages joueurs appartiennent à la table d'origine.
        $local = $source->localEntities()
            ->whereNotIn('id', $source->playerCharacters()->select('entity_id'))
            ->orderBy('id')
            ->get();

        foreach ($local as $entity) {
            $map[$entity->id] = $this->entities->copySheet($entity, $files, $entity->name, $campaign)->id;
        }

        foreach (EntityRelation::where('campaign_id', $source->id)->get() as $relation) {
            if (isset($map[$relation->from_entity_id], $map[$relation->to_entity_id])) {
                $this->entities->copyRelation($relation, $map[$relation->from_entity_id], $map[$relation->to_entity_id], $campaign->id);
            }
        }

        return $map;
    }

    /**
     * Secrets et leurs liens vers les fiches et documents copiés ; personne ne les connaît encore.
     *
     * @param  array<int, int>  $entities
     * @param  array<int, int>  $documents
     * @return array<int, int>
     */
    private function copySecrets(Campaign $source, Campaign $campaign, User $owner, array $entities, array $documents): array
    {
        $map = [];

        foreach ($source->secrets()->with(['entities', 'documents'])->orderBy('id')->get() as $secret) {
            $copy = new Secret($secret->only(['title', 'body']));
            $copy->campaign()->associate($campaign);
            $copy->owner()->associate($owner);
            $copy->save();

            $copy->entities()->attach(array_values(array_filter(array_map(fn (int $id) => $entities[$id] ?? null, $secret->entities->modelKeys()))));
            $copy->documents()->attach(array_values(array_filter(array_map(fn (int $id) => $documents[$id] ?? null, $secret->documents->modelKeys()))));

            $map[$secret->id] = $copy->id;
        }

        return $map;
    }

    /**
     * Chronologie du monde et événements prévus ; les événements joués racontent la table d'origine.
     *
     * @param  array<int, int>  $scenes
     */
    private function copyTimeline(Campaign $source, Campaign $campaign, User $owner, array $scenes): void
    {
        foreach ($source->timelineEvents()->where('kind', '!=', 'played')->orderBy('position')->get() as $event) {
            $copy = new TimelineEvent($event->only(['kind', 'date_label', 'title', 'description', 'zone']));
            $copy->campaign()->associate($campaign);
            $copy->user_id = $owner->id;
            $copy->scene_id = $event->scene_id === null ? null : ($scenes[$event->scene_id] ?? null);
            $copy->position = $event->position;
            $copy->save();
        }
    }

    /** @return array<int, int> */
    private function copyDocuments(Campaign $source, Campaign $campaign, FileCopies $files): array
    {
        $map = Document::query()->availableIn($source)->whereNull('campaign_id')->pluck('id', 'id')->all();

        foreach ($source->documents()->with('tags')->get() as $document) {
            $path = $files->copy($document->disk, $document->path, 'documents');

            if ($path === null) {
                continue;
            }

            $copy = $document->replicate();
            $copy->setRelations([]);
            $copy->path = $path;
            $copy->campaign_id = $campaign->id;
            $copy->save();
            $copy->tags()->sync($document->tags->modelKeys());

            $map[$document->id] = $copy->id;
        }

        return $map;
    }

    /**
     * Cartes et jetons préparés, sans la vue ni la règle du moment. Les jetons des personnages
     * joueurs restent à la table d'origine.
     *
     * @param  array<int, int>  $entities
     * @param  array<int, int>  $documents
     */
    private function copyMaps(Campaign $source, Campaign $campaign, array $entities, array $documents): void
    {
        foreach ($source->maps()->with('tokens')->get() as $map) {
            if (! isset($documents[$map->document_id])) {
                continue;
            }

            $copy = $map->replicate(['view_x', 'view_y', 'view_zoom', 'ruler']);
            $copy->setRelations([]);
            $copy->campaign_id = $campaign->id;
            $copy->document_id = $documents[$map->document_id];
            $copy->save();

            foreach ($map->tokens as $token) {
                if ($token->entity_id !== null && ! isset($entities[$token->entity_id])) {
                    continue;
                }

                $tokenCopy = $token->replicate();
                $tokenCopy->setRelations([]);
                $tokenCopy->table_map_id = $copy->id;
                $tokenCopy->entity_id = $token->entity_id === null ? null : $entities[$token->entity_id];
                $tokenCopy->save();
            }
        }
    }

    /** @return array<int, int> */
    private function copyRules(Campaign $source, Campaign $campaign): array
    {
        $map = Rule::where('game_system_id', $source->game_system_id)->pluck('id', 'id')->all();

        foreach ($source->rules()->with('tags')->get() as $rule) {
            $copy = $rule->replicate();
            $copy->setRelations([]);
            $copy->campaign_id = $campaign->id;
            $copy->save();
            $copy->tags()->sync($rule->tags->modelKeys());

            $map[$rule->id] = $copy->id;
        }

        return $map;
    }

    /**
     * Liens fiche-document et règle-document dont au moins une extrémité est propre à la campagne
     * (les liens entre éléments partagés existent déjà pour toutes les campagnes).
     *
     * @param  array<int, int>  $entities
     * @param  array<int, int>  $documents
     * @param  array<int, int>  $rules
     */
    private function copyLinks(Campaign $source, array $entities, array $documents, array $rules): void
    {
        $localEntities = $source->localEntities()->select('id');
        $localDocuments = $source->documents()->select('id');
        $localRules = $source->rules()->select('id');

        $pairs = [
            ['document_entity', 'entity_id', $entities, $localEntities],
            ['document_rule', 'rule_id', $rules, $localRules],
        ];

        foreach ($pairs as [$table, $column, $map, $localOwners]) {
            $rows = DB::table($table)
                ->where(fn ($q) => $q->whereIn($column, $localOwners)->orWhereIn('document_id', $localDocuments))
                ->get();

            $insert = [];

            foreach ($rows as $row) {
                if (isset($map[$row->{$column}], $documents[$row->document_id])) {
                    $insert[] = [
                        $column => $map[$row->{$column}],
                        'document_id' => $documents[$row->document_id],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            DB::table($table)->insertOrIgnore($insert);
        }
    }
}
