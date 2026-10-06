<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class EntityType extends Model
{
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
