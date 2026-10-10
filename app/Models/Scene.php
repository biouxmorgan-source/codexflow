<?php

namespace App\Models;

use App\Enums\SceneStatus;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Scène préparée par le MJ. Tout son contenu relève de la zone MJ.
 */
#[Fillable(['chapter', 'name', 'description', 'status', 'position'])]
class Scene extends Model
{
    use RecordsActivity;

    protected $attributes = ['status' => 'planned'];

    protected function casts(): array
    {
        return ['status' => SceneStatus::class];
    }

    /** @return BelongsTo<Scenario, $this> */
    public function scenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class);
    }

    /** @return BelongsToMany<Entity, $this> */
    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'scene_entity')->withPivot('note', 'position')->orderByPivot('position');
    }

    /** @return HasMany<ToPlayItem, $this> */
    public function toPlayItems(): HasMany
    {
        return $this->hasMany(ToPlayItem::class)->orderBy('position')->orderBy('id');
    }

    /** @return BelongsToMany<Rule, $this> */
    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(Rule::class)->withPivot('position')->orderByPivot('position');
    }

    /** @return BelongsToMany<Document, $this> */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class)->withPivot('position')->orderByPivot('position');
    }

    /** @return BelongsToMany<AudioTrack, $this> */
    public function audioTracks(): BelongsToMany
    {
        return $this->belongsToMany(AudioTrack::class)->withPivot('position')->orderByPivot('position');
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderByRaw('lower(name)');
    }

    /** @return BelongsToMany<Secret, $this> */
    public function secrets(): BelongsToMany
    {
        return $this->belongsToMany(Secret::class, 'scene_secret')->orderBy('title');
    }

    public function activityType(): string
    {
        return 'scene';
    }

    public function activityLabel(): string
    {
        return $this->name;
    }

    public function activityScope(): array
    {
        return ['campaign_id' => $this->scenario?->campaign_id];
    }
}
