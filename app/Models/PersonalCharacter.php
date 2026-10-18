<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Feuille de personnage personnelle : un personnage joué hors de SagaWyn, rangé dans le compte
 * et visible de son seul propriétaire. Sans campagne ni jeu, ses champs sont libres : une liste
 * de lignes « texte » (label, value) ou « compteur » (label, value, max).
 */
class PersonalCharacter extends Model
{
    public const DISK = 'local';

    public const MAX_FIELDS = 100;

    protected $guarded = ['id'];

    protected $attributes = ['fields' => '[]'];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'portrait_size' => 'integer',
            'sheet_size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (PersonalCharacter $character) {
            $character->deletePortrait();
            $character->deleteSheet();
        });
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function hasPortrait(): bool
    {
        return $this->portrait_path !== null;
    }

    public function hasSheet(): bool
    {
        return $this->sheet_path !== null;
    }

    public function deletePortrait(): void
    {
        if ($this->portrait_path !== null) {
            Storage::disk(self::DISK)->delete($this->portrait_path);
        }
    }

    public function deleteSheet(): void
    {
        if ($this->sheet_path !== null) {
            Storage::disk(self::DISK)->delete($this->sheet_path);
        }
    }

    /** @return list<array{type: string, label: string, value: string|int, max?: int|null}> */
    public function fieldRows(): array
    {
        return array_values(array_filter($this->fields ?? [], 'is_array'));
    }
}
