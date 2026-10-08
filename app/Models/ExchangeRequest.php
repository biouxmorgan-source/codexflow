<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Échange proposé par un joueur, que le MJ accepte ou refuse. */
class ExchangeRequest extends Model
{
    protected $guarded = ['id'];

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<CharacterGrant, $this> */
    public function grant(): BelongsTo
    {
        return $this->belongsTo(CharacterGrant::class, 'character_grant_id');
    }

    /** @return BelongsTo<PlayerCharacter, $this> */
    public function from(): BelongsTo
    {
        return $this->belongsTo(PlayerCharacter::class, 'from_character_id');
    }

    /** @return BelongsTo<PlayerCharacter, $this> */
    public function to(): BelongsTo
    {
        return $this->belongsTo(PlayerCharacter::class, 'to_character_id');
    }

    /** « Cartouches ×4 », « Le code du coffre »… */
    public function label(): string
    {
        if ($this->grant->kind !== 'possession') {
            return $this->grant->label();
        }

        // La quantité proposée, pas celle de toute la pile.
        return $this->quantity > 1 ? $this->grant->title.' ×'.$this->quantity : $this->grant->title;
    }
}
