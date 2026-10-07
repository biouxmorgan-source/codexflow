<?php

namespace App\Livewire\Rules;

use App\Enums\RuleStatus;
use App\Models\Campaign;
use App\Models\Rule;
use App\Models\ToPlayItem;
use App\Support\EntityLinks;
use Livewire\Component;

class Show extends Component
{
    public Campaign $campaign;

    public Rule $rule;

    public ?int $pickedDocumentId = null;

    public function mount(Campaign $campaign, Rule $rule): void
    {
        $this->authorize('update', $campaign);
        abort_unless($campaign->availableRules()->whereKey($rule->id)->exists(), 404);
        $this->authorize('view', $rule);
    }

    public function setStatus(string $status): void
    {
        $this->authorize('update', $this->rule);

        $this->rule->status = RuleStatus::from($status);
        $this->rule->save();
    }

    /** Place la règle dans « À jouer » de la campagne, une seule fois tant qu'elle n'est pas jouée. */
    public function addToPlay(): void
    {
        $this->authorize('update', $this->campaign);

        $pending = $this->campaign->toPlayItems()->pending()->where('rule_id', $this->rule->id)->exists();

        if (! $pending) {
            $item = new ToPlayItem(['body' => $this->rule->title, 'position' => (int) $this->campaign->toPlayItems()->max('position') + 1]);
            $item->campaign()->associate($this->campaign);
            $item->rule()->associate($this->rule);
            $item->save();
        }
    }

    public function linkDocument(): void
    {
        $this->authorize('update', $this->rule);

        $document = $this->pickedDocumentId ? $this->campaign->availableDocuments()->find($this->pickedDocumentId) : null;

        if ($document === null) {
            $this->addError('pickedDocumentId', __('Choisissez un document dans la liste.'));

            return;
        }

        $this->rule->documents()->syncWithoutDetaching([$document->id]);
        $this->pickedDocumentId = null;
    }

    public function unlinkDocument(int $documentId): void
    {
        $this->authorize('update', $this->rule);

        $this->rule->documents()->detach($documentId);
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->rule);

        $this->rule->delete();

        $this->redirectRoute('rules.index', $this->campaign, navigate: true);
    }

    public function render()
    {
        $documents = $this->rule->documents()->get();

        return view('livewire.rules.show', [
            'procedure' => EntityLinks::render($this->rule->procedure, $this->campaign),
            'documents' => $documents,
            'documentOptions' => $this->campaign->availableDocuments()->whereKeyNot($documents->modelKeys())->orderBy('title')->get(['id', 'title']),
            'scenes' => $this->campaign->scenes()->whereHas('rules', fn ($q) => $q->whereKey($this->rule->id))->with('scenario')->get(),
            'pendingToPlay' => $this->campaign->toPlayItems()->pending()->where('rule_id', $this->rule->id)->exists(),
        ])->title($this->rule->title);
    }
}
