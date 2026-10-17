<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * Gabarit de zone : une forme posée sur la carte (cercle, carré, rectangle, cône, ligne) pour
 * voir quels jetons un sort ou une explosion atteint. Le cercle, le carré et le rectangle sont
 * centrés sur (x, y) ; le cône et la ligne partent de (x, y) dans la direction de l'angle.
 * Le cône s'ouvre autant qu'il est long. Un jeton est touché quand son centre est dans la forme.
 */
#[Fillable(['shape', 'size', 'size2', 'x', 'y', 'angle', 'color', 'hidden'])]
class MapTemplate extends Model
{
    public const SHAPES = ['circle', 'square', 'rectangle', 'cone', 'line'];

    public const COLORS = ['#b91c1c', '#1d4ed8', '#15803d', '#7e22ce', '#b45309'];

    /** Dimensions possibles, en cases. */
    public const MAX_SIZE = 200;

    protected function casts(): array
    {
        return [
            'size' => 'float',
            'size2' => 'float',
            'x' => 'float',
            'y' => 'float',
            'angle' => 'float',
            'hidden' => 'boolean',
        ];
    }

    /** @return BelongsTo<TableMap, $this> */
    public function map(): BelongsTo
    {
        return $this->belongsTo(TableMap::class, 'table_map_id');
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'circle' => __('Cercle'),
            'square' => __('Carré'),
            'rectangle' => __('Rectangle'),
            'cone' => __('Cône'),
            'line' => __('Ligne'),
        ];
    }

    /** Une seconde dimension (hauteur du rectangle, largeur de la ligne) ? */
    public static function hasSecondSize(string $shape): bool
    {
        return in_array($shape, ['rectangle', 'line'], true);
    }

    /** Peut-on la faire tourner ? Le cercle, non. */
    public function rotates(): bool
    {
        return $this->shape !== 'circle';
    }

    /** « Cercle, rayon 4 cases », « Cône, 9 m »… selon l'échelle de la carte. */
    public function describe(TableMap $map): string
    {
        $length = fn (float $cells) => $map->lengthLabel($cells);

        return match ($this->shape) {
            'circle' => __(':shape, rayon :size', ['shape' => __('Cercle'), 'size' => $length($this->size)]),
            'square' => __(':shape, côté :size', ['shape' => __('Carré'), 'size' => $length($this->size)]),
            'rectangle' => __(':shape, :width × :height', ['shape' => __('Rectangle'), 'width' => $length($this->size), 'height' => $length($this->size2 ?? $this->size)]),
            'cone' => __(':shape, :size', ['shape' => __('Cône'), 'size' => $length($this->size)]),
            default => __(':shape, :size × :width', ['shape' => __('Ligne'), 'size' => $length($this->size), 'width' => $length($this->size2 ?? 1)]),
        };
    }

    /**
     * Contour SVG de la forme, en pixels de la carte, avant déplacement en (x, y) et rotation.
     *
     * @return array{type: 'circle', r: float}|array{type: 'polygon', points: string}
     */
    public function outline(float $cell): array
    {
        $a = $this->size * $cell;
        $b = ($this->size2 ?? ($this->shape === 'line' ? 1 : $this->size)) * $cell;

        $points = match ($this->shape) {
            'circle' => null,
            'square' => [[-$a / 2, -$a / 2], [$a / 2, -$a / 2], [$a / 2, $a / 2], [-$a / 2, $a / 2]],
            'rectangle' => [[-$a / 2, -$b / 2], [$a / 2, -$b / 2], [$a / 2, $b / 2], [-$a / 2, $b / 2]],
            'cone' => [[0, 0], [$a, -$a / 2], [$a, $a / 2]],
            default => [[0, -$b / 2], [$a, -$b / 2], [$a, $b / 2], [0, $b / 2]],
        };

        return $points === null
            ? ['type' => 'circle', 'r' => round($a, 2)]
            : ['type' => 'polygon', 'points' => collect($points)->map(fn (array $p) => round($p[0], 2).','.round($p[1], 2))->implode(' ')];
    }

    /** Le point (en pixels de la carte) est-il dans la forme ? */
    public function contains(float $px, float $py, float $cell): bool
    {
        $dx = $px - $this->x;
        $dy = $py - $this->y;

        if ($this->shape === 'circle') {
            return hypot($dx, $dy) <= $this->size * $cell + 0.01;
        }

        // Dans le repère de la forme : u le long de l'angle, v en travers.
        $rad = deg2rad($this->angle);
        $u = $dx * cos($rad) + $dy * sin($rad);
        $v = -$dx * sin($rad) + $dy * cos($rad);
        $a = $this->size * $cell;
        $b = ($this->size2 ?? ($this->shape === 'line' ? 1 : $this->size)) * $cell;
        $e = 0.01;

        return match ($this->shape) {
            'square', 'rectangle' => abs($u) <= $a / 2 + $e && abs($v) <= ($this->shape === 'square' ? $a : $b) / 2 + $e,
            'cone' => $u >= -$e && $u <= $a + $e && abs($v) <= $u / 2 + $e,
            default => $u >= -$e && $u <= $a + $e && abs($v) <= $b / 2 + $e,
        };
    }

    /**
     * Jetons dont le centre est dans le gabarit.
     *
     * @param  iterable<MapToken>  $tokens
     * @return Collection<int, MapToken>
     */
    public function caught(iterable $tokens, float $cell): Collection
    {
        return collect($tokens)->filter(fn (MapToken $token) => $this->contains($token->x, $token->y, $cell))->values();
    }
}
