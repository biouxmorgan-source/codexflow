<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Feuille de personnage personnelle : un personnage joué hors de SagaWyn, rangé dans le compte
 * et visible de son seul propriétaire. Sans campagne ni jeu, ses champs sont libres, rangés dans
 * des blocs nommés que l'on déplace (colonne gauche ou droite) : chaque ligne est un « texte »
 * (label, value) ou un « compteur » (label, value, max), avec une note et une case à cocher facultatives.
 */
class PersonalCharacter extends Model
{
    public const DISK = 'local';

    public const MAX_FIELDS = 100;

    public const MAX_BLOCKS = 30;

    public const COLUMNS = ['left', 'right'];

    protected $guarded = ['id'];

    protected $attributes = ['blocks' => '[]'];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
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

    /**
     * Blocs remis en forme : identifiant, titre, colonne et lignes complètes, quoi qu'en dise la base.
     *
     * @return list<array{id: string, title: string, column: string, rows: list<array{type: string, label: string, value: string|int, max: int|null, note: string, checkbox: bool, checked: bool}>}>
     */
    public function blockList(): array
    {
        return array_values(array_map(fn (array $block) => [
            'id' => (string) ($block['id'] ?? ''),
            'title' => (string) ($block['title'] ?? ''),
            'column' => in_array($block['column'] ?? null, self::COLUMNS, true) ? $block['column'] : 'left',
            'rows' => array_values(array_map(fn (array $row) => [
                'type' => ($row['type'] ?? null) === 'counter' ? 'counter' : 'text',
                'label' => (string) ($row['label'] ?? ''),
                'value' => ($row['type'] ?? null) === 'counter' ? (int) ($row['value'] ?? 0) : (string) ($row['value'] ?? ''),
                'max' => isset($row['max']) ? (int) $row['max'] : null,
                'note' => (string) ($row['note'] ?? ''),
                'checkbox' => (bool) ($row['checkbox'] ?? false),
                'checked' => (bool) ($row['checked'] ?? false),
            ], array_filter($block['rows'] ?? [], 'is_array'))),
        ], array_filter($this->blocks ?? [], 'is_array')));
    }
}
