<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class EntityType extends Model
{
    protected static function booted(): void
    {
        // Un champ partagé avec d'autres types leur reste : seul ce type est retiré de sa liste.
        static::deleting(function (EntityType $type) {
            FieldDefinition::whereJsonContains('entity_type_ids', $type->id)->get()
                ->each(fn (FieldDefinition $field) => $field->assignTypes(array_diff($field->typeIds(), [$type->id]))->save());
        });
    }

    /** @return HasMany<Entity, $this> */
    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class);
    }

    /** @return HasMany<FieldDefinition, $this> */
    public function fieldDefinitions(): HasMany
    {
        return $this->hasMany(FieldDefinition::class);
    }

    /** Les types standards portent un nom français en base, affiché dans la langue de l'interface. */
    protected function name(): Attribute
    {
        return Attribute::get(fn (?string $value) => $this->user_id !== null ? $value : match ($this->key) {
            'character' => __('Personnage'),
            'place' => __('Lieu'),
            'organization' => __('Organisation'),
            'item' => __('Objet'),
            'creature' => __('Créature'),
            'document' => __('Document'),
            default => $value,
        });
    }

    public function isStandard(): bool
    {
        return $this->user_id === null;
    }

    /**
     * Types standards et types personnalisés de l'utilisateur.
     *
     * @param  Builder<EntityType>  $query
     */
    public function scopeAvailableTo(Builder $query, User $user): void
    {
        $query->where(fn (Builder $q) => $q->whereNull('user_id')->orWhere('user_id', $user->getKey()));
    }

    public static function standard(string $key): self
    {
        return static::query()->whereNull('user_id')->where('key', $key)->firstOrFail();
    }
}
