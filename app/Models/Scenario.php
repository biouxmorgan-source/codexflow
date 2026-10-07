<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'summary', 'position'])]
class Scenario extends Model
{
    use RecordsActivity;

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return HasMany<Scene, $this> */
    public function scenes(): HasMany
    {
        return $this->hasMany(Scene::class)->orderBy('position')->orderBy('id');
    }

    public function activityType(): string
    {
        return 'scenario';
    }

    public function activityLabel(): string
    {
        return $this->name;
    }

    public function activityScope(): array
    {
        return ['campaign_id' => $this->campaign_id];
    }
}
