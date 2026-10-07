<?php

namespace App\Livewire\Tags;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
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

    private function own(int $id): Tag
    {
        return Tag::query()->where('user_id', auth()->id())->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.tags.manage', ['colors' => Tag::colorLabels()])->title(__('Tags'));
    }
}
