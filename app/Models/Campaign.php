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
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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
            $campaign->documents()->each(fn (Document $document) => $document->delete());
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

    /** @return HasMany<CampaignInvitation, $this> */
    public function invitations(): HasMany
    {
        return $this->hasMany(CampaignInvitation::class);
    }

    /** @return HasMany<PlayerCharacter, $this> */
    public function playerCharacters(): HasMany
    {
        return $this->hasMany(PlayerCharacter::class);
    }

    /** @return HasMany<Message, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /** @return HasMany<Entity, $this> */
    public function localEntities(): HasMany
    {
        return $this->hasMany(Entity::class);
    }

    /** @return HasMany<Scenario, $this> */
    public function scenarios(): HasMany
    {
        return $this->hasMany(Scenario::class)->orderBy('position')->orderBy('id');
    }

    /** @return HasManyThrough<Scene, Scenario, $this> */
    public function scenes(): HasManyThrough
    {
        return $this->hasManyThrough(Scene::class, Scenario::class);
    }

    /** @return HasMany<PlaySession, $this> */
    public function playSessions(): HasMany
    {
        return $this->hasMany(PlaySession::class);
    }

    public function openSession(): ?PlaySession
    {
        return $this->playSessions()->whereNull('ended_at')->first();
    }

    /** @return BelongsToMany<Entity, $this> */
    public function pins(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'campaign_pins')->withPivot('position')->withTimestamps()->orderByPivot('position');
    }

    /** @return HasMany<ToPlayItem, $this> */
    public function toPlayItems(): HasMany
    {
        return $this->hasMany(ToPlayItem::class)->orderBy('position')->orderBy('id');
    }

    /** @return HasMany<Rule, $this> */
    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class);
    }

    /** @return HasMany<Document, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /** @return Builder<Rule> */
    public function availableRules(): Builder
    {
        return Rule::query()->availableIn($this);
    }

    /** @return Builder<Document> */
    public function availableDocuments(): Builder
    {
        return Document::query()->availableIn($this);
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
