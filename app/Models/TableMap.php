<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Carte de la table : une image de la campagne, montrée sur l'écran de table, avec une grille
 * carrée facultative, des jetons facultatifs, des gabarits de zone et une règle temporaire. Pas une table virtuelle :
 * aucune mécanique de déplacement, aucun brouillard. Coordonnées en pixels de l'image d'origine.
 */
#[Fillable(['name', 'grid_enabled', 'grid_size', 'grid_offset_x', 'grid_offset_y', 'grid_color', 'scale_value', 'scale_unit'])]
class TableMap extends Model
{
    public const MAX_ZOOM = 8;

    protected function casts(): array
    {
        return [
            'grid_enabled' => 'boolean',
            'grid_size' => 'float',
            'grid_offset_x' => 'float',
            'grid_offset_y' => 'float',
            'scale_value' => 'float',
            'view_x' => 'float',
            'view_y' => 'float',
            'view_zoom' => 'float',
            'ruler' => 'array',
            'is_campaign_map' => 'boolean',
        ];
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<Document, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** @return HasMany<MapToken, $this> */
    public function tokens(): HasMany
    {
        return $this->hasMany(MapToken::class)->orderBy('id');
    }

    /** @return HasMany<MapTemplate, $this> gabarits de zone (cercle, cône…) */
    public function templates(): HasMany
    {
        return $this->hasMany(MapTemplate::class)->orderBy('id');
    }

    /** @return HasMany<MapPoint, $this> points d'intérêt posés sur la carte */
    public function points(): HasMany
    {
        return $this->hasMany(MapPoint::class)->orderBy('id');
    }

    /** @return HasMany<MapPoint, $this> points d'autres cartes qui mènent à celle-ci */
    public function pointsLeadingHere(): HasMany
    {
        return $this->hasMany(MapPoint::class, 'target_map_id');
    }

    /**
     * Chemin depuis la carte de campagne jusqu'à cette carte, sans elle : on remonte par les
     * points qui y mènent (le premier posé si plusieurs cartes y mènent). S'arrête sur une boucle.
     *
     * @return list<TableMap>
     */
    public function parents(): array
    {
        $chain = [];
        $seen = [$this->id];
        $current = $this;

        while (! $current->is_campaign_map) {
            $parent = TableMap::query()->where('campaign_id', $this->campaign_id)
                ->whereIn('id', MapPoint::query()->where('target_map_id', $current->id)->select('table_map_id'))
                ->orderByDesc('is_campaign_map')->orderBy('id')->first();

            if ($parent === null || in_array($parent->id, $seen, true)) {
                break;
            }

            array_unshift($chain, $parent);
            $seen[] = $parent->id;
            $current = $parent;
        }

        return $chain;
    }

    /** Taille de case proposée à la création : vingt cases dans la largeur. */
    public static function defaultGridSize(int $width): float
    {
        return max(10, round($width / 20));
    }

    /**
     * Partie de la carte montrée : x, y, largeur, hauteur (viewBox SVG).
     *
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    public function viewBox(): array
    {
        $zoom = max(1, min(self::MAX_ZOOM, $this->view_zoom ?: 1));
        $w = $this->width / $zoom;
        $h = $this->height / $zoom;

        return [round(($this->view_x ?? $this->width / 2) - $w / 2, 2), round(($this->view_y ?? $this->height / 2) - $h / 2, 2), round($w, 2), round($h, 2)];
    }

    /** Recentre la vue, zoom borné entre la carte entière et ×8, centre gardé sur la carte. */
    public function setView(float $x, float $y, float $zoom): void
    {
        $this->view_zoom = round(max(1, min(self::MAX_ZOOM, $zoom)), 3);
        $this->view_x = round(max(0, min($this->width, $x)), 2);
        $this->view_y = round(max(0, min($this->height, $y)), 2);
    }

    /** Diamètre d'un jeton, en pixels de la carte. */
    public function tokenDiameter(MapToken $token): float
    {
        return max(4, $token->size * $this->grid_size);
    }

    /**
     * Longueur de la règle : « 4,5 m » si une échelle est donnée, sinon « 3 cases ».
     * Les distances sont à vol d'oiseau, en cases de la grille.
     */
    public function distanceLabel(float $x1, float $y1, float $x2, float $y2): string
    {
        return $this->lengthLabel(hypot($x2 - $x1, $y2 - $y1) / max(1, $this->grid_size));
    }

    /** Une longueur en cases : « 4,5 m » si une échelle est donnée, sinon « 3 cases ». */
    public function lengthLabel(float $cells): string
    {
        if ($this->scale_value) {
            return trim(self::number($cells * $this->scale_value).' '.($this->scale_unit ?: trans_choice('case|cases', $cells * $this->scale_value < 2 ? 1 : 2)));
        }

        return trans_choice(':count case|:count cases', $cells < 2 ? 1 : 2, ['count' => self::number($cells)]);
    }

    /** La règle temporaire s'efface d'elle-même après ce délai, sur tous les écrans. */
    public const RULER_SECONDS = 15;

    /** @return array{x1: float, y1: float, x2: float, y2: float, at?: int}|null la règle encore visible */
    public function activeRuler(): ?array
    {
        return $this->rulerSecondsLeft() > 0 ? $this->ruler : null;
    }

    public function rulerSecondsLeft(): int
    {
        $at = $this->ruler['at'] ?? null;

        return $at === null ? 0 : max(0, $at + self::RULER_SECONDS - now()->timestamp);
    }

    public function rulerLabel(): ?string
    {
        $r = $this->activeRuler();

        return $r === null ? null : $this->distanceLabel($r['x1'], $r['y1'], $r['x2'], $r['y2']);
    }

    private static function number(float $value): string
    {
        $rounded = round($value, 1);

        return floor($rounded) == $rounded ? (string) (int) $rounded : number_format($rounded, 1, in_array(app()->getLocale(), ['en'], true) ? '.' : ',', '');
    }
}
