<?php

namespace App\Support\Dice;

use App\Enums\FieldType;
use App\Enums\Zone;
use App\Models\Campaign;
use App\Models\FieldDefinition;
use App\Models\PlayerCharacter;
use Illuminate\Support\Collection;

/**
 * Valeurs de la fiche qu'un lancer peut reprendre comme bonus ou comme seuil : les champs nombre
 * et compteur de la zone publique, lus sur la fiche du personnage qui lance, jamais ceux de la zone MJ.
 */
final class SheetValues
{
    /** @return Collection<int, FieldDefinition> */
    public static function fields(Campaign $campaign): Collection
    {
        if ($campaign->gameSystem === null) {
            return collect();
        }

        return $campaign->gameSystem->fieldDefinitions()
            ->where('zone', Zone::Public)
            ->whereIn('type', [FieldType::Number->value, FieldType::Counter->value])
            ->ordered()
            ->get();
    }

    /** @return list<array{id: int, name: string, group: string}> pour le plateau */
    public static function choices(Campaign $campaign): array
    {
        return self::fields($campaign)
            ->map(fn (FieldDefinition $field) => ['id' => $field->id, 'name' => $field->name, 'group' => $field->groupLabel()])
            ->values()->all();
    }

    /** Le lancer avec les valeurs de la fiche du personnage ; sans personnage, rien n'est lu. */
    public static function apply(DiceFormula $formula, Campaign $campaign, ?PlayerCharacter $character): DiceFormula
    {
        if (! $formula->readsSheet()) {
            return $formula;
        }

        $fields = self::fields($campaign)->keyBy('id');
        $entity = $character?->entity;

        return $formula->withSheet(function (int $id) use ($fields, $entity, $campaign) {
            $field = $fields->get($id);
            if ($field === null || $entity === null || ! $field->appliesTo($entity->entity_type_id)) {
                return null;
            }
            $value = $entity->fieldValueIn($field, $campaign);
            $value = $field->type === FieldType::Counter ? ($value['value'] ?? null) : $value;

            return is_numeric($value) ? (float) $value : null;
        }, $fields->map->name->all(), $entity !== null);
    }
}
