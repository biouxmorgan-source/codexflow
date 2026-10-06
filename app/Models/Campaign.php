<?php

namespace App\Models;

use App\Enums\CampaignRole;
use App\Enums\CampaignStatus;
use Database\Factories\CampaignFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'game_system_id', 'world_id', 'status', 'archived_at'])]
class Campaign extends Model
{
    /** @use HasFactory<CampaignFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        // Une à une, pour que chaque entité supprime ses fichiers.
        static::deleting(function (Campaign $campaign) {
            $campaign->localEntities()->each(fn (Entity $entity) => $entity->delete());
        });
    }

    protected function casts(): array
    {
        return [
            'status' => CampaignStatus::class,
            'archived_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<GameSystem, $this> */
    public function gameSystem(): BelongsTo
    {
        return $this->belongsTo(GameSystem::class);
    }

    /** @return BelongsTo<World, $this> */
    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'campaign_memberships')
            ->using(CampaignMembership::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /** @return HasMany<Entity, $this> */
    public function localEntities(): HasMany
    {
        return $this->hasMany(Entity::class);
    }

    /** @return HasMany<CampaignEntityState, $this> */
    public function entityStates(): HasMany
    {
        return $this->hasMany(CampaignEntityState::class);
    }

    /**
     * Entités utilisables dans la campagne : ses entités locales et celles de son monde.
     *
     * @return Builder<Entity>
     */
    public function availableEntities(): Builder
    {
        return Entity::query()->availableIn($this);
    }

    public function roleOf(User $user): ?CampaignRole
    {
        $role = $this->members()->whereKey($user->getKey())->value('role');

        return $role === null ? null : CampaignRole::from($role);
    }

    public function isGameMaster(User $user): bool
    {
        return $this->roleOf($user) === CampaignRole::GameMaster;
    }

    /** @param Builder<Campaign> $query */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        $query->whereHas('members', fn (Builder $q) => $q->whereKey($user->getKey()));
    }
}
