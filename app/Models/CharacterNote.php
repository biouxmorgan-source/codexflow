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

    public function visibilityLabel(): string
    {
        return self::VISIBILITIES[$this->visibility] ?? $this->visibility;
    }
}
