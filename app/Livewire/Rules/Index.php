<?php

namespace App\Livewire\Rules;

use App\Enums\RuleStatus;
use App\Models\Campaign;
use App\Models\Rule;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Règles et aides de jeu de la campagne : celles du jeu et celles propres à la campagne.
 */
class Index extends Component
{
    public Campaign $campaign;

    #[Url]
    public string $status = '';

    #[Url]
    public string $tag = '';

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
    }

    /** @return Collection<string, Collection<int, Rule>> */
    #[Computed]
    public function rules(): Collection
    {
        return $this->campaign->availableRules()
            ->with('tags')
            ->when(RuleStatus::tryFrom($this->status), fn (Builder $q, RuleStatus $status) => $q->where('status', $status))
            ->when($this->tag !== '', fn (Builder $q) => $q->whereHas('tags', fn (Builder $t) => $t->whereRaw('lower(tags.name) = ?', [mb_strtolower($this->tag)])))
            ->orderByRaw('lower(coalesce(category, \'\'))')
            ->orderByRaw('lower(title)')
            ->get()
            ->groupBy(fn (Rule $rule) => $rule->category ?: __('Sans catégorie'));
    }

    /** @return list<string> */
    #[Computed]
    public function tags(): array
    {
        return Tag::query()
            ->whereHas('rules', fn (Builder $q) => $q->availableIn($this->campaign))
            ->orderByRaw('lower(name)')
            ->pluck('name')
            ->all();
    }

    public function render()
    {
        return view('livewire.rules.index')->title(__('Règles · :name', ['name' => $this->campaign->name]));
    }
}
