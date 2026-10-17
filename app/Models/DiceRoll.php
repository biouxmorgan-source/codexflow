<?php

namespace App\Models;

use App\Support\Dice\DiceFormula;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un lancer de dés fait à la table : par le MJ ou un joueur (au nom de son personnage), libre ou
 * en réponse à une demande du MJ. Le résultat est tiré et lu côté serveur, puis gardé tel quel.
 */
class DiceRoll extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'formula' => 'array',
            'result' => 'array',
        ];
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<PlayerCharacter, $this> */
    public function character(): BelongsTo
    {
        return $this->belongsTo(PlayerCharacter::class, 'player_character_id');
    }

    /** @return BelongsTo<DiceRequest, $this> */
    public function request(): BelongsTo
    {
        return $this->belongsTo(DiceRequest::class, 'dice_request_id');
    }

    public function formula(): DiceFormula
    {
        return DiceFormula::fromArray($this->formula ?? []);
    }

    /** Qui a lancé : le personnage, sinon la personne (le MJ). */
    public function rollerName(): string
    {
        return $this->character?->entity?->name ?? $this->user?->name ?? __('MJ');
    }
}
