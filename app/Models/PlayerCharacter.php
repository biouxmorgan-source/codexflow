<?php

namespace App\Models;

use App\Support\Live;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Personnage joueur : une fiche propre à la campagne (nom, portrait, zones publique et MJ,
 * champs du jeu) confiée à un joueur, avec sa feuille PDF. Les connaissances et possessions
 * appartiendront au personnage, pas au joueur.
 */
class PlayerCharacter extends Model
{
    public const DISK = 'local';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'locked' => 'boolean',
            'sheet_size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(fn (PlayerCharacter $character) => $character->deleteSheet());
        static::updated(fn (PlayerCharacter $character) => Live::character($character->id));
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<Entity, $this> */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /** @return BelongsTo<User, $this> */
    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return HasMany<CharacterGrant, $this> */
    public function grants(): HasMany
    {
        return $this->hasMany(CharacterGrant::class)->latest('id');
    }

    /** @return HasMany<CharacterNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(CharacterNote::class);
    }

    /** @return HasMany<Message, $this> conversation privée avec le MJ */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /** @return HasMany<ToPlayItem, $this> intentions du joueur */
    public function intentions(): HasMany
    {
        return $this->hasMany(ToPlayItem::class);
    }

    /** Le joueur connaît-il cette fiche (la sienne ou une fiche révélée) ? */
    public function knows(Entity $entity): bool
    {
        return $entity->id === $this->entity_id
            || $this->grants()->where('kind', 'entity')->where('entity_id', $entity->id)->exists();
    }

    /** @param Builder<PlayerCharacter> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function isPlayedBy(User $user): bool
    {
        return $this->user_id !== null && $this->user_id === $user->getKey();
    }

    public function hasSheet(): bool
    {
        return $this->sheet_path !== null;
    }

    public function deleteSheet(): void
    {
        if ($this->sheet_path !== null) {
            Storage::disk(self::DISK)->delete($this->sheet_path);
        }
    }
}
