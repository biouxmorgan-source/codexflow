<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Database\Factories\EntityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Fiche d'entité. Zone publique : name, summary, description. Zone MJ : gm_notes.
 * Les champs libres (field_values) sont rangés par zone selon leur définition : masqués à la sérialisation.
 */
#[Fillable(['entity_type_id', 'name', 'summary', 'description', 'gm_notes', 'image_path'])]
#[Hidden(['gm_notes', 'field_values'])]
class Entity extends Model
{
    /** @use HasFactory<EntityFactory> */
    use HasFactory;

    use RecordsActivity;

    /** Disque privé : les fichiers ne sont servis qu'après vérification des droits. */
    public const FILES_DISK = 'local';

    protected function casts(): array
    {
        return [
            'field_values' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Entity $entity) {
            $entity->attachments->each->delete();
            $entity->deleteImage();
            PlayerCharacter::where('entity_id', $entity->id)->get()->each->delete();
        });
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<EntityType, $this> */
    public function type(): BelongsTo
    {
        return $this->belongsTo(EntityType::class, 'entity_type_id');
    }

    /** @return BelongsTo<World, $this> */
    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return HasMany<CampaignEntityState, $this> */
    public function campaignStates(): HasMany
    {
        return $this->hasMany(CampaignEntityState::class);
    }

    /** @return HasMany<Attachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class)->latest();
    }

    /** @return BelongsToMany<Scene, $this> */
    public function scenes(): BelongsToMany
    {
        return $this->belongsToMany(Scene::class, 'scene_entity')->withPivot('note', 'position');
    }

    /** @return BelongsToMany<Document, $this> */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class)->withTimestamps()->orderBy('documents.title');
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderByRaw('lower(tags.name)');
    }

    /**
     * Relations de la fiche dans les deux sens, visibles dans la campagne.
     *
     * @return Builder<EntityRelation>
     */
    public function relationsIn(Campaign $campaign): Builder
    {
        return EntityRelation::query()
            ->visibleIn($campaign)
            ->where(fn (Builder $q) => $q->where('from_entity_id', $this->getKey())->orWhere('to_entity_id', $this->getKey()))
            ->with(['from.type', 'to.type']);
    }

    public function hasImage(): bool
    {
        return $this->image_path !== null;
    }

    public function deleteImage(): void
    {
        if ($this->image_path !== null) {
            Storage::disk(self::FILES_DISK)->delete($this->image_path);
            $this->image_path = null;
        }
    }

    public function fieldValue(FieldDefinition $definition): mixed
    {
        return ($this->field_values ?? [])[(string) $definition->getKey()] ?? null;
    }

    /**
     * Valeur du champ dans une campagne : la surcharge de la campagne si elle existe,
     * sinon la valeur de la fiche.
     */
    public function fieldValueIn(FieldDefinition $definition, Campaign $campaign): mixed
    {
        $state = $this->loadedStateIn($campaign);

        return $state?->overrides($definition) ? $state->fieldOverrides()[(string) $definition->getKey()] : $this->fieldValue($definition);
    }

    public function overridesFieldIn(FieldDefinition $definition, Campaign $campaign): bool
    {
        return (bool) $this->loadedStateIn($campaign)?->overrides($definition);
    }

    /**
     * État de campagne, lu une fois puis gardé en mémoire dans la relation campaignStates.
     */
    private function loadedStateIn(Campaign $campaign): ?CampaignEntityState
    {
        if (! $this->relationLoaded('campaignStates')) {
            $this->setRelation('campaignStates', $this->campaignStates()->where('campaign_id', $campaign->getKey())->get());
        }

        return $this->campaignStates->firstWhere('campaign_id', $campaign->getKey());
    }

    /**
     * Remplace les valeurs des champs donnés, sans toucher à celles des autres jeux.
     *
     * @param  array<int|string, mixed>  $values  [id de définition => valeur ou null]
     */
    public function setFieldValues(array $values): void
    {
        $current = $this->field_values ?? [];

        foreach ($values as $id => $value) {
            if ($value === null) {
                unset($current[(string) $id]);
            } else {
                $current[(string) $id] = $value;
            }
        }

        // Un objet vide reste un objet JSON, pas un tableau.
        $this->field_values = $current === [] ? new \stdClass : $current;
    }

    public function isWorldEntity(): bool
    {
        return $this->world_id !== null;
    }

    /** @param Builder<Entity> $query */
    public function scopeAvailableIn(Builder $query, Campaign $campaign): void
    {
        $query->where(function (Builder $q) use ($campaign) {
            $q->where('campaign_id', $campaign->getKey());

            if ($campaign->world_id !== null) {
                $q->orWhere('world_id', $campaign->world_id);
            }
        });
    }

    /**
     * État de l'entité dans une campagne, créé à la demande.
     */
    public function stateIn(Campaign $campaign): CampaignEntityState
    {
        return $this->campaignStates()->firstOrNew(['campaign_id' => $campaign->getKey()]);
    }

    public function activityType(): string
    {
        return 'entity';
    }

    public function activityLabel(): string
    {
        return $this->name;
    }

    public function activityScope(): array
    {
        return ['world_id' => $this->world_id, 'campaign_id' => $this->campaign_id];
    }

    protected function activityJsonAttributes(): array
    {
        return ['field_values'];
    }
}
