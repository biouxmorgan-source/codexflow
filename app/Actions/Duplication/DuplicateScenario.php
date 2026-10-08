<?php

namespace App\Actions\Duplication;

use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Scenario;
use App\Models\Scene;
use App\Models\ToPlayItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Duplique un scénario et ses scènes dans sa campagne : « Nom (copie) », placé en dernier.
 *
 * Copié : chapitre, nom, description et ordre des scènes, fiches liées (avec leur précision),
 * documents, règles et secrets liés, éléments « À jouer » du MJ non encore joués rattachés aux scènes.
 * Les statuts repartent de « Prévue » : la progression reste propre à l'original.
 */
class DuplicateScenario
{
    public function handle(Scenario $scenario): Scenario
    {
        return ActivityLog::batch(fn () => DB::transaction(fn () => $this->copyInto(
            $scenario,
            $scenario->campaign,
            DuplicateEntity::copyName($scenario->name, fn (string $name) => $scenario->campaign->scenarios()->where('name', $name)->exists()),
            (int) $scenario->campaign->scenarios()->max('position') + 1,
        )[0]));
    }

    /**
     * Copie le scénario dans $campaign. Les tables de correspondance (ancien id => nouvel id)
     * servent à la duplication d'une campagne : null garde les mêmes fiches, documents et règles ;
     * un tableau remplace chaque id et abandonne le lien vers ce qui n'a pas été copié.
     *
     * @param  array<int, int>|null  $entities
     * @param  array<int, int>|null  $documents
     * @param  array<int, int>|null  $rules
     * @param  array<int, int>|null  $secrets
     * @return array{0: Scenario, 1: array<int, int>} le scénario copié et la correspondance des scènes
     */
    public function copyInto(Scenario $scenario, Campaign $campaign, string $name, int $position, ?array $entities = null, ?array $documents = null, ?array $rules = null, ?array $secrets = null): array
    {
        $copy = new Scenario(['name' => $name, 'summary' => $scenario->summary, 'position' => $position]);
        $campaign->scenarios()->save($copy);

        $scenes = [];

        foreach ($scenario->scenes()->with(['entities', 'documents', 'rules', 'secrets', 'tags'])->get() as $scene) {
            // Statut absent : la nouvelle scène prend le statut initial (« Prévue »).
            $duplicate = new Scene($scene->only(['chapter', 'name', 'description', 'position']));
            $copy->scenes()->save($duplicate);
            $scenes[$scene->id] = $duplicate->id;

            $duplicate->entities()->attach(self::remap($scene->entities, $entities, fn ($entity) => [
                'note' => $entity->pivot->note,
                'position' => $entity->pivot->position,
            ]));
            $duplicate->documents()->attach(self::remap($scene->documents, $documents, fn ($document) => ['position' => $document->pivot->position]));
            $duplicate->rules()->attach(self::remap($scene->rules, $rules, fn ($rule) => ['position' => $rule->pivot->position]));
            $duplicate->secrets()->attach(array_keys(self::remap($scene->secrets, $secrets, fn () => [])));
            $duplicate->tags()->attach($scene->tags->modelKeys());
        }

        // « À jouer » du MJ encore en attente ; les intentions des joueurs restent à leur table.
        $items = ToPlayItem::whereIn('scene_id', array_keys($scenes))->pending()->whereNull('player_character_id')->get();

        foreach ($items as $item) {
            $ruleId = $item->rule_id === null ? null : ($rules === null ? $item->rule_id : ($rules[$item->rule_id] ?? false));

            if ($ruleId === false) {
                continue;
            }

            $duplicate = new ToPlayItem($item->only(['body', 'position']));
            $duplicate->campaign()->associate($campaign);
            $duplicate->scene_id = $scenes[$item->scene_id];
            $duplicate->rule_id = $ruleId;
            $duplicate->save();
        }

        return [$copy, $scenes];
    }

    /**
     * @param  iterable<Model>  $models
     * @param  array<int, int>|null  $map
     * @return array<int, array<string, mixed>> [nouvel id => colonnes du pivot]
     */
    private static function remap(iterable $models, ?array $map, callable $pivot): array
    {
        $attach = [];

        foreach ($models as $model) {
            $id = $map === null ? $model->getKey() : ($map[$model->getKey()] ?? null);

            if ($id !== null) {
                $attach[$id] = $pivot($model);
            }
        }

        return $attach;
    }
}
