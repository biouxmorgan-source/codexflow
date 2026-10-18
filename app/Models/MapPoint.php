<?php

namespace App\Models;

use App\Enums\SceneStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Point d'intérêt d'une carte : une ville, un donjon, le lieu d'une scène. Il mène à une
 * sous-carte (on clique, elle s'ouvre), à une scène (jouée ou à venir) ou à une fiche.
 * Tout reste au MJ ; seul le nom d'un point « visible à la table » apparaît sur l'écran de table.
 */
#[Fillable(['label', 'note', 'color', 'x', 'y', 'shown_at_table'])]
class MapPoint extends Model
{
    public const COLORS = ['#b45309', '#b91c1c', '#15803d', '#1d4ed8', '#7e22ce', '#0f766e', '#57534e'];

    protected function casts(): array
    {
        return [
            'x' => 'float',
            'y' => 'float',
            'shown_at_table' => 'boolean',
        ];
    }

    /** @return BelongsTo<TableMap, $this> */
    public function map(): BelongsTo
    {
        return $this->belongsTo(TableMap::class, 'table_map_id');
    }

    /** @return BelongsTo<TableMap, $this> sous-carte ouverte d'un clic */
    public function target(): BelongsTo
    {
        return $this->belongsTo(TableMap::class, 'target_map_id');
    }

    /** @return BelongsTo<Scene, $this> */
    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }

    /** @return BelongsTo<Entity, $this> */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /** Scène déjà jouée (ou passée), pour distinguer le chemin parcouru de ce qui reste à venir. */
    public function played(): bool
    {
        return in_array($this->scene?->status, [SceneStatus::Played, SceneStatus::Skipped], true);
    }

    /** La scène liée n'est pas encore jouée. */
    public function upcoming(): bool
    {
        return $this->scene !== null && ! $this->played();
    }
}
