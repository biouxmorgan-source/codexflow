<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Réponse d'une IA collée par le MJ. Elle ne change rien à la campagne :
 * ses propositions attendent que le MJ les accepte, les modifie ou les rejette.
 */
#[Fillable(['response'])]
class AiAnalysis extends Model
{
    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<PlaySession, $this> */
    public function playSession(): BelongsTo
    {
        return $this->belongsTo(PlaySession::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return HasMany<AiSuggestion, $this> */
    public function suggestions(): HasMany
    {
        return $this->hasMany(AiSuggestion::class);
    }
}
