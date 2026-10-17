<?php

namespace App\Models;

use App\Support\Dice\DiceFormula;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Le MJ demande un lancer à toute la table ou à certains personnages, à la vue de tous ou en
 * discret (seuls le MJ et le lanceur voient le résultat). Chaque personnage visé lance une fois.
 */
class DiceRequest extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'formula' => 'array',
            'character_ids' => 'array',
            'closed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return HasMany<DiceRoll, $this> */
    public function rolls(): HasMany
    {
        return $this->hasMany(DiceRoll::class);
    }

    public function formula(): DiceFormula
    {
        return DiceFormula::fromArray($this->formula ?? []);
    }

    public function isOpen(): bool
    {
        return $this->closed_at === null;
    }

    /** @return list<int> */
    public function characterIds(): array
    {
        return array_map('intval', $this->character_ids ?? []);
    }
}
