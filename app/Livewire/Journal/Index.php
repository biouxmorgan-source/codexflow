<?php

namespace App\Livewire\Journal;

use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\FieldDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Journal des modifications d'une campagne, de son monde et de son jeu : qui a changé quoi,
 * quand, avec l'ancienne et la nouvelle valeur. Réservé au MJ (les entrées contiennent la zone MJ).
 */
class Index extends Component
{
    use WithPagination;

    public Campaign $campaign;

    #[Url(as: 'element')]
    public string $type = '';

    #[Url(as: 'q')]
    public string $search = '';

    /** Historique d'un seul élément : « entity:12 ». */
    #[Url(as: 'sujet')]
    public string $subject = '';

    /** @var array<string, array<int, int>> identifiants encore accessibles, par type */
    protected array $available = [];

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['type', 'search', 'subject'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function entries(): LengthAwarePaginator
    {
        [$subjectType, $subjectId] = array_pad(explode(':', $this->subject, 2), 2, null);

        return ActivityLog::query()
            ->forCampaign($this->campaign)
            ->with('user')
            ->when(isset(ActivityLog::SUBJECTS[$this->type]), fn (Builder $q) => $q->where('subject_type', $this->type))
            ->when(trim($this->search) !== '', fn (Builder $q) => $q->where('subject_label', 'ilike', '%'.str_replace(['%', '_'], ['\%', '\_'], trim($this->search)).'%'))
            ->when($subjectType && ctype_digit((string) $subjectId), fn (Builder $q) => $q->where('subject_type', $subjectType)->where('subject_id', (int) $subjectId))
            ->orderByDesc('id')
            ->paginate(50);
    }

    /** @return array<int, FieldDefinition> */
    #[Computed]
    public function definitions(): array
    {
        return $this->campaign->gameSystem->fieldDefinitions()->get()->keyBy('id')->all();
    }

    /**
     * Adresse de l'élément s'il existe encore et reste accessible depuis cette campagne.
     */
    public function link(ActivityLog $entry): ?string
    {
        $this->available[$entry->subject_type] ??= match ($entry->subject_type) {
            'entity' => $this->campaign->availableEntities()->pluck('entities.id')->flip()->all(),
            'scene' => $this->campaign->scenes()->pluck('scenes.id')->flip()->all(),
            'rule' => $this->campaign->availableRules()->pluck('rules.id')->flip()->all(),
            'document' => $this->campaign->availableDocuments()->pluck('documents.id')->flip()->all(),
            default => [],
        };

        if (in_array($entry->subject_type, ['scenario', 'field'], true)) {
            return $entry->event === 'deleted' ? null : route($entry->subject_type === 'field' ? 'fields.index' : 'scenarios.index', $this->campaign);
        }

        if (! isset($this->available[$entry->subject_type][$entry->subject_id])) {
            return null;
        }

        return match ($entry->subject_type) {
            'entity' => route('entities.show', [$this->campaign, $entry->subject_id]),
            'scene' => route('scenes.show', [$this->campaign, $entry->subject_id]),
            'rule' => route('rules.show', [$this->campaign, $entry->subject_id]),
            'document' => route('documents.show', [$this->campaign, $entry->subject_id]),
            default => null,
        };
    }

    public function render()
    {
        return view('livewire.journal.index')->title('Journal · '.$this->campaign->name);
    }
}
