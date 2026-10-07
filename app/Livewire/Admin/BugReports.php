<?php

namespace App\Livewire\Admin;

use App\Models\BugReport;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Problèmes signalés par les utilisateurs, pour les administrateurs.
 */
class BugReports extends Component
{
    public bool $showResolved = false;

    public function mount(): void
    {
        $this->authorize('admin');
    }

    /** @return Collection<int, BugReport> */
    #[Computed]
    public function reports(): Collection
    {
        return BugReport::query()
            ->with('user')
            ->when(! $this->showResolved, fn ($query) => $query->open())
            ->latest()
            ->latest('id')
            ->get();
    }

    public function toggleResolved(int $id): void
    {
        $this->authorize('admin');

        $report = BugReport::findOrFail($id);
        $report->forceFill(['resolved_at' => $report->resolved_at ? null : now()])->save();
        unset($this->reports);
    }

    public function delete(int $id): void
    {
        $this->authorize('admin');

        BugReport::findOrFail($id)->delete();
        unset($this->reports);
    }

    public function render()
    {
        return view('livewire.admin.bug-reports', ['openCount' => BugReport::open()->count()])->title(__('Problèmes signalés'));
    }
}
