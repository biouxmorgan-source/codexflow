<?php

namespace App\Livewire\Tags;

use App\Models\Campaign;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Les tags du MJ, toutes campagnes confondues : renommer, colorer, fusionner, supprimer.
 */
class Manage extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $color = '';

    public ?int $mergingId = null;

    public ?int $mergeTargetId = null;

    /** Tag dont la liste des éléments est dépliée. */
    public ?int $openId = null;

    /** @return Collection<int, Tag> */
    #[Computed]
    public function tags(): Collection
    {
        return Tag::query()
            ->where('user_id', auth()->id())
            ->withCount(['entities', 'rules', 'documents', 'scenes'])
            ->orderByRaw('lower(name)')
            ->get();
    }

    public function edit(int $id): void
    {
        $tag = $this->own($id);

        $this->resetValidation();
        $this->reset('mergingId', 'mergeTargetId');
        $this->editingId = $tag->id;
        $this->name = $tag->name;
        $this->color = (string) $tag->color;
    }

    public function cancel(): void
    {
        $this->resetValidation();
        $this->reset('editingId', 'name', 'color', 'mergingId', 'mergeTargetId');
    }

    public function save(): void
    {
        $tag = $this->own((int) $this->editingId);

        $this->validate([
            'name' => ['required', 'string', 'max:60'],
            'color' => ['nullable', Rule::in(array_keys(Tag::COLORS))],
        ], attributes: ['name' => __('nom'), 'color' => __('couleur')]);

        $name = trim(preg_replace('/\s+/', ' ', $this->name));
        $taken = $this->tags->contains(fn (Tag $other) => $other->id !== $tag->id && mb_strtolower($other->name) === mb_strtolower($name));

        if ($taken) {
            $this->addError('name', __('Ce tag existe déjà. Pour réunir les deux, utilisez « Fusionner ».'));

            return;
        }

        $tag->update(['name' => $name, 'color' => $this->color ?: null]);

        $this->cancel();
        unset($this->tags);
    }

    public function startMerge(int $id): void
    {
        $this->cancel();
        $this->mergingId = $this->own($id)->id;
    }

    public function merge(): void
    {
        $source = $this->own((int) $this->mergingId);
        $target = $this->mergeTargetId ? $this->own($this->mergeTargetId) : null;

        if ($target === null || $target->is($source)) {
            $this->addError('mergeTargetId', __('Choisissez le tag qui reçoit les éléments.'));

            return;
        }

        $source->mergeInto($target);

        $this->cancel();
        unset($this->tags);
    }

    public function delete(int $id): void
    {
        $this->own($id)->delete();

        $this->cancel();
        unset($this->tags);
    }

    /** Déplie ou replie la liste des éléments portant ce tag. */
    public function toggleItems(int $id): void
    {
        $this->openId = $this->openId === $id ? null : $this->own($id)->id;
        unset($this->items);
    }

    /**
     * Éléments du tag déplié, chacun avec un lien vers sa page dans une campagne du MJ
     * (sa campagne, ou une campagne de son monde ou de son jeu) ; sans lien s'il n'y en a pas.
     *
     * @return SupportCollection<int, array{type: string, label: string, url: ?string, place: ?string}>
     */
    #[Computed]
    public function items(): SupportCollection
    {
        if ($this->openId === null) {
            return collect();
        }

        $tag = $this->own($this->openId);
        $campaigns = Campaign::query()->where('user_id', auth()->id())->orderBy('id')->get(['id', 'name', 'world_id', 'game_system_id']);
        $pick = fn (?int $campaignId, ?int $worldId = null, ?int $gameId = null) => $campaigns->firstWhere('id', $campaignId)
            ?? ($worldId ? $campaigns->firstWhere('world_id', $worldId) : null)
            ?? ($gameId ? $campaigns->firstWhere('game_system_id', $gameId) : null);
        $item = fn (string $type, string $label, ?Campaign $campaign, ?string $route, mixed $model) => [
            'type' => $type,
            'label' => $label,
            'url' => $campaign && $route ? route($route, [$campaign, $model]) : null,
            'place' => $campaign?->name,
        ];

        return collect()
            ->concat($tag->entities()->orderBy('name')->limit(100)->get()->map(fn ($entity) => $item(__('Fiche'), $entity->name, $pick($entity->campaign_id, $entity->world_id), 'entities.show', $entity)))
            ->concat($tag->scenes()->with('scenario:id,campaign_id')->orderBy('name')->limit(100)->get()->map(fn ($scene) => $item(__('Scène'), $scene->name, $pick($scene->scenario?->campaign_id), 'scenes.show', $scene)))
            ->concat($tag->rules()->orderBy('title')->limit(100)->get()->map(fn ($rule) => $item(__('Règle'), $rule->title, $pick($rule->campaign_id, null, $rule->game_system_id), 'rules.show', $rule)))
            ->concat($tag->documents()->orderBy('title')->limit(100)->get()->map(fn ($document) => $item(__('Document'), $document->title, $pick($document->campaign_id, $document->world_id, $document->game_system_id), 'documents.show', $document)));
    }

    private function own(int $id): Tag
    {
        return Tag::query()->where('user_id', auth()->id())->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.tags.manage', ['colors' => Tag::colorLabels()])->title(__('Tags'));
    }
}
