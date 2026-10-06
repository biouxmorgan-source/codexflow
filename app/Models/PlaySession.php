<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Séance de jeu d'une campagne : début, fin, scène en cours et notes prises pendant la partie.
 */
#[Fillable(['number', 'title', 'started_at', 'ended_at'])]
class PlaySession extends Model
{
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<Scene, $this> */
    public function currentScene(): BelongsTo
    {
        return $this->belongsTo(Scene::class, 'current_scene_id');
    }

    /** @return HasMany<SessionNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(SessionNote::class);
    }

    public function isOpen(): bool
    {
        return $this->ended_at === null;
    }

    public function label(): string
    {
        return 'Session '.$this->number.($this->title ? ' · '.$this->title : '');
    }
}
