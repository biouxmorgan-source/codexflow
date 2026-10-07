<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Jeton d'une carte : un PJ, un PNJ, une créature, un objet… Lié à une fiche, il en prend
 * le nom et le portrait. Masqué, il garde sa place chez le MJ et disparaît de l'écran de table.
 */
#[Fillable(['label', 'color', 'x', 'y', 'size', 'hidden', 'show_label'])]
class MapToken extends Model
{
    public const SIZES = [0.5, 1, 2, 3, 4];

    public const COLORS = ['#b45309', '#b91c1c', '#15803d', '#1d4ed8', '#7e22ce', '#0f766e', '#57534e'];

    protected function casts(): array
    {
        return [
            'x' => 'float',
            'y' => 'float',
            'size' => 'float',
            'hidden' => 'boolean',
            'show_label' => 'boolean',
        ];
    }

    /** @return BelongsTo<TableMap, $this> */
    public function map(): BelongsTo
    {
        return $this->belongsTo(TableMap::class, 'table_map_id');
    }

    /** @return BelongsTo<Entity, $this> */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /** Initiales affichées quand le jeton n'a pas de portrait. */
    public function initials(): string
    {
        $words = preg_split('/\s+/u', trim($this->label)) ?: [];

        return mb_strtoupper(collect($words)->take(2)->map(fn (string $w) => mb_substr($w, 0, 1))->implode(''));
    }

    public function hasPortrait(): bool
    {
        return $this->entity?->hasImage() ?? false;
    }
}
