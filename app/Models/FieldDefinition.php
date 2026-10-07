<?php

namespace App\Models;

use App\Enums\FieldType;
use App\Enums\Zone;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * Champ libre nommé par le MJ pour un jeu. Les valeurs sont stockées dans entities.field_values.
 */
#[Fillable(['entity_type_id', 'group', 'name', 'type', 'options', 'zone', 'position'])]
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
    }

    protected function casts(): array
    {
        return [
            'type' => FieldType::class,
            'zone' => Zone::class,
            'options' => 'array',
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
        $query->where(fn (Builder $q) => $q->whereNull('entity_type_id')->orWhere('entity_type_id', $entityTypeId));
    }

    /** @param Builder<FieldDefinition> $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    /**
     * @return array{0: mixed, 1: string|null}
     */
    public function parse(mixed $raw): array
    {
        return $this->type->parse($raw, $this->options);
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
        return $this->group ?: 'Champs';
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
