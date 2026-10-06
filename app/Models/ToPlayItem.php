<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Élément « À jouer » : une idée, un indice ou un événement que le MJ veut placer.
 */
#[Fillable(['body', 'position', 'done_at'])]
class ToPlayItem extends Model
{
    protected function casts(): array
    {
        return ['done_at' => 'datetime'];
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<Scene, $this> */
    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }

    /** @return BelongsTo<Rule, $this> */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class);
    }

    /** @param Builder<ToPlayItem> $query */
    public function scopePending(Builder $query): void
    {
        $query->whereNull('done_at');
    }
}
