<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Note d'un joueur sur son personnage. Sa visibilité est vérifiée côté serveur
 * (scopeVisibleTo) ; elle n'entre jamais dans le journal, qui est lu par le MJ.
 */
class CharacterNote extends Model
{
    public const VISIBILITIES = [
        'private' => 'Moi seul',
        'gm' => 'Moi et le MJ',
        'players' => 'Certains joueurs (et le MJ)',
        'group' => 'Toute la table',
    ];

    protected $fillable = ['body', 'visibility'];

    /** @return array<string, string> libellés traduits des visibilités (mêmes clés que VISIBILITIES) */
    public static function visibilities(): array
    {
        return [
            'private' => __('Moi seul'),
            'gm' => __('Moi et le MJ'),
            'players' => __('Certains joueurs (et le MJ)'),
            'group' => __('Toute la table'),
        ];
    }

    /** @return BelongsTo<PlayerCharacter, $this> */
    public function character(): BelongsTo
    {
        return $this->belongsTo(PlayerCharacter::class, 'player_character_id');
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<PlaySession, $this> */
    public function playSession(): BelongsTo
    {
        return $this->belongsTo(PlaySession::class);
    }

    /** @return BelongsToMany<PlayerCharacter, $this> personnages avec qui la note est partagée */
    public function sharedWith(): BelongsToMany
    {
        return $this->belongsToMany(PlayerCharacter::class, 'character_note_shares');
    }

    /**
     * Notes d'une campagne qu'un utilisateur a le droit de lire.
     *
     * @param  Builder<CharacterNote>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user, Campaign $campaign): void
    {
        $query->whereHas('character', fn (Builder $q) => $q->where('campaign_id', $campaign->id));

        if ($campaign->isGameMaster($user)) {
            $query->where(fn (Builder $q) => $q->where('user_id', $user->id)->orWhere('visibility', '!=', 'private'));

            return;
        }

        $query->where(fn (Builder $q) => $q
            ->where('user_id', $user->id)
            ->orWhere('visibility', 'group')
            ->orWhere(fn (Builder $q) => $q
                ->where('visibility', 'players')
                ->whereHas('sharedWith', fn (Builder $q) => $q->where('player_characters.user_id', $user->id))));
    }

    /**
     * Notes que le joueur d'un personnage lit, vues par le MJ en mode « Voir comme… ».
     *
     * Même règle que scopeVisibleTo pour un joueur, à une exception : les notes « Moi seul »
     * restent privées, le MJ ne les lit jamais, même à travers le personnage. Personnage sans
     * joueur : les notes de toute la table et celles partagées avec ce personnage, soit ce que
     * lirait n'importe quel joueur à qui on le confierait.
     *
     * @param  Builder<CharacterNote>  $query
     */
    public function scopeVisibleToCharacter(Builder $query, PlayerCharacter $character): void
    {
        $playerId = $character->user_id;

        $query->whereHas('character', fn (Builder $q) => $q->where('campaign_id', $character->campaign_id))
            ->where('visibility', '!=', 'private')
            ->where(fn (Builder $q) => $q
                ->where('visibility', 'group')
                ->when($playerId, fn (Builder $q) => $q->orWhere('user_id', $playerId))
                ->orWhere(fn (Builder $q) => $q
                    ->where('visibility', 'players')
                    ->whereHas('sharedWith', fn (Builder $q) => $playerId
                        ? $q->where('player_characters.user_id', $playerId)
                        : $q->whereKey($character->id))));
    }

    public function visibilityLabel(): string
    {
        return self::visibilities()[$this->visibility] ?? $this->visibility;
    }
}
