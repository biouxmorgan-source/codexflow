<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Surcharge d'une entité de monde dans une campagne, sans toucher à la fiche mondiale.
 */
#[Fillable(['campaign_id', 'status', 'gm_notes', 'overrides'])]
#[Hidden(['gm_notes'])]
class CampaignEntityState extends Model
{
    use RecordsActivity;

    protected $attributes = [
        'overrides' => '{}',
    ];

    protected function casts(): array
    {
        return [
            'overrides' => 'array',
        ];
    }

    /**
     * Valeurs de champs propres à la campagne : [id de définition => valeur].
     * Une clé présente remplace la valeur du monde, même par une valeur vide.
     *
     * @return array<string, mixed>
     */
    public function fieldOverrides(): array
    {
        return (array) ($this->overrides ?? []);
    }

    public function overrides(FieldDefinition $definition): bool
    {
        return array_key_exists((string) $definition->getKey(), $this->fieldOverrides());
    }

    public function setOverride(FieldDefinition $definition, mixed $value): void
    {
        $overrides = $this->fieldOverrides();
        $overrides[(string) $definition->getKey()] = $value;

        // Toujours un objet JSON : des clés numériques ne doivent pas devenir une liste.
        $this->overrides = (object) $overrides;
    }

    public function removeOverride(FieldDefinition $definition): void
    {
        $overrides = $this->fieldOverrides();
        unset($overrides[(string) $definition->getKey()]);

        $this->overrides = (object) $overrides;
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<Entity, $this> */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function activityType(): string
    {
        return 'entity_state';
    }

    public function activityLabel(): string
    {
        return (string) $this->entity?->name;
    }

    public function activityScope(): array
    {
        return ['campaign_id' => $this->campaign_id];
    }

    protected function activityJsonAttributes(): array
    {
        return ['overrides'];
    }
}
