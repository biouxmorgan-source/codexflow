<?php

namespace App\Models;

use App\Enums\Zone;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Événement de la chronologie : histoire du monde, prévu (ce qui arrivera si les personnages
 * n'interviennent pas) ou joué. Les joueurs ne voient que les événements en zone publique.
 */
#[Fillable(['kind', 'date_label', 'title', 'description', 'zone'])]
class TimelineEvent extends Model
{
    public const KINDS = ['world', 'planned', 'played'];

    protected function casts(): array
    {
        return ['zone' => Zone::class];
    }

    /** @return array<string, string> */
    public static function kinds(): array
    {
        return [
            'world' => __('Histoire du monde'),
            'planned' => __('Prévu'),
            'played' => __('Joué'),
        ];
    }

    /** Zone proposée à la création : ce qui a été joué est connu de la table, le reste reste au MJ. */
    public static function defaultZone(string $kind): Zone
    {
        return $kind === 'played' ? Zone::Public : Zone::GameMaster;
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<PlaySession, $this> */
    public function playSession(): BelongsTo
    {
        return $this->belongsTo(PlaySession::class);
    }

    /** @return BelongsTo<Scene, $this> */
    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }

    /** @param Builder<TimelineEvent> $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    /** @param Builder<TimelineEvent> $query */
    public function scopeVisibleToPlayers(Builder $query): void
    {
        $query->where('zone', Zone::Public->value);
    }
}
