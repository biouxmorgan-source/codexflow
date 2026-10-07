<?php

namespace App\Models;

use App\Enums\Zone;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

/**
 * PDF ou image de la bibliothèque : rangé dans un jeu, un monde ou une campagne,
 * puis associé aux scènes, fiches et règles qui en ont besoin.
 */
#[Fillable(['title', 'description', 'zone', 'disk', 'path', 'original_name', 'mime_type', 'size'])]
class Document extends Model
{
    use RecordsActivity;

    public const DISK = 'local';

    /** Types acceptés au téléversement (extensions). */
    public const MIMES = 'pdf,jpg,jpeg,png,gif,webp';

    protected $attributes = ['zone' => 'gm'];

    protected function casts(): array
    {
        return [
            'zone' => Zone::class,
            'size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(fn (Document $document) => Storage::disk($document->disk)->delete($document->path));
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

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderByRaw('lower(tags.name)');
    }

    /** @return BelongsToMany<Scene, $this> */
    public function scenes(): BelongsToMany
    {
        return $this->belongsToMany(Scene::class);
    }

    /** @return BelongsToMany<Entity, $this> */
    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class)->withTimestamps();
    }

    /** @return BelongsToMany<Rule, $this> */
    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(Rule::class)->withTimestamps();
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /** « Jeu », « Monde » ou « Campagne » : où le document est rangé. */
    public function scopeLabel(): string
    {
        return match (true) {
            $this->game_system_id !== null => 'Jeu',
            $this->world_id !== null => 'Monde',
            default => 'Campagne',
        };
    }

    public function humanSize(): string
    {
        return match (true) {
            $this->size >= 1_048_576 => number_format($this->size / 1_048_576, 1, ',', ' ').' Mo',
            $this->size >= 1024 => number_format($this->size / 1024, 0, ',', ' ').' Ko',
            default => $this->size.' o',
        };
    }

    /**
     * Documents utilisables dans la campagne : les siens, ceux de son monde et de son jeu.
     *
     * @param  Builder<Document>  $query
     */
    public function scopeAvailableIn(Builder $query, Campaign $campaign): void
    {
        $query->where(function (Builder $q) use ($campaign) {
            $q->where('campaign_id', $campaign->getKey())
                ->orWhere('game_system_id', $campaign->game_system_id);

            if ($campaign->world_id !== null) {
                $q->orWhere('world_id', $campaign->world_id);
            }
        });
    }

    public function activityType(): string
    {
        return 'document';
    }

    public function activityLabel(): string
    {
        return $this->title;
    }

    public function activityScope(): array
    {
        return ['campaign_id' => $this->campaign_id, 'world_id' => $this->world_id, 'game_system_id' => $this->game_system_id];
    }
}
