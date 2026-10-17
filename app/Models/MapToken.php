<?php

namespace App\Models;

use App\Enums\CampaignRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Jeton d'une carte : un PJ, un PNJ, une créature, un objet… Lié à une fiche, il en prend
 * le nom et le portrait. Masqué, il garde sa place chez le MJ et disparaît de l'écran de table.
 * Sur l'écran partagé, un joueur déplace le jeton de son personnage et ceux que le MJ lui confie.
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

    /** @return BelongsTo<User, $this> joueur à qui le MJ confie le jeton (suivant, familier, ennemi contrôlé) */
    public function controller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'controller_id');
    }

    /**
     * Jetons visibles qu'un joueur peut déplacer sur l'écran partagé : ceux qu'on lui confie et
     * celui de son personnage. Le MJ, lui, les déplace tous.
     *
     * @param  iterable<MapToken>  $tokens  les jetons visibles de la carte affichée
     * @return list<int>
     */
    public static function movableIds(User $user, Campaign $campaign, iterable $tokens): array
    {
        // getAttribute : ici, « $token->hidden » serait la liste des attributs cachés du modèle.
        $tokens = collect($tokens)->reject(fn (MapToken $token) => $token->getAttribute('hidden'));

        if ($user->can('update', $campaign)) {
            return $tokens->pluck('id')->values()->all();
        }

        if ($campaign->roleOf($user) !== CampaignRole::Player || ! $campaign->table_shared) {
            return [];
        }

        $mine = $campaign->playerCharacters()->where('user_id', $user->id)->pluck('entity_id')->all();

        return $tokens
            ->filter(fn (MapToken $token) => $token->controller_id === $user->id || ($token->entity_id !== null && in_array($token->entity_id, $mine, true)))
            ->pluck('id')->values()->all();
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
