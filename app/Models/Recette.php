<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Cahier de recette d'une version : une ligne par fonctionnalité, avec sa note. */
class Recette extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['tested_on' => 'date', 'score' => 'float', 'score_before' => 'float'];
    }

    /** @return HasMany<RecetteItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(RecetteItem::class)->orderBy('position');
    }
}
