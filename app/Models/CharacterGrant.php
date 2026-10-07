<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Élément révélé ou donné à un personnage par le MJ.
 *
 * - entity : fiche révélée, le joueur en voit la zone publique (Connaissances) ;
 * - information : texte libre (Connaissances) ;
 * - possession : objet, avec quantité facultative (Possessions) ;
 * - document : PDF ou image de la bibliothèque (Documents) ;
 * - rule : règle publique ouverte au personnage (Règles), sans les notes MJ.
 */
class CharacterGrant extends Model
{
    public const KINDS = [
        'entity' => 'Fiche',
        'information' => 'Information',
        'possession' => 'Objet',
        'document' => 'Document',
        'rule' => 'Règle',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }

    /** @return BelongsTo<PlayerCharacter, $this> */
    public function character(): BelongsTo
    {
        return $this->belongsTo(PlayerCharacter::class, 'player_character_id');
    }

    /** @return BelongsTo<Entity, $this> */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /** @return BelongsTo<Document, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** @return BelongsTo<Rule, $this> */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class);
    }

    /** @return BelongsTo<User, $this> */
    public function giver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function label(): string
    {
        return match ($this->kind) {
            'entity' => (string) $this->entity?->name,
            'document' => (string) $this->document?->title,
            'rule' => (string) $this->rule?->title,
            'possession' => $this->quantity && $this->quantity > 1 ? $this->title.' ×'.$this->quantity : (string) $this->title,
            default => (string) $this->title,
        };
    }
}
