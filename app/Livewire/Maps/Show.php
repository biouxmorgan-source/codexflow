<?php

namespace App\Livewire\Maps;

use App\Livewire\Concerns\SuggestsEntities;
use App\Models\Campaign;
use App\Models\MapToken;
use App\Models\TableMap;
use App\Support\TableDisplay;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;

/**
 * Préparation et pilotage d'une carte : ce que le MJ voit ici (cadrage, grille, jetons visibles,
 * règle) est ce que montre l'écran de table quand la carte y est affichée. Les jetons masqués
 * n'apparaissent qu'ici.
 */
class Show extends Component
{
    use SuggestsEntities;

    #[Locked]
    public Campaign $campaign;

    #[Locked]
    public TableMap $map;

    public string $name = '';

    public bool $gridEnabled = false;

    public string $gridSize = '';

    public string $gridOffsetX = '0';

    public string $gridOffsetY = '0';

    public string $gridColor = '#1c1917';

    public string $scaleValue = '';

    public string $scaleUnit = '';

    public ?int $tokenEntityId = null;

    public string $tokenLabel = '';

    public function mount(Campaign $campaign, TableMap $map): void
    {
        $this->authorize('update', $campaign);
        abort_unless($map->campaign_id === $campaign->id, 404);

        $this->fill([
            'name' => $map->name,
            'gridEnabled' => $map->grid_enabled,
            'gridSize' => self::number($map->grid_size),
            'gridOffsetX' => self::number($map->grid_offset_x),
            'gridOffsetY' => self::number($map->grid_offset_y),
            'gridColor' => $map->grid_color,
            'scaleValue' => $map->scale_value === null ? '' : self::number($map->scale_value),
            'scaleUnit' => (string) $map->scale_unit,
        ]);
    }

    /** Grille et échelle enregistrées à chaque réglage : l'écran de table suit en direct. */
    public function updated(string $property): void
    {
        if (! in_array($property, ['name', 'gridEnabled', 'gridSize', 'gridOffsetX', 'gridOffsetY', 'gridColor', 'scaleValue', 'scaleUnit'], true)) {
            return;
        }

        $this->authorize('update', $this->campaign);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'gridSize' => ['required', 'numeric', 'min:5', 'max:2000'],
            'gridOffsetX' => ['required', 'numeric', 'min:-2000', 'max:2000'],
            'gridOffsetY' => ['required', 'numeric', 'min:-2000', 'max:2000'],
            'gridColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'scaleValue' => ['nullable', 'numeric', 'gt:0', 'max:100000'],
            'scaleUnit' => ['nullable', 'string', 'max:20'],
        ], attributes: [
            'name' => __('nom'), 'gridSize' => __('taille des cases'), 'gridOffsetX' => __('décalage horizontal'),
            'gridOffsetY' => __('décalage vertical'), 'gridColor' => __('couleur'), 'scaleValue' => __('échelle'), 'scaleUnit' => __('unité'),
        ]);

        $this->map->update([
            'name' => trim($this->name),
            'grid_enabled' => $this->gridEnabled,
            'grid_size' => (float) $this->gridSize,
            'grid_offset_x' => (float) $this->gridOffsetX,
            'grid_offset_y' => (float) $this->gridOffsetY,
            'grid_color' => strtolower($this->gridColor),
            'scale_value' => $this->scaleValue === '' ? null : (float) $this->scaleValue,
            'scale_unit' => trim($this->scaleUnit) ?: null,
        ]);

        TableDisplay::mapChanged($this->map);
    }

    /** Cadrage choisi par le MJ (déplacement, molette) : c'est celui de l'écran de table. */
    #[Renderless]
    public function setView(float $x, float $y, float $zoom): void
    {
        $this->authorize('update', $this->campaign);

        $this->map->setView($x, $y, $zoom);
        $this->map->save();
        TableDisplay::mapChanged($this->map);
    }

    /** Recentre sur la carte entière. */
    public function resetView(): void
    {
        $this->authorize('update', $this->campaign);

        $this->map->setView($this->map->width / 2, $this->map->height / 2, 1);
        $this->map->save();
        TableDisplay::mapChanged($this->map);
    }

    public function addToken(): void
    {
        $this->authorize('update', $this->campaign);

        $entity = $this->tokenEntityId ? $this->campaign->availableEntities()->find($this->tokenEntityId) : null;
        $label = trim($this->tokenLabel) ?: $entity?->name;

        if ($label === null || $label === '') {
            $this->addError('tokenLabel', __('Choisissez une fiche ou donnez un nom au jeton.'));

            return;
        }

        [$vx, $vy, $vw, $vh] = $this->map->viewBox();
        $count = $this->map->tokens()->count();

        // Au centre de la vue, légèrement décalé pour ne pas empiler les jetons.
        $token = new MapToken([
            'label' => mb_substr($label, 0, 100),
            'color' => MapToken::COLORS[$count % count(MapToken::COLORS)],
            'x' => round($vx + $vw / 2 + ($count % 5) * $this->map->grid_size * 0.6, 2),
            'y' => round($vy + $vh / 2 + intdiv($count % 25, 5) * $this->map->grid_size * 0.6, 2),
            'size' => 1,
            'hidden' => true,
            'show_label' => true,
        ]);
        $token->entity()->associate($entity);
        $this->map->tokens()->save($token);

        $this->reset('tokenEntityId', 'tokenLabel');
        $this->dispatch('token-added');
    }

    #[Renderless]
    public function moveToken(int $id, float $x, float $y): void
    {
        $token = $this->token($id);
        $token->update([
            'x' => round(max(0, min($this->map->width, $x)), 2),
            'y' => round(max(0, min($this->map->height, $y)), 2),
        ]);

        if (! $token->hidden) {
            TableDisplay::mapChanged($this->map);
        }
    }

    public function toggleToken(int $id): void
    {
        $token = $this->token($id);
        $token->update(['hidden' => ! $token->hidden]);
        TableDisplay::mapChanged($this->map);
    }

    /** Tous les jetons apparaissent, ou disparaissent, d'un coup. */
    public function setAllHidden(bool $hidden): void
    {
        $this->authorize('update', $this->campaign);

        $this->map->tokens()->update(['hidden' => $hidden]);
        TableDisplay::mapChanged($this->map);
    }

    public function toggleLabel(int $id): void
    {
        $token = $this->token($id);
        $token->update(['show_label' => ! $token->show_label]);
        TableDisplay::mapChanged($this->map);
    }

    public function resizeToken(int $id, string $size): void
    {
        $token = $this->token($id);
        validator(['size' => $size], ['size' => ['required', Rule::in(array_map('strval', MapToken::SIZES))]])->validate();

        $token->update(['size' => (float) $size]);
        TableDisplay::mapChanged($this->map);
    }

    /** Couleur suivante dans la palette. */
    public function recolorToken(int $id): void
    {
        $token = $this->token($id);
        $index = array_search($token->color, MapToken::COLORS, true);
        $token->update(['color' => MapToken::COLORS[($index === false ? 0 : $index + 1) % count(MapToken::COLORS)]]);
        TableDisplay::mapChanged($this->map);
    }

    public function deleteToken(int $id): void
    {
        $this->token($id)->delete();
        TableDisplay::mapChanged($this->map);
    }

    public function setRuler(float $x1, float $y1, float $x2, float $y2): void
    {
        $this->authorize('update', $this->campaign);

        $clamp = fn (float $v, int $max) => round(max(0, min($max, $v)), 2);
        $this->map->forceFill(['ruler' => [
            'x1' => $clamp($x1, $this->map->width), 'y1' => $clamp($y1, $this->map->height),
            'x2' => $clamp($x2, $this->map->width), 'y2' => $clamp($y2, $this->map->height),
            'at' => now()->timestamp,
        ]])->save();
        TableDisplay::mapChanged($this->map);
    }

    public function clearRuler(): void
    {
        $this->authorize('update', $this->campaign);

        $this->map->forceFill(['ruler' => null])->save();
        TableDisplay::mapChanged($this->map);
    }

    private function token(int $id): MapToken
    {
        $this->authorize('update', $this->campaign);

        return $this->map->tokens()->findOrFail($id);
    }

    private static function number(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    public function render()
    {
        $this->map->load(['document', 'tokens.entity']);

        return view('livewire.maps.show', [
            'tokens' => $this->map->tokens,
        ])->title(__(':map · Cartes · :name', ['map' => $this->map->name, 'name' => $this->campaign->name]));
    }
}
