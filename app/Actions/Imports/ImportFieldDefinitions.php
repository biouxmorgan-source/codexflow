<?php

namespace App\Actions\Imports;

use App\Enums\FieldType;
use App\Enums\Zone;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Models\GameSystem;
use App\Models\User;
use App\Support\Import\Normalize;
use App\Support\Import\TabularFile;
use Illuminate\Support\Facades\DB;

/**
 * Import d'une liste de champs (une ligne par champ). Colonnes reconnues :
 * Nom, Groupe, Type, Zone, Choix, Type de fiche, Modifiable par le joueur (facultative :
 * absente, un champ existant garde son réglage).
 */
class ImportFieldDefinitions
{
    public const COLUMNS = [
        'name' => ['nom', 'name', 'champ'],
        'group' => ['groupe', 'group', 'categorie', 'section'],
        'type' => ['type', 'typedechamp'],
        'zone' => ['zone', 'visibilite'],
        'options' => ['choix', 'options', 'valeurs'],
        'entity_type' => ['typedefiche', 'fiche', 'fiches', 'entite', 'entitytype'],
        'player_editable' => ['modifiableparlejoueur', 'modifiable', 'editable', 'playereditable'],
    ];

    /** @var array<string, int> */
    private array $columns = [];

    public function __construct(
        private readonly GameSystem $gameSystem,
        private readonly User $user,
        private readonly TabularFile $table,
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
     *     rows: list<array{line: int, name: string, group: string, type: string, zone: string, entity_type: string, action: string, errors: list<string>}>,
     *     valid: int,
     * }
     */
    public function plan(): array
    {
        return $this->build()['plan'];
    }

    /**
     * @return array{created: int, updated: int}
     */
    public function run(): array
    {
        $build = $this->build();

        return DB::transaction(function () use ($build) {
            $position = (int) $this->gameSystem->fieldDefinitions()->max('position');
            $created = 0;
            $updated = 0;

            foreach ($build['valid'] as $attributes) {
                $definition = $this->gameSystem->fieldDefinitions()
                    ->where('name', $attributes['name'])
                    ->where('entity_type_id', $attributes['entity_type_id'])
                    ->first();

                if ($definition) {
                    $definition->update($attributes);
                    $updated++;
                } else {
                    $this->gameSystem->fieldDefinitions()->create($attributes + ['position' => ++$position]);
                    $created++;
                }
            }

            return ['created' => $created, 'updated' => $updated];
        });
    }

    private function build(): array
    {
        if (! isset($this->columns['name'])) {
            return [
                'plan' => ['errors' => ['Le fichier doit avoir une colonne « Nom ».'], 'rows' => [], 'valid' => 0],
                'valid' => [],
            ];
        }

        $types = EntityType::query()->availableTo($this->user)->get();
        $existing = $this->gameSystem->fieldDefinitions()->get()
            ->keyBy(fn (FieldDefinition $definition) => $definition->entity_type_id.'|'.mb_strtolower($definition->name));

        $rows = [];
        $valid = [];
        $seen = [];

        foreach ($this->table->rows as $row) {
            $cell = fn (string $target) => isset($this->columns[$target]) ? ($row['cells'][$this->columns[$target]] ?? '') : '';
            $errors = [];

            $name = $cell('name');
            $type = FieldType::fromLabel($cell('type'));
            $zone = Normalize::zone($cell('zone'));
            $options = FieldDefinition::splitOptions($cell('options'));
            $entityType = null;
            [$editable, $editableError] = isset($this->columns['player_editable'])
                ? FieldType::Boolean->parse($cell('player_editable'))
                : [null, null];

            if ($name === '') {
                $errors[] = 'nom manquant';
            } elseif (mb_strlen($name) > 100) {
                $errors[] = 'nom trop long (100 caractères maximum)';
            }

            if (mb_strlen($cell('group')) > 100) {
                $errors[] = 'groupe trop long (100 caractères maximum)';
            }

            if ($type === null) {
                $errors[] = 'type « '.$cell('type').' » inconnu (texte, texte long, nombre, oui/non, date, liste ou compteur)';
            } elseif ($type === FieldType::Select && $options === []) {
                $errors[] = 'une liste a besoin de choix, séparés par |';
            }

            if ($zone === null) {
                $errors[] = 'zone « '.$cell('zone').' » inconnue (publique ou MJ)';
            }

            if ($editableError !== null) {
                $errors[] = 'modifiable par le joueur : '.$editableError;
            }

            if ($cell('entity_type') !== '') {
                $entityType = $types->first(fn (EntityType $candidate) => in_array(Normalize::key($cell('entity_type')), [Normalize::key($candidate->name), Normalize::key((string) $candidate->key)], true));

                if ($entityType === null) {
                    $errors[] = 'type de fiche « '.$cell('entity_type').' » inconnu';
                }
            }

            $key = $entityType?->id.'|'.mb_strtolower($name);

            if ($name !== '' && isset($seen[$key])) {
                $errors[] = 'déjà présent ligne '.$seen[$key];
            }

            $seen[$key] ??= $row['line'];

            $rows[] = [
                'line' => $row['line'],
                'name' => $name,
                'group' => $cell('group'),
                'type' => $type?->label() ?? $cell('type'),
                'zone' => $zone?->label() ?? $cell('zone'),
                'entity_type' => $entityType?->name ?? 'Tous les types',
                'action' => $existing->has($key) ? 'update' : 'create',
                'errors' => $errors,
            ];

            if ($errors === []) {
                $valid[] = [
                    'name' => $name,
                    'group' => $cell('group') ?: null,
                    'type' => $type,
                    'zone' => $zone,
                    'options' => $type === FieldType::Select ? $options : null,
                    'entity_type_id' => $entityType?->id,
                ] + ($editable === null ? [] : [
                    // La zone MJ reste hors de portée des joueurs.
                    'player_editable' => $editable && $zone === Zone::Public,
                ]);
            }
        }

        return ['plan' => ['errors' => [], 'rows' => $rows, 'valid' => count($valid)], 'valid' => $valid];
    }
}
