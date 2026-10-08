<?php

namespace App\Livewire\Admin;

use App\Models\BugReport;
use App\Support\Changelog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Backlog de l'administrateur : les problèmes signalés depuis l'application y arrivent
 * d'eux-mêmes, à côté des bugs et évolutions relevés en recette ou ajoutés à la main.
 */
class Backlog extends Component
{
    /** open = tout ce qui reste à traiter ; all = tout ; sinon un statut précis. */
    #[Url(except: 'open')]
    public string $status = 'open';

    #[Url(except: '')]
    public string $kind = '';

    #[Url(except: '')]
    public string $search = '';

    public bool $adding = false;

    public string $newTitle = '';

    public string $newKind = 'evolution';

    public string $newPriority = 'normal';

    public string $newMessage = '';

    public function mount(): void
    {
        $this->authorize('admin');
    }

    /** @return Collection<int, BugReport> */
    #[Computed]
    public function items(): Collection
    {
        return BugReport::query()
            ->with('user')
            ->when($this->status === 'open', fn ($query) => $query->open())
            ->when(in_array($this->status, BugReport::STATUSES, true), fn ($query) => $query->where('status', $this->status))
            ->when(in_array($this->kind, BugReport::KINDS, true), fn ($query) => $query->where('kind', $this->kind))
            ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'ilike', '%'.addcslashes($this->search, '%_\\').'%')
                ->orWhere('message', 'ilike', '%'.addcslashes($this->search, '%_\\').'%')))
            ->orderByRaw("case status when 'new' then 0 when 'in_progress' then 1 when 'planned' then 2 when 'fixed' then 3 else 4 end")
            ->orderByRaw("case priority when 'high' then 0 when 'normal' then 1 else 2 end")
            ->latest()
            ->latest('id')
            ->get();
    }

    /** @return array<string, int> nombre d'éléments par statut */
    #[Computed]
    public function counts(): array
    {
        return BugReport::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->all();
    }

    public function setStatus(int $id, string $status): void
    {
        $this->authorize('admin');
        abort_unless(in_array($status, BugReport::STATUSES, true), 422);

        $item = BugReport::findOrFail($id);
        $item->update([
            'status' => $status,
            // Corrigé : par défaut dans la version en cours, modifiable ensuite.
            'fixed_in' => $status === 'fixed' ? ($item->fixed_in ?: Changelog::version()) : $item->fixed_in,
        ]);
        unset($this->items, $this->counts);
    }

    public function setPriority(int $id, string $priority): void
    {
        $this->authorize('admin');
        abort_unless(in_array($priority, BugReport::PRIORITIES, true), 422);

        BugReport::findOrFail($id)->update(['priority' => $priority]);
        unset($this->items);
    }

    public function setKind(int $id, string $kind): void
    {
        $this->authorize('admin');
        abort_unless(in_array($kind, BugReport::KINDS, true), 422);

        BugReport::findOrFail($id)->update(['kind' => $kind]);
        unset($this->items);
    }

    public function saveDetails(int $id, string $title, string $fixedIn, string $note): void
    {
        $this->authorize('admin');

        BugReport::findOrFail($id)->update([
            'title' => mb_substr(trim($title), 0, 255) ?: null,
            'fixed_in' => mb_substr(trim($fixedIn), 0, 32) ?: null,
            'admin_note' => mb_substr(trim($note), 0, 5000) ?: null,
        ]);
        unset($this->items);
    }

    public function add(): void
    {
        $this->authorize('admin');

        $this->validate([
            'newTitle' => ['required', 'string', 'max:255'],
            'newKind' => ['required', Rule::in(BugReport::KINDS)],
            'newPriority' => ['required', Rule::in(BugReport::PRIORITIES)],
            'newMessage' => ['nullable', 'string', 'max:5000'],
        ], attributes: ['newTitle' => __('titre'), 'newMessage' => __('détail')]);

        BugReport::create([
            'user_id' => auth()->id(),
            'title' => trim($this->newTitle),
            'message' => trim($this->newMessage) ?: trim($this->newTitle),
            'kind' => $this->newKind,
            'priority' => $this->newPriority,
            'source' => 'admin',
            'version' => Changelog::version(),
        ]);

        $this->reset('adding', 'newTitle', 'newMessage', 'newKind', 'newPriority');
        unset($this->items, $this->counts);
    }

    public function delete(int $id): void
    {
        $this->authorize('admin');

        BugReport::findOrFail($id)->delete();
        unset($this->items, $this->counts);
    }

    public function render()
    {
        return view('livewire.admin.backlog', [
            'statuses' => BugReport::statuses(),
            'priorities' => BugReport::priorities(),
            'kinds' => BugReport::kinds(),
            'sources' => BugReport::sources(),
        ])->title(__('Administration · Backlog'));
    }
}
