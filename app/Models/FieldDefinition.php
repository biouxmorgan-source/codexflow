<?php

namespace App\Models;

use App\Enums\FieldType;
use App\Enums\Zone;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Champ libre nommé par le MJ pour un jeu. Les valeurs sont stockées dans entities.field_values.
 */
#[Fillable(['entity_type_id', 'entity_type_ids', 'group', 'name', 'type', 'options', 'zone', 'position', 'player_editable'])]
class FieldDefinition extends Model
{
    use RecordsActivity;

    protected static function booted(): void
    {
        static::deleted(function (FieldDefinition $definition) {
            $key = (string) $definition->getKey();
            DB::update('update entities set field_values = field_values - ? where jsonb_exists(field_values, ?)', [$key, $key]);
            DB::update("update campaign_entity_states set overrides = overrides - ? where jsonb_typeof(overrides) = 'object' and jsonb_exists(overrides, ?)", [$key, $key]);
        });

        static::updated(function (FieldDefinition $definition) {
            if ($definition->wasChanged('type')) {
                $definition->convertStoredValues(FieldType::from($definition->getRawOriginal('type')));
            }
        });
    }

    /**
     * Garde les valeurs saisies quand le champ change de type entre nombre et compteur :
     * des PV « 11 » deviennent « 11 / 11 », et un compteur redevenu nombre garde sa valeur actuelle.
     */
    private function convertStoredValues(FieldType $from): void
    {
        $sql = match (true) {
            $from === FieldType::Number && $this->type === FieldType::Counter => [
                'number', "jsonb_build_object('value', %1\$s->?, 'max', %1\$s->?)",
            ],
            $from === FieldType::Counter && $this->type === FieldType::Number => [
                'object', "coalesce(%1\$s->?->'value', '0'::jsonb)",
            ],
            default => null,
        };

        if ($sql === null) {
            return;
        }

        [$jsonType, $expression] = $sql;
        $key = (string) $this->getKey();
        $bindings = $this->type === FieldType::Counter ? [$key, $key] : [$key];

        foreach (['entities' => 'field_values', 'campaign_entity_states' => 'overrides'] as $table => $column) {
            DB::update(
                "update {$table} set {$column} = jsonb_set({$column}, array[?], ".sprintf($expression, $column).")
                where jsonb_typeof({$column}) = 'object' and jsonb_typeof({$column}->?) = ?",
                [$key, ...$bindings, $key, $jsonType],
            );
        }
    }

    protected function casts(): array
    {
        return [
            'type' => FieldType::class,
            'zone' => Zone::class,
            'options' => 'array',
            'entity_type_ids' => 'array',
            'player_editable' => 'boolean',
        ];
    }

    /** @return BelongsTo<GameSystem, $this> */
    public function gameSystem(): BelongsTo
    {
        return $this->belongsTo(GameSystem::class);
    }

    /** @return BelongsTo<EntityType, $this> */
    public function entityType(): BelongsTo
    {
        return $this->belongsTo(EntityType::class);
    }

    /**
     * Champs d'un jeu qui s'appliquent à un type de fiche (ceux du type et ceux de tous les types).
     *
     * @param  Builder<FieldDefinition>  $query
     */
    public function scopeForType(Builder $query, int|string|null $entityTypeId): void
    {
        $query->where(fn (Builder $q) => $q->whereNull('entity_type_id')
            ->orWhere('entity_type_id', $entityTypeId)
            ->when($entityTypeId !== null && $entityTypeId !== '', fn (Builder $q) => $q->orWhereJsonContains('entity_type_ids', (int) $entityTypeId)));
    }

    /**
     * Types de fiche concernés ; une liste vide veut dire tous les types.
     *
     * @return list<int>
     */
    public function typeIds(): array
    {
        if ($this->entity_type_id === null) {
            return [];
        }

        return collect($this->entity_type_ids ?: [$this->entity_type_id])->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    public function appliesTo(int|string|null $entityTypeId): bool
    {
        $ids = $this->typeIds();

        return $ids === [] || in_array((int) $entityTypeId, $ids, true);
    }

    /**
     * Choisit les types concernés : le premier dans entity_type_id, la liste complète
     * dans entity_type_ids quand il y en a plusieurs. Aucun type : tous.
     *
     * @param  array<int|string>  $ids
     */
    public function assignTypes(array $ids): static
    {
        $ids = collect($ids)->map(fn ($id) => (int) $id)->filter()->unique()->sort()->values()->all();

        $this->entity_type_id = $ids[0] ?? null;
        $this->entity_type_ids = count($ids) > 1 ? $ids : null;

        return $this;
    }

    /**
     * Noms des types concernés, ou « Tous les types ».
     *
     * @param  Collection<int, EntityType>|null  $types  types déjà chargés, pour éviter une requête
     */
    public function typeLabel(?Collection $types = null): string
    {
        $ids = $this->typeIds();

        if ($ids === []) {
            return __('Tous les types');
        }

        $types = $types !== null && collect($ids)->every(fn (int $id) => $types->contains('id', $id))
            ? $types->whereIn('id', $ids)
            : EntityType::whereKey($ids)->get();

        return $types->sortBy(fn (EntityType $type) => array_search($type->id, $ids, true))->pluck('name')->implode(', ');
    }

    /** @param Builder<FieldDefinition> $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    /**
     * Valeur saisie convertie ; dans une campagne, une fiche citée par son nom est retrouvée
     * et liée par son identifiant (le lien survit à un renommage), un document doit en faire partie.
     *
     * @return array{0: mixed, 1: string|null}
     */
    public function parse(mixed $raw, ?Campaign $campaign = null): array
    {
        [$value, $error] = $this->type->parse($raw, $this->options);

        if ($campaign === null || $value === null || $error !== null) {
            return [$value, $error];
        }

        if ($this->type === FieldType::EntityRef && preg_match('/^\[\[([^|]+)\]\]$/u', $value, $match)) {
            $entity = $campaign->availableEntities()->whereRaw('lower(name) = ?', [mb_strtolower(trim($match[1]))])->first(['id', 'name']);

            return [$entity ? '[['.$entity->name.'|'.$entity->id.']]' : $value, null];
        }

        if ($this->type === FieldType::File && ! $campaign->availableDocuments()->whereKey($value)->exists()) {
            return [null, __('ce document ne fait pas partie de la campagne')];
        }

        return [$value, null];
    }

    /**
     * Choix d'une liste, saisis un par ligne ou séparés par « | ».
     *
     * @return list<string>
     */
    public static function splitOptions(string|array|null $options): array
    {
        $parts = is_array($options) ? $options : preg_split('/\r\n|\r|\n|\|/', (string) $options);

        return collect($parts)->map(fn ($option) => trim((string) $option))->filter()->unique()->values()->all();
    }

    public function groupLabel(): string
    {
        return $this->group ?: __('Champs');
    }

    public function activityType(): string
    {
        return 'field';
    }

    public function activityLabel(): string
    {
        return $this->name;
    }

    public function activityScope(): array
    {
        return ['game_system_id' => $this->game_system_id];
    }
}
