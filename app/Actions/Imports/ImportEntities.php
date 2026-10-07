<?php

namespace App\Actions\Imports;

use App\Enums\FieldType;
use App\Enums\Zone;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Models\User;
use App\Support\Import\Normalize;
use App\Support\Import\TabularFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Import de fiches avec leurs champs libres. plan() ne modifie rien ; run() applique le plan.
 */
class ImportEntities
{
    /** Cibles possibles d'une colonne, hors champs libres (« field:ID ») et nouveau champ (« new »). */
    public const TARGETS = [
        'ignore' => 'Ignorer',
        'name' => 'Nom',
        'type' => 'Type de fiche',
        'summary' => 'Résumé',
        'description' => 'Description',
        'gm_notes' => 'Notes MJ',
    ];

    private const LIMITS = ['name' => 255, 'summary' => 500, 'description' => 20000, 'gm_notes' => 20000];

    /**
     * Libellés traduits des cibles de colonne, dans l'ordre de TARGETS.
     *
     * @return array<string, string>
     */
    public static function targets(): array
    {
        return [
            'ignore' => __('Ignorer'),
            'name' => __('Nom'),
            'type' => __('Type de fiche'),
            'summary' => __('Résumé'),
            'description' => __('Description'),
            'gm_notes' => __('Notes MJ'),
        ];
    }

    /** @var Collection<int, EntityType> */
    private Collection $types;

    /** @var Collection<int, FieldDefinition> */
    private Collection $definitions;

    /**
     * @param  array<int, string>  $mapping  [index de colonne => cible]
     * @param  array{default_type_id: int, scope: string, update_existing: bool, new_group: ?string, new_zone: Zone, new_type_id: ?int}  $options
     */
    public function __construct(
        private readonly Campaign $campaign,
        private readonly User $user,
        private readonly TabularFile $table,
        private readonly array $mapping,
        private readonly array $options,
    ) {
        $this->types = EntityType::query()->availableTo($user)->get();
        $this->definitions = $campaign->gameSystem->fieldDefinitions()->get()->keyBy('id');
    }

    /**
     * Devine la cible de chaque colonne d'après son en-tête.
     *
     * @param  Collection<int, FieldDefinition>  $definitions
     * @return array<int, string>
     */
    public static function guessMapping(array $headers, Collection $definitions): array
    {
        $aliases = [
            'name' => ['nom', 'name', 'titre'],
            'type' => ['type', 'typedefiche', 'categorie'],
            'summary' => ['resume', 'summary', 'accroche'],
            'description' => ['description', 'desc'],
            'gm_notes' => ['notesmj', 'notes', 'gmnotes', 'secret', 'secrets', 'mj'],
        ];

        $used = [];
        $mapping = [];

        foreach ($headers as $index => $header) {
            $key = Normalize::key($header);
            $target = collect($aliases)->search(fn (array $names) => in_array($key, $names, true));

            // Colonne de base déjà prise (« Notes MJ » puis « Secret ») : un champ du même nom passe avant.
            if ($target === false || in_array($target, $used, true)) {
                $definition = $definitions->first(fn (FieldDefinition $definition) => Normalize::key($definition->name) === $key);
                $target = $definition ? 'field:'.$definition->id : 'new';
            }

            if ($target !== 'new' && in_array($target, $used, true)) {
                $target = 'ignore';
            }

            $used[] = $target;
            $mapping[$index] = $target;
        }

        return $mapping;
    }

    /**
     * @return array{
     *     errors: list<string>,
     *     rows: list<array{line: int, name: string, type: string, action: string, errors: list<string>, warnings: list<string>}>,
     *     new_fields: list<array{column: int, name: string, type: FieldType, reused: bool}>,
     *     valid: int,
     * }
     */
    public function plan(): array
    {
        return $this->build()['plan'];
    }

    /**
     * @return array{created: int, updated: int, fields: int}
     */
    public function run(): array
    {
        $build = $this->build();

        return DB::transaction(function () use ($build) {
            $columnDefinitions = $build['columns'];
            $createdFields = 0;
            $position = (int) $this->campaign->gameSystem->fieldDefinitions()->max('position');

            foreach ($build['plan']['new_fields'] as $new) {
                if ($new['reused']) {
                    continue;
                }

                $definition = $this->campaign->gameSystem->fieldDefinitions()->create([
                    'name' => $new['name'],
                    'group' => $this->options['new_group'],
                    'type' => $new['type'],
                    'zone' => $this->options['new_zone'],
                    'entity_type_id' => $this->options['new_type_id'],
                    'position' => ++$position,
                ]);
                $columnDefinitions[$new['column']] = $definition;
                $createdFields++;
            }

            $created = 0;
            $updated = 0;

            foreach ($build['valid'] as $row) {
                $entity = $row['existing'] ?? new Entity;

                foreach ($row['attributes'] as $attribute => $value) {
                    if ($value !== null || ! $entity->exists) {
                        $entity->{$attribute} = $value;
                    }
                }

                $values = [];
                foreach ($row['fields'] as $column => $value) {
                    if ($value !== null) {
                        $values[($row['definitions'][$column] ?? $columnDefinitions[$column])->id] = $value;
                    }
                }
                $entity->setFieldValues($values);

                if (! $entity->exists) {
                    $entity->owner()->associate($this->user);

                    if ($this->options['scope'] === 'world' && $this->campaign->world_id) {
                        $entity->world()->associate($this->campaign->world);
                    } else {
                        $entity->campaign()->associate($this->campaign);
                    }

                    $created++;
                } else {
                    $updated++;
                }

                $entity->save();
            }

            return ['created' => $created, 'updated' => $updated, 'fields' => $createdFields];
        });
    }

    private function build(): array
    {
        $errors = [];
        $targets = array_count_values($this->mapping);

        if (($targets['name'] ?? 0) !== 1) {
            $errors[] = __('Associez une colonne, et une seule, au nom des fiches.');
        }

        // Colonnes de champs libres : définitions existantes ou nouveaux champs (types devinés).
        $columns = [];
        $newFields = [];

        foreach ($this->mapping as $column => $target) {
            if (str_starts_with($target, 'field:')) {
                $definition = $this->definitions->get((int) substr($target, 6));

                if ($definition === null) {
                    $errors[] = __("La colonne « :name » vise un champ qui n'existe plus.", ['name' => $this->table->headers[$column]]);
                } else {
                    $columns[$column] = $definition;
                }
            } elseif ($target === 'new') {
                $name = $this->table->headers[$column];
                $reused = $this->definitions->first(fn (FieldDefinition $definition) => Normalize::key($definition->name) === Normalize::key($name)
                    && $definition->entity_type_id === $this->options['new_type_id']);
                $type = $reused?->type ?? FieldType::guess($this->table->column($column));

                $columns[$column] = $reused ?? new FieldDefinition(['name' => $name, 'type' => $type]);
                $newFields[] = ['column' => $column, 'name' => $name, 'type' => $type, 'reused' => $reused !== null];
            }
        }

        $existing = $this->campaign->availableEntities()->get()
            ->keyBy(fn (Entity $entity) => $entity->entity_type_id.'|'.mb_strtolower($entity->name));

        $rows = [];
        $valid = [];
        $seen = [];

        foreach ($this->table->rows as $row) {
            $rowErrors = [];
            $warnings = [];
            $attributes = [];
            $fields = [];
            $fieldColumns = [];
            $rowDefinitions = [];
            $typeId = $this->options['default_type_id'];

            foreach ($this->mapping as $column => $target) {
                $raw = $row['cells'][$column] ?? '';

                if (isset(self::LIMITS[$target])) {
                    $attributes[$target] = $raw === '' ? null : $raw;

                    if (mb_strlen($raw) > self::LIMITS[$target]) {
                        $rowErrors[] = __(':field : dépasse :max caractères', ['field' => self::targets()[$target], 'max' => self::LIMITS[$target]]);
                    }
                } elseif ($target === 'type' && $raw !== '') {
                    $type = $this->types->first(fn (EntityType $type) => in_array(Normalize::key($raw), [Normalize::key($type->name), Normalize::key((string) $type->key)], true));

                    if ($type) {
                        $typeId = $type->id;
                    } else {
                        $rowErrors[] = __('type de fiche « :name » inconnu', ['name' => $raw]);
                    }
                } elseif (isset($columns[$column])) {
                    $fieldColumns[] = $column;
                    [$value, $error] = $columns[$column]->parse($raw);

                    if ($error) {
                        // Clé par colonne, pour pouvoir retirer l'erreur si un champ voisin la remplace.
                        $rowErrors['column:'.$column] = __(':name : :error', ['name' => $columns[$column]->name, 'error' => $error]);
                    } else {
                        $fields[$column] = $value;
                    }
                }
            }

            // Un champ réservé à un autre type de fiche ne s'afficherait pas : on prend le champ
            // du même nom prévu pour ce type s'il existe, sinon on le signale.
            foreach ($fieldColumns as $column) {
                $fieldTypeId = $columns[$column]->exists ? $columns[$column]->entity_type_id : $this->options['new_type_id'];

                if ($fieldTypeId === null || $fieldTypeId === $typeId) {
                    continue;
                }

                unset($fields[$column]);
                unset($rowErrors['column:'.$column]);
                $sibling = $this->sibling($columns[$column], $typeId);

                if ($sibling) {
                    [$value, $error] = $sibling->parse($row['cells'][$column] ?? '');

                    if ($error) {
                        $rowErrors[] = __(':name : :error', ['name' => $sibling->name, 'error' => $error]);
                    } else {
                        $fields[$column] = $value;
                        $rowDefinitions[$column] = $sibling;
                    }
                } elseif (($row['cells'][$column] ?? '') !== '') {
                    $warnings[] = __(':name ne concerne que les fiches :type : valeur ignorée', ['name' => $columns[$column]->name, 'type' => $this->types->firstWhere('id', $fieldTypeId)?->name]);
                }
            }

            $name = (string) ($attributes['name'] ?? '');
            $key = $typeId.'|'.mb_strtolower($name);

            if ($name === '') {
                $rowErrors[] = __('nom manquant');
            } elseif (isset($seen[$key])) {
                $rowErrors[] = __('déjà présente ligne :line', ['line' => $seen[$key]]);
            } else {
                $seen[$key] = $row['line'];
            }

            $rowErrors = array_values($rowErrors);
            $match = $this->options['update_existing'] ? $existing->get($key) : null;
            $attributes['entity_type_id'] = $typeId;

            $rows[] = [
                'line' => $row['line'],
                'name' => $name,
                'type' => (string) $this->types->firstWhere('id', $typeId)?->name,
                'action' => $match ? 'update' : 'create',
                'errors' => $rowErrors,
                'warnings' => $warnings,
            ];

            if ($rowErrors === []) {
                $valid[] = ['existing' => $match, 'attributes' => $attributes, 'fields' => $fields, 'definitions' => $rowDefinitions];
            }
        }

        return [
            'plan' => [
                'errors' => $errors,
                'rows' => $rows,
                'new_fields' => $newFields,
                'valid' => $errors === [] ? count($valid) : 0,
            ],
            'columns' => $columns,
            'valid' => $errors === [] ? $valid : [],
        ];
    }

    /**
     * Champ de même nom propre à un type de fiche (ou commun à tous), pour qu'une même
     * colonne serve à plusieurs types : FOR d'un personnage et FOR d'une créature.
     */
    private function sibling(FieldDefinition $definition, int $typeId): ?FieldDefinition
    {
        $candidates = $this->definitions->filter(fn (FieldDefinition $candidate) => Normalize::key($candidate->name) === Normalize::key($definition->name));

        return $candidates->first(fn (FieldDefinition $candidate) => $candidate->entity_type_id === $typeId)
            ?? $candidates->first(fn (FieldDefinition $candidate) => $candidate->entity_type_id === null);
    }
}
