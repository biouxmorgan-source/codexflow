<?php

namespace App\Actions\Imports;

use App\Enums\SceneStatus;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\Rule;
use App\Models\Scenario;
use App\Models\Scene;
use App\Support\Import\Normalize;
use App\Support\Import\TabularFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Import de scénarios et de leurs scènes (une ligne par scène). Colonnes reconnues :
 * Scénario, Résumé du scénario, Chapitre, Scène, Description, Statut, Fiches, Documents, Règles.
 * Les fiches, documents et règles sont cherchés par nom parmi ceux utilisables dans la campagne.
 */
class ImportScenes
{
    public const COLUMNS = [
        'scenario' => ['scenario', 'scenarios', 'aventure'],
        'scenario_summary' => ['resumeduscenario', 'resumescenario'],
        'chapter' => ['chapitre', 'partie', 'acte', 'chapter'],
        'name' => ['scene', 'nom', 'titre', 'name'],
        'description' => ['description', 'deroulement', 'texte', 'contenu'],
        'status' => ['statut', 'status', 'etat'],
        'entities' => ['fiches', 'fiche', 'entites', 'pnj', 'liens'],
        'documents' => ['documents', 'document', 'aidesdejeu', 'handouts'],
        'rules' => ['regles', 'regle', 'rules'],
    ];

    private const LIMITS = ['scenario' => 255, 'scenario_summary' => 5000, 'chapter' => 100, 'name' => 255, 'description' => 20000];

    /** Liens au-delà de cette limite ignorés, comme dans le formulaire de scène. */
    private const MAX_LINKS = 100;

    /** @var array<string, int> */
    private array $columns = [];

    public function __construct(
        private readonly Campaign $campaign,
        private readonly TabularFile $table,
        private readonly bool $updateExisting = true,
    ) {
        foreach ($table->headers as $index => $header) {
            foreach (self::COLUMNS as $target => $aliases) {
                if (! isset($this->columns[$target]) && in_array(Normalize::key($header), $aliases, true)) {
                    $this->columns[$target] = $index;
                }
            }
        }
    }

    /**
     * @return array{
     *     errors: list<string>,
     *     rows: list<array{line: int, name: string, scenario: string, links: int, action: string, errors: list<string>, warnings: list<string>}>,
     *     valid: int,
     * }
     */
    public function plan(): array
    {
        return $this->build()['plan'];
    }

    /**
     * @return array{created: int, updated: int, scenarios: int}
     */
    public function run(): array
    {
        $build = $this->build();

        return DB::transaction(function () use ($build) {
            $scenarios = $this->campaign->scenarios()->get()->keyBy(fn (Scenario $scenario) => mb_strtolower($scenario->name));
            $scenarioPosition = (int) $this->campaign->scenarios()->max('position');
            $newScenarios = 0;
            $created = 0;
            $updated = 0;

            foreach ($build['valid'] as $row) {
                $scenario = $scenarios->get(mb_strtolower($row['scenario']));

                if ($scenario === null) {
                    $scenario = $this->campaign->scenarios()->create([
                        'name' => $row['scenario'],
                        'summary' => $row['scenario_summary'],
                        'position' => ++$scenarioPosition,
                    ]);
                    $scenarios->put(mb_strtolower($scenario->name), $scenario);
                    $newScenarios++;
                } elseif ($row['scenario_summary'] !== null && $this->updateExisting) {
                    $scenario->update(['summary' => $row['scenario_summary']]);
                }

                $scene = $this->updateExisting
                    ? $scenario->scenes()->whereRaw('lower(name) = ?', [mb_strtolower($row['name'])])->first()
                    : null;

                if ($scene) {
                    // Une case vide ne remplace pas la valeur d'une scène existante.
                    $scene->fill(array_filter($row['attributes'], fn ($value) => $value !== null))->save();
                    $updated++;
                } else {
                    $scene = new Scene($row['attributes'] + ['name' => $row['name']]);
                    $scene->status ??= SceneStatus::Planned;
                    $scene->scenario_id = $scenario->id;
                    $scene->position = (int) Scene::where('scenario_id', $scenario->id)->max('position') + 1;
                    $scene->save();
                    $created++;
                }

                foreach (['entities', 'documents', 'rules'] as $relation) {
                    if ($row[$relation] !== null) {
                        $scene->{$relation}()->sync(collect($row[$relation])
                            ->values()
                            ->mapWithKeys(fn (int $id, int $position) => [$id => ['position' => $position]])
                            ->all());
                    }
                }
            }

            return ['created' => $created, 'updated' => $updated, 'scenarios' => $newScenarios];
        });
    }

    private function build(): array
    {
        $missing = array_diff(['scenario', 'name'], array_keys($this->columns));

        if ($missing !== []) {
            return [
                'plan' => ['errors' => [__('Le fichier doit avoir une colonne « Scénario » et une colonne « Scène ».')], 'rows' => [], 'valid' => 0],
                'valid' => [],
            ];
        }

        // Seuls les éléments utilisables dans la campagne peuvent être liés.
        $lookups = [
            'entities' => $this->index($this->campaign->availableEntities()->get(['id', 'name']), fn (Entity $entity) => $entity->name),
            'documents' => $this->index($this->campaign->availableDocuments()->get(['id', 'title', 'original_name']), fn (Document $document) => [$document->title, pathinfo((string) $document->original_name, PATHINFO_FILENAME)]),
            'rules' => $this->index($this->campaign->availableRules()->get(['id', 'title']), fn (Rule $rule) => $rule->title),
        ];

        $existing = $this->campaign->scenes()->with('scenario')->get()
            ->keyBy(fn (Scene $scene) => mb_strtolower($scene->scenario->name.'|'.$scene->name));

        $rows = [];
        $valid = [];
        $seen = [];

        foreach ($this->table->rows as $row) {
            $cell = fn (string $target) => isset($this->columns[$target]) ? ($row['cells'][$this->columns[$target]] ?? '') : '';
            $errors = [];
            $warnings = [];

            foreach (self::LIMITS as $target => $limit) {
                if (mb_strlen($cell($target)) > $limit) {
                    $errors[] = __(':field : dépasse :max caractères', ['field' => $this->label($target), 'max' => $limit]);
                }
            }

            $scenario = $cell('scenario');
            $name = $cell('name');
            $key = mb_strtolower($scenario.'|'.$name);
            $status = self::status($cell('status'));

            if ($scenario === '') {
                $errors[] = __('scénario manquant');
            }

            if ($name === '') {
                $errors[] = __('nom de scène manquant');
            } elseif (isset($seen[$key])) {
                $errors[] = __('déjà présente ligne :line', ['line' => $seen[$key]]);
            } else {
                $seen[$key] = $row['line'];
            }

            if ($status === false) {
                $errors[] = __('statut « :name » inconnu', ['name' => $cell('status')]);
            }

            $links = [];
            foreach ($lookups as $relation => $lookup) {
                $links[$relation] = null;

                if (! isset($this->columns[$relation]) || $cell($relation) === '') {
                    continue;
                }

                $links[$relation] = [];
                foreach (self::split($cell($relation)) as $wanted) {
                    $id = $lookup[mb_strtolower($wanted)] ?? $lookup[Normalize::key($wanted)] ?? null;

                    if ($id === null) {
                        $warnings[] = match ($relation) {
                            'entities' => __('fiche « :name » introuvable', ['name' => $wanted]),
                            'documents' => __('document « :name » introuvable', ['name' => $wanted]),
                            default => __('règle « :name » introuvable', ['name' => $wanted]),
                        };
                    } elseif (! in_array($id, $links[$relation], true) && count($links[$relation]) < self::MAX_LINKS) {
                        $links[$relation][] = $id;
                    }
                }
            }

            $match = $this->updateExisting ? $existing->get($key) : null;

            $rows[] = [
                'line' => $row['line'],
                'name' => $name,
                'scenario' => $scenario,
                'links' => collect($links)->filter()->sum(fn (array $ids) => count($ids)),
                'action' => $match ? 'update' : 'create',
                'errors' => $errors,
                'warnings' => $warnings,
            ];

            if ($errors === []) {
                $valid[] = $links + [
                    'scenario' => $scenario,
                    'scenario_summary' => $cell('scenario_summary') ?: null,
                    'name' => $name,
                    'attributes' => [
                        'chapter' => $cell('chapter') ?: null,
                        'description' => $cell('description') ?: null,
                        'status' => $status,
                    ],
                ];
            }
        }

        return ['plan' => ['errors' => [], 'rows' => $rows, 'valid' => count($valid)], 'valid' => $valid];
    }

    /**
     * Index nom → identifiant, en minuscules et sous forme normalisée (accents, ponctuation).
     *
     * @return array<string, int>
     */
    private function index(Collection $models, callable $names): array
    {
        $index = [];

        foreach ($models as $model) {
            foreach ((array) $names($model) as $name) {
                if ((string) $name === '') {
                    continue;
                }

                $index[mb_strtolower($name)] ??= $model->id;
                $index[Normalize::key($name)] ??= $model->id;
            }
        }

        return $index;
    }

    /** @return list<string> */
    private static function split(string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[|\n]/', $value)), fn (string $part) => $part !== ''));
    }

    private function label(string $target): string
    {
        return match ($target) {
            'scenario' => __('scénario'),
            'scenario_summary' => __('résumé du scénario'),
            'chapter' => __('chapitre'),
            'name' => __('nom de la scène'),
            'description' => __('description'),
            default => $target,
        };
    }

    /** Statut lu, null si la case est vide, false s'il est inconnu. */
    private static function status(string $value): SceneStatus|null|false
    {
        $key = Normalize::key($value);

        if ($key === '') {
            return null;
        }

        foreach (SceneStatus::cases() as $status) {
            if (in_array($key, [Normalize::key($status->value), Normalize::key($status->label('fr')), Normalize::key($status->label())], true)) {
                return $status;
            }
        }

        return false;
    }
}
