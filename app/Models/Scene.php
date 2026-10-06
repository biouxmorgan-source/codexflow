<?php

namespace App\Models;

use App\Enums\SceneStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Scène préparée par le MJ. Tout son contenu relève de la zone MJ.
 */
#[Fillable(['chapter', 'name', 'description', 'status', 'position'])]
class Scene extends Model
{
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
}
