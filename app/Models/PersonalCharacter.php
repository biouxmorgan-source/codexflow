<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Feuille de personnage personnelle : un personnage joué hors de SagaWyn, rangé dans le compte
 * et visible de son seul propriétaire. Sans campagne ni jeu, ses champs sont libres, rangés dans
 * des blocs nommés que l'on déplace sur trois colonnes. Un bloc « champs » a des lignes « texte »
 * (label, value) ou « compteur » (label, value, max), avec une note et une case à cocher facultatives ;
 * un bloc « note » garde un texte libre. Les blocs se répartissent dans des onglets nommés.
 */
class PersonalCharacter extends Model
{
    public const DISK = 'local';

    public const MAX_FIELDS = 100;

    public const MAX_BLOCKS = 30;

    public const COLUMNS = ['left', 'middle', 'right'];

    public const KINDS = ['fields', 'note'];

    public const MAX_TABS = 12;

    /** Identifiant de l'onglet implicite d'une feuille qui n'en a encore créé aucun. */
    public const FIRST_TAB = 'main';

    protected $guarded = ['id'];

    protected $attributes = ['blocks' => '[]', 'tabs' => '[]'];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'tabs' => 'array',
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
     * Onglets remis en forme, toujours au moins un.
     *
     * @return list<array{id: string, name: string}>
     */
    public function tabList(): array
    {
        $tabs = [];
        foreach (array_filter($this->tabs ?? [], 'is_array') as $tab) {
            $id = (string) ($tab['id'] ?? '');
            if ($id !== '' && ! isset($tabs[$id])) {
                $tabs[$id] = ['id' => $id, 'name' => (string) ($tab['name'] ?? '')];
            }
        }

        return $tabs === [] ? [['id' => self::FIRST_TAB, 'name' => '']] : array_values($tabs);
    }

    /** Nom affiché d'un onglet : le sien, ou « Onglet n ». */
    public static function tabLabel(array $tab, int $index): string
    {
        return $tab['name'] !== '' ? $tab['name'] : __('Onglet :number', ['number' => $index + 1]);
    }

    /**
     * Blocs remis en forme : identifiant, titre, onglet, colonne et lignes complètes, quoi qu'en dise la base.
     *
     * @return list<array{id: string, kind: string, title: string, tab: string, column: string, text: string, rows: list<array{type: string, label: string, value: string|int, max: int|null, note: string, checkbox: bool, checked: bool}>}>
     */
    public function blockList(): array
    {
        $tabs = array_column($this->tabList(), 'id');

        return array_values(array_map(fn (array $block) => [
            'id' => (string) ($block['id'] ?? ''),
            'kind' => ($block['kind'] ?? null) === 'note' ? 'note' : 'fields',
            'title' => (string) ($block['title'] ?? ''),
            'tab' => in_array($block['tab'] ?? null, $tabs, true) ? $block['tab'] : $tabs[0],
            'column' => in_array($block['column'] ?? null, self::COLUMNS, true) ? $block['column'] : 'left',
            'text' => (string) ($block['text'] ?? ''),
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

    /** Nombre de colonnes à afficher dans un onglet : jusqu'à la dernière qui contient un bloc. */
    public function usedColumns(?string $tab = null): int
    {
        $last = 0;
        foreach ($this->blockList() as $block) {
            if ($tab !== null && $block['tab'] !== $tab) {
                continue;
            }

            $last = max($last, array_search($block['column'], self::COLUMNS, true) + 1);
        }

        return max(1, $last);
    }
}
