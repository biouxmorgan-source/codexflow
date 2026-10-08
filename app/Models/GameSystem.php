<?php

namespace App\Models;

use Database\Factories\GameSystemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'image_path'])]
class GameSystem extends Model
{
    /** @use HasFactory<GameSystemFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return HasMany<Campaign, $this> */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /** @return HasMany<FieldDefinition, $this> */
    public function fieldDefinitions(): HasMany
    {
        return $this->hasMany(FieldDefinition::class);
    }

    /** @return HasMany<Rule, $this> règles de référence, communes à toutes ses campagnes */
    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class);
    }

    /** @return HasMany<Document, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
