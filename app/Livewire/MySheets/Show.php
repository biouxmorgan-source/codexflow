<?php

namespace App\Livewire\MySheets;

use App\Enums\FieldType;
use App\Models\PersonalCharacter;
use App\Support\Plans\Plans;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Une feuille de personnage personnelle : identité, portrait, feuille PDF, champs libres rangés
 * en blocs déplaçables sur trois colonnes (textes et compteurs à − / +, avec note et case à cocher)
 * et autant de blocs de notes que voulu.
 * Visible de son seul propriétaire.
 */
class Show extends Component
{
    use WithFileUploads;

    #[Locked]
    public PersonalCharacter $character;

    public bool $editing = false;

    public string $name = '';

    public string $gameName = '';

    public string $tableName = '';

    /** @var list<array{id: string, kind: string, title: string, column: string, text: string, rows: list<array{type: string, label: string, value: string, max: string, note: string, checkbox: bool, checked: bool}>}> */
    public array $blocks = [];

    /** Les poignées des blocs ne s'affichent qu'en mode « Organiser ». */
    public bool $arranging = false;

    public ?TemporaryUploadedFile $portrait = null;

    public ?TemporaryUploadedFile $sheet = null;

    public function mount(PersonalCharacter $personalCharacter): void
    {
        $this->authorize('view', $personalCharacter);
        $this->character = $personalCharacter;
    }

    public function edit(): void
    {
        $this->resetValidation();
        $this->arranging = false;
        $this->name = $this->character->name;
        $this->gameName = (string) $this->character->game;
        $this->tableName = (string) $this->character->table_name;
        $this->blocks = array_map(fn (array $block) => [
            ...$block,
            'rows' => array_map(fn (array $row) => [
                ...$row,
                'value' => (string) $row['value'],
                'max' => $row['max'] === null ? '' : (string) $row['max'],
            ], $block['rows']),
        ], $this->character->blockList());

        if ($this->blocks === []) {
            $this->addBlock();
        }

        $this->editing = true;
    }

    public function cancel(): void
    {
        $this->resetValidation();
        $this->reset('editing', 'blocks');
    }

    public function addBlock(string $kind = 'fields'): void
    {
        abort_unless(in_array($kind, PersonalCharacter::KINDS, true) && count($this->blocks) < PersonalCharacter::MAX_BLOCKS, 422);

        $this->blocks[] = ['id' => Str::lower(Str::random(10)), 'kind' => $kind, 'title' => $kind === 'note' ? __('Notes') : '', 'column' => 'left', 'text' => '', 'rows' => []];
    }

    public function removeBlock(int $block): void
    {
        unset($this->blocks[$block]);
        $this->blocks = array_values($this->blocks);
    }

    public function addRow(int $block, string $type): void
    {
        abort_unless(($this->blocks[$block]['kind'] ?? null) === 'fields' && in_array($type, ['text', 'counter'], true) && $this->rowCount() < PersonalCharacter::MAX_FIELDS, 422);

        $this->blocks[$block]['rows'][] = ['type' => $type, 'label' => '', 'value' => $type === 'counter' ? '0' : '', 'max' => '', 'note' => '', 'checkbox' => false, 'checked' => false];
    }

    public function removeRow(int $block, int $row): void
    {
        unset($this->blocks[$block]['rows'][$row]);
        $this->blocks[$block]['rows'] = array_values($this->blocks[$block]['rows'] ?? []);
    }

    public function moveRow(int $block, int $row, int $direction): void
    {
        $target = $row + ($direction < 0 ? -1 : 1);
        $rows = &$this->blocks[$block]['rows'];

        if (isset($rows[$row], $rows[$target])) {
            [$rows[$row], $rows[$target]] = [$rows[$target], $rows[$row]];
        }
    }

    /** Range une ligne dans un autre bloc, à la fin. */
    public function moveRowToBlock(int $block, int $row, int $target): void
    {
        if ($block === $target || ! isset($this->blocks[$block]['rows'][$row]) || ($this->blocks[$target]['kind'] ?? null) !== 'fields') {
            return;
        }

        $this->blocks[$target]['rows'][] = $this->blocks[$block]['rows'][$row];
        $this->removeRow($block, $row);
    }

    private function rowCount(): int
    {
        return array_sum(array_map(fn (array $block) => count($block['rows']), $this->blocks));
    }

    public function save(): void
    {
        $this->authorize('update', $this->character);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'gameName' => ['nullable', 'string', 'max:255'],
            'tableName' => ['nullable', 'string', 'max:255'],
            'blocks' => ['array', 'max:'.PersonalCharacter::MAX_BLOCKS],
            'blocks.*.kind' => ['required', 'in:fields,note'],
            'blocks.*.title' => ['nullable', 'string', 'max:100'],
            'blocks.*.text' => ['nullable', 'string', 'max:50000'],
            'blocks.*.rows' => ['array'],
            'blocks.*.rows.*.type' => ['required', 'in:text,counter'],
            'blocks.*.rows.*.label' => ['required', 'string', 'max:100'],
            'blocks.*.rows.*.value' => ['nullable', 'string', 'max:5000'],
            'blocks.*.rows.*.max' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'blocks.*.rows.*.note' => ['nullable', 'string', 'max:2000'],
            'blocks.*.rows.*.checkbox' => ['boolean'],
            'blocks.*.rows.*.checked' => ['boolean'],
        ], attributes: [
            'name' => __('nom'), 'gameName' => __('jeu'), 'tableName' => __('table'), 'blocks.*.text' => __('notes'),
            'blocks.*.title' => __('titre du bloc'), 'blocks.*.rows.*.label' => __('nom du champ'), 'blocks.*.rows.*.value' => __('valeur'),
            'blocks.*.rows.*.max' => __('maximum'), 'blocks.*.rows.*.note' => __('note'),
        ]);

        $errors = [];
        foreach ($this->blocks as $b => $block) {
            foreach ($block['rows'] as $r => $row) {
                if ($row['type'] === 'counter' && trim($row['value']) !== '' && filter_var(trim($row['value']), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000]]) === false) {
                    $errors["blocks.$b.rows.$r.value"] = __('Un compteur est un nombre entier, de 0 à 1 000 000.');
                }
            }
        }
        if ($this->rowCount() > PersonalCharacter::MAX_FIELDS) {
            $errors['blocks'] = __('Une feuille garde au plus :count champs.', ['count' => PersonalCharacter::MAX_FIELDS]);
        }
        if ($errors !== []) {
            $this->setErrorBag($errors);

            return;
        }

        $this->character->update([
            'name' => trim($this->name),
            'game' => trim($this->gameName) ?: null,
            'table_name' => trim($this->tableName) ?: null,
            'blocks' => array_map(fn (array $block) => [
                'id' => preg_match('/^[a-z0-9]{1,20}$/', (string) $block['id']) ? $block['id'] : Str::lower(Str::random(10)),
                'kind' => $block['kind'],
                'title' => trim((string) $block['title']),
                'column' => in_array($block['column'] ?? null, PersonalCharacter::COLUMNS, true) ? $block['column'] : 'left',
                'text' => $block['kind'] === 'note' ? trim((string) $block['text']) : '',
                'rows' => $block['kind'] === 'note' ? [] : array_map(fn (array $row) => [
                    'type' => $row['type'],
                    'label' => trim($row['label']),
                    'value' => $row['type'] === 'counter' ? (int) trim($row['value']) : trim((string) $row['value']),
                    ...($row['type'] === 'counter' ? ['max' => trim((string) $row['max']) === '' ? null : (int) $row['max']] : []),
                    'note' => trim((string) $row['note']),
                    'checkbox' => (bool) $row['checkbox'],
                    'checked' => (bool) $row['checkbox'] && (bool) $row['checked'],
                ], $block['rows']),
            ], $this->blocks),
        ]);

        $this->cancel();
    }

    /** − / + sur un compteur pendant la partie, enregistré tout de suite. */
    public function adjust(string $blockId, int $row, int $delta): void
    {
        abort_unless(abs($delta) <= 1000, 422);

        $this->changeRow($blockId, $row, function (array $line) use ($delta) {
            abort_unless($line['type'] === 'counter', 404);

            return [...$line, ...FieldType::adjustCounter(['value' => (int) $line['value']], $delta)];
        });
    }

    /** Case à cocher d'une ligne, enregistrée tout de suite. */
    public function toggleCheck(string $blockId, int $row): void
    {
        $this->changeRow($blockId, $row, function (array $line) {
            abort_unless($line['checkbox'], 404);

            return [...$line, 'checked' => ! $line['checked']];
        });
    }

    private function changeRow(string $blockId, int $row, callable $change): void
    {
        $this->authorize('update', $this->character);

        DB::transaction(function () use ($blockId, $row, $change) {
            $character = PersonalCharacter::query()->lockForUpdate()->findOrFail($this->character->id);
            $blocks = $character->blockList();
            $index = collect($blocks)->search(fn (array $block) => $block['id'] === $blockId);
            abort_if($index === false || ! isset($blocks[$index]['rows'][$row]), 404);

            $blocks[$index]['rows'][$row] = $change($blocks[$index]['rows'][$row]);
            $character->update(['blocks' => $blocks]);
            $this->character = $character;
        });
    }

    public function toggleArranging(): void
    {
        $this->arranging = ! $this->arranging;
    }

    /**
     * Nouvel ordre des blocs après un glisser-déposer ou une flèche : identifiants par colonne.
     *
     * @param  array<string, mixed>  $columns
     */
    public function saveLayout(array $columns): void
    {
        $this->authorize('update', $this->character);

        DB::transaction(function () use ($columns) {
            $character = PersonalCharacter::query()->lockForUpdate()->findOrFail($this->character->id);
            $blocks = collect($character->blockList())->keyBy('id');
            $ordered = [];

            foreach (PersonalCharacter::COLUMNS as $column) {
                foreach (array_filter((array) ($columns[$column] ?? []), 'is_string') as $id) {
                    if ($blocks->has($id) && ! isset($ordered[$id])) {
                        $ordered[$id] = [...$blocks[$id], 'column' => $column];
                    }
                }
            }

            // Un bloc absent de l'envoi garde sa place, à la fin.
            foreach ($blocks as $id => $block) {
                $ordered[$id] ??= $block;
            }

            $character->update(['blocks' => array_values($ordered)]);
            $this->character = $character;
        });
    }

    public function updatedPortrait(): void
    {
        $this->authorize('update', $this->character);
        $this->validate(['portrait' => ['image', 'max:5120']], attributes: ['portrait' => __('portrait')]);
        Plans::ensureRoom(auth()->user(), (int) $this->portrait->getSize(), 'portrait');

        $this->character->deletePortrait();
        $this->character->update([
            'portrait_path' => $this->portrait->store('personal-characters/'.auth()->id(), PersonalCharacter::DISK),
            'portrait_size' => $this->portrait->getSize(),
        ]);

        $this->reset('portrait');
    }

    public function removePortrait(): void
    {
        $this->authorize('update', $this->character);

        $this->character->deletePortrait();
        $this->character->update(['portrait_path' => null, 'portrait_size' => null]);
    }

    public function updatedSheet(): void
    {
        $this->authorize('update', $this->character);
        $this->validate(['sheet' => ['file', 'mimes:pdf', 'max:30720']], attributes: ['sheet' => __('feuille')]);
        Plans::ensureRoom(auth()->user(), (int) $this->sheet->getSize(), 'sheet');

        $this->character->deleteSheet();
        $this->character->update([
            'sheet_path' => $this->sheet->store('personal-characters/'.auth()->id(), PersonalCharacter::DISK),
            'sheet_name' => $this->sheet->getClientOriginalName(),
            'sheet_size' => $this->sheet->getSize(),
        ]);

        $this->reset('sheet');
    }

    public function removeSheet(): void
    {
        $this->authorize('update', $this->character);

        $this->character->deleteSheet();
        $this->character->update(['sheet_path' => null, 'sheet_name' => null, 'sheet_size' => null]);
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->character);

        $this->character->delete();

        $this->redirectRoute('my-sheets.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.my-sheets.show')->title($this->character->name);
    }
}
