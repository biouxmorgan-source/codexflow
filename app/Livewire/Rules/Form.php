<?php

namespace App\Livewire\Rules;

use App\Enums\RuleOrigin;
use App\Enums\RuleStatus;
use App\Enums\Zone;
use App\Livewire\Concerns\SuggestsEntities;
use App\Models\Campaign;
use App\Models\Rule;
use App\Models\Tag;
use Illuminate\Validation\Rule as ValidationRule;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Saisie libre d'une règle : titre, catégorie, résumé, procédure, notes, source, tags.
 */
class Form extends Component
{
    use SuggestsEntities;

    public Campaign $campaign;

    public ?Rule $rule = null;

    public string $title = '';

    public string $category = '';

    public string $summary = '';

    public string $procedure = '';

    public string $gmNotes = '';

    public string $source = '';

    public string $origin = 'reference';

    public string $status = 'available';

    public string $zone = 'public';

    /** « game » : partagée par les campagnes du jeu ; « campaign » : propre à cette campagne. */
    public string $scope = 'game';

    public string $tags = '';

    public function mount(Campaign $campaign, ?Rule $rule = null): void
    {
        $this->authorize('update', $campaign);

        if (! $rule?->exists) {
            $this->rule = null;

            return;
        }

        abort_unless($campaign->availableRules()->whereKey($rule->id)->exists(), 404);
        $this->authorize('update', $rule);

        $this->rule = $rule;
        $this->title = $rule->title;
        $this->category = (string) $rule->category;
        $this->summary = (string) $rule->summary;
        $this->procedure = (string) $rule->procedure;
        $this->gmNotes = (string) $rule->gm_notes;
        $this->source = (string) $rule->source;
        $this->origin = $rule->origin->value;
        $this->status = $rule->status->value;
        $this->zone = $rule->zone->value;
        $this->scope = $rule->isShared() ? 'game' : 'campaign';
        $this->tags = $rule->tags->pluck('name')->implode(', ');
    }

    /** @return list<string> */
    #[Computed]
    public function categories(): array
    {
        return $this->campaign->availableRules()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->all();
    }

    /** @return list<string> */
    #[Computed]
    public function existingTags(): array
    {
        return Tag::query()->where('user_id', auth()->id())->has('rules')->orderByRaw('lower(name)')->pluck('name')->all();
    }

    public function save(): void
    {
        $this->authorize('update', $this->campaign);

        if ($this->scope === 'game') {
            $this->authorize('update', $this->campaign->gameSystem);
        }

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'procedure' => ['nullable', 'string', 'max:20000'],
            'gmNotes' => ['nullable', 'string', 'max:20000'],
            'source' => ['nullable', 'string', 'max:255'],
            'origin' => ['required', ValidationRule::enum(RuleOrigin::class)],
            'status' => ['required', ValidationRule::enum(RuleStatus::class)],
            'zone' => ['required', ValidationRule::enum(Zone::class)],
            'scope' => ['required', ValidationRule::in(['game', 'campaign'])],
            'tags' => ['nullable', 'string', 'max:1000'],
        ], attributes: [
            'title' => __('titre'),
            'category' => __('catégorie'),
            'summary' => __('résumé'),
            'gmNotes' => __('notes MJ'),
            'origin' => __('origine'),
            'zone' => __('visibilité'),
            'scope' => __('rattachement'),
        ]);

        $rule = $this->rule ?? new Rule;
        $rule->fill([
            'title' => trim($this->title),
            'category' => trim($this->category) ?: null,
            'summary' => trim($this->summary) ?: null,
            'procedure' => $this->procedure ?: null,
            'gm_notes' => $this->gmNotes ?: null,
            'source' => trim($this->source) ?: null,
            'origin' => $this->origin,
            'status' => $this->status,
            'zone' => $this->zone,
        ]);

        if (! $rule->exists) {
            $rule->owner()->associate(auth()->user());
        }

        $rule->game_system_id = $this->scope === 'game' ? $this->campaign->game_system_id : null;
        $rule->campaign_id = $this->scope === 'campaign' ? $this->campaign->id : null;
        $rule->save();

        $rule->tags()->sync(Tag::idsFromInput(auth()->user(), $this->tags));

        $this->redirectRoute('rules.show', [$this->campaign, $rule], navigate: true);
    }

    public function render()
    {
        return view('livewire.rules.form')->title($this->rule ? __('Modifier :title', ['title' => $this->rule->title]) : __('Nouvelle règle'));
    }
}
