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

    /** Mêmes valeurs par défaut qu'en base, pour un personnage tout juste créé. */
    protected $attributes = ['is_active' => true, 'locked' => false];

    /** Le joueur vient de changer : sa période commence à l'enregistrement. */
    private bool $playerChanged = false;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'locked' => 'boolean',
            'sheet_size' => 'integer',
            'assigned_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Confié à un (nouveau) joueur : sa conversation avec le MJ commence maintenant.
        static::saving(function (PlayerCharacter $character) {
            if ($character->isDirty('user_id')) {
                $character->playerChanged = true;
                $character->assigned_at = $character->user_id === null ? null : now();

                // Le joueur qu'on remplace ou qu'on libère est retenu, pour lui rendre son personnage s'il revient.
                $previous = $character->getOriginal('user_id');
                if ($previous !== null) {
                    $character->previous_user_id = $previous;
                }
                if ($character->user_id !== null && $character->user_id === $character->previous_user_id) {
                    $character->previous_user_id = null;
                }
            }
        });
        static::saved(function (PlayerCharacter $character) {
            if ($character->playerChanged) {
                $character->playerChanged = false;
                $character->recordAssignment();
            }
        });
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

    /** @return BelongsTo<User, $this> dernier joueur, quand le personnage a été libéré ou confié à un autre */
    public function previousPlayer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'previous_user_id');
    }

    /** @return HasMany<CharacterAssignment, $this> joueurs successifs, du plus récent au plus ancien */
    public function assignments(): HasMany
    {
        return $this->hasMany(CharacterAssignment::class)->orderByDesc('started_at')->orderByDesc('id');
    }

    /** Clôt la période du joueur précédent et ouvre celle du nouveau. */
    public function recordAssignment(): void
    {
        $now = $this->assigned_at ?? now();
        $this->assignments()->whereNull('ended_at')->where('user_id', '!=', (int) $this->user_id)->update(['ended_at' => $now]);

        if ($this->user_id !== null && ! $this->assignments()->whereNull('ended_at')->exists()) {
            $this->assignments()->create(['user_id' => $this->user_id, 'started_at' => $now]);
        }
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

    /** @return HasMany<ExchangeRequest, $this> échanges proposés par ce personnage, en attente du MJ */
    public function exchangeRequests(): HasMany
    {
        return $this->hasMany(ExchangeRequest::class, 'from_character_id');
    }
}
