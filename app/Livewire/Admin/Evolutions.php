<?php

namespace App\Livewire\Admin;

use App\Models\BugReport;
use App\Models\Evolution;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/** Feuille de route de la plateforme : ce que l'administrateur prévoit de faire. */
class Evolutions extends Component
{
    /** open = en cours ou à venir ; all = tout ; sinon un statut précis. */
    #[Url(except: 'open')]
    public string $status = 'open';

    public bool $adding = false;

    public string $newTitle = '';

    public string $newDetail = '';

    public string $newPriority = 'normal';

    public string $newTarget = '';

    public function mount(): void
    {
        $this->authorize('admin');
    }

    /** @return Collection<int, Evolution> */
    #[Computed]
    public function items(): Collection
    {
        return Evolution::query()
            ->when($this->status === 'open', fn ($query) => $query->whereNotIn('status', Evolution::CLOSED))
            ->when(in_array($this->status, Evolution::STATUSES, true), fn ($query) => $query->where('status', $this->status))
            ->orderByRaw("case status when 'in_progress' then 0 when 'planned' then 1 when 'idea' then 2 when 'done' then 3 else 4 end")
            ->orderByRaw("case priority when 'high' then 0 when 'normal' then 1 else 2 end")
            ->oldest()
            ->oldest('id')
            ->get();
    }

    /** @return array<string, int> */
    #[Computed]
    public function counts(): array
    {
        return Evolution::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->all();
    }

    public function add(): void
    {
        $this->authorize('admin');

        $this->validate([
            'newTitle' => ['required', 'string', 'max:255'],
            'newDetail' => ['nullable', 'string', 'max:10000'],
            'newPriority' => ['required', Rule::in(BugReport::PRIORITIES)],
            'newTarget' => ['nullable', 'string', 'max:100'],
        ], attributes: ['newTitle' => __('titre'), 'newDetail' => __('détail'), 'newTarget' => __('échéance')]);

        Evolution::create([
            'title' => trim($this->newTitle),
            'detail' => trim($this->newDetail) ?: null,
            'priority' => $this->newPriority,
            'target' => trim($this->newTarget) ?: null,
        ]);

        $this->reset('adding', 'newTitle', 'newDetail', 'newPriority', 'newTarget');
        unset($this->items, $this->counts);
    }

    public function setStatus(int $id, string $status): void
    {
        $this->authorize('admin');
        abort_unless(in_array($status, Evolution::STATUSES, true), 422);

        Evolution::findOrFail($id)->update(['status' => $status]);
        unset($this->items, $this->counts);
    }

    public function setPriority(int $id, string $priority): void
    {
        $this->authorize('admin');
        abort_unless(in_array($priority, BugReport::PRIORITIES, true), 422);

        Evolution::findOrFail($id)->update(['priority' => $priority]);
        unset($this->items);
    }

    public function saveDetails(int $id, string $title, string $target, string $detail): void
    {
        $this->authorize('admin');

        $title = mb_substr(trim($title), 0, 255);
        $evolution = Evolution::findOrFail($id);
        $evolution->update([
            'title' => $title !== '' ? $title : $evolution->title,
            'target' => mb_substr(trim($target), 0, 100) ?: null,
            'detail' => mb_substr(trim($detail), 0, 10000) ?: null,
        ]);
        unset($this->items);
    }

    public function delete(int $id): void
    {
        $this->authorize('admin');

        Evolution::findOrFail($id)->delete();
        unset($this->items, $this->counts);
    }

    public function render()
    {
        return view('livewire.admin.evolutions', [
            'statuses' => Evolution::statuses(),
            'priorities' => BugReport::priorities(),
        ])->title(__('Administration · Évolutions'));
    }
}
