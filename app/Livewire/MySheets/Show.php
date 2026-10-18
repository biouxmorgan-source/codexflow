<?php

namespace App\Livewire\MySheets;

use App\Enums\FieldType;
use App\Models\PersonalCharacter;
use App\Support\Plans\Plans;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Une feuille de personnage personnelle : identité, portrait, feuille PDF, champs libres
 * (textes et compteurs à − / + pendant la partie) et notes. Visible de son seul propriétaire.
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

    public string $notes = '';

    /** @var list<array{type: string, label: string, value: string, max: string}> */
    public array $rows = [];

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
        $this->name = $this->character->name;
        $this->gameName = (string) $this->character->game;
        $this->tableName = (string) $this->character->table_name;
        $this->notes = (string) $this->character->notes;
        $this->rows = array_map(fn (array $row) => [
            'type' => $row['type'] === 'counter' ? 'counter' : 'text',
            'label' => (string) ($row['label'] ?? ''),
            'value' => (string) ($row['value'] ?? ''),
            'max' => isset($row['max']) ? (string) $row['max'] : '',
        ], $this->character->fieldRows());
        $this->editing = true;
    }

    public function cancel(): void
    {
        $this->resetValidation();
        $this->reset('editing', 'rows');
    }

    public function addRow(string $type): void
    {
        abort_unless(in_array($type, ['text', 'counter'], true) && count($this->rows) < PersonalCharacter::MAX_FIELDS, 422);

        $this->rows[] = ['type' => $type, 'label' => '', 'value' => $type === 'counter' ? '0' : '', 'max' => ''];
    }

    public function removeRow(int $index): void
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    public function moveRow(int $index, int $direction): void
    {
        $target = $index + ($direction < 0 ? -1 : 1);

        if (isset($this->rows[$index], $this->rows[$target])) {
            [$this->rows[$index], $this->rows[$target]] = [$this->rows[$target], $this->rows[$index]];
        }
    }

    public function save(): void
    {
        $this->authorize('update', $this->character);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'gameName' => ['nullable', 'string', 'max:255'],
            'tableName' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:50000'],
            'rows' => ['array', 'max:'.PersonalCharacter::MAX_FIELDS],
            'rows.*.type' => ['required', 'in:text,counter'],
            'rows.*.label' => ['required', 'string', 'max:100'],
            'rows.*.value' => ['nullable', 'string', 'max:5000'],
            'rows.*.max' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ], attributes: [
            'name' => __('nom'), 'gameName' => __('jeu'), 'tableName' => __('table'), 'notes' => __('notes'),
            'rows.*.label' => __('nom du champ'), 'rows.*.value' => __('valeur'), 'rows.*.max' => __('maximum'),
        ]);

        $errors = [];
        foreach ($this->rows as $index => $row) {
            if ($row['type'] === 'counter' && trim($row['value']) !== '' && filter_var(trim($row['value']), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000]]) === false) {
                $errors["rows.$index.value"] = __('Un compteur est un nombre entier, de 0 à 1 000 000.');
            }
        }
        if ($errors !== []) {
            $this->setErrorBag($errors);

            return;
        }

        $this->character->update([
            'name' => trim($this->name),
            'game' => trim($this->gameName) ?: null,
            'table_name' => trim($this->tableName) ?: null,
            'notes' => trim($this->notes) ?: null,
            'fields' => array_map(fn (array $row) => $row['type'] === 'counter'
                ? ['type' => 'counter', 'label' => trim($row['label']), 'value' => (int) trim($row['value']), 'max' => trim($row['max']) === '' ? null : (int) $row['max']]
                : ['type' => 'text', 'label' => trim($row['label']), 'value' => trim($row['value'])], $this->rows),
        ]);

        $this->cancel();
    }

    /** − / + sur un compteur pendant la partie, enregistré tout de suite. */
    public function adjust(int $index, int $delta): void
    {
        $this->authorize('update', $this->character);
        abort_unless(abs($delta) <= 1000, 422);

        DB::transaction(function () use ($index, $delta) {
            $character = PersonalCharacter::query()->lockForUpdate()->findOrFail($this->character->id);
            $fields = $character->fieldRows();
            abort_unless(($fields[$index]['type'] ?? null) === 'counter', 404);

            $fields[$index] = [...$fields[$index], ...FieldType::adjustCounter(['value' => (int) $fields[$index]['value']], $delta)];
            $character->update(['fields' => $fields]);
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
