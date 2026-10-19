<?php

namespace App\Models;

use App\Enums\SceneStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Point d'intérêt d'une carte : une ville, un donjon, le lieu d'une scène. Il mène à une
 * sous-carte (on clique, elle s'ouvre), et se relie à plusieurs scènes (jouées ou à venir) et fiches.
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

    /** @return BelongsToMany<Scene, $this> scènes qui se jouent ici */
    public function scenes(): BelongsToMany
    {
        return $this->belongsToMany(Scene::class, 'map_point_scene')->withPivot('position')->orderByPivot('position')->orderBy('scenes.position');
    }

    /** @return BelongsToMany<Entity, $this> fiches du lieu : le lieu lui-même, ses PNJ, ses objets */
    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'map_point_entity')->withPivot('position')->orderByPivot('position')->orderBy('entities.name');
    }

    /**
     * Où en est le lieu : « in_progress » si une de ses scènes est en cours, « played » si toutes
     * sont jouées (ou passées), « upcoming » s'il en reste à jouer, null sans scène.
     */
    public function progress(): ?string
    {
        $statuses = $this->scenes->pluck('status');

        return match (true) {
            $statuses->isEmpty() => null,
            $statuses->contains(SceneStatus::InProgress) => 'in_progress',
            $statuses->every(fn (SceneStatus $status) => in_array($status, [SceneStatus::Played, SceneStatus::Skipped], true)) => 'played',
            default => 'upcoming',
        };
    }

    /** Toutes les scènes du lieu sont jouées : le chemin parcouru. */
    public function played(): bool
    {
        return $this->progress() === 'played';
    }

    /** Il reste au moins une scène à jouer ici. */
    public function upcoming(): bool
    {
        return in_array($this->progress(), ['upcoming', 'in_progress'], true);
    }

    /** Ajoute en dernier, sans doublon. */
    public function link(string $relation, int $id): void
    {
        if (! $this->{$relation}()->whereKey($id)->exists()) {
            $this->{$relation}()->attach($id, ['position' => $this->{$relation}()->count()]);
        }
    }
}
