<?php

namespace App\Livewire\Entities;

use App\Livewire\Concerns\SuggestsEntities;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Création et modification d'une fiche, toujours depuis le contexte d'une campagne.
 */
class Form extends Component
{
    use SuggestsEntities;
    use WithFileUploads;

    public Campaign $campaign;

    public ?Entity $entity = null;

    public string $entityTypeId = '';

    public string $name = '';

    public string $summary = '';

    public string $description = '';

    public string $gmNotes = '';

    /** « world » : réutilisable dans toutes les campagnes du monde ; « campaign » : propre à cette campagne. */
    public string $scope = 'campaign';

    /** Image principale (portrait, illustration du lieu ou de l'objet). */
    public ?TemporaryUploadedFile $image = null;

    public bool $removeImage = false;

    /** Étiquettes séparées par des virgules. */
    public string $tags = '';

    /** Page d'où l'on vient (ex. Personnages), où revenir après l'enregistrement ou l'annulation. */
    #[Url(as: 'retour', except: '')]
    public string $back = '';

    /** Champs libres du jeu : [id de définition => saisie]. */
    public array $fields = [];

    public function mount(Campaign $campaign, ?Entity $entity = null): void
    {
        $this->authorize('update', $campaign);

        if ($entity?->exists) {
            abort_unless($campaign->availableEntities()->whereKey($entity->getKey())->exists(), 404);
            $this->authorize('update', $entity);

            $this->entity = $entity;
            $this->entityTypeId = (string) $entity->entity_type_id;
            $this->name = $entity->name;
            $this->summary = (string) $entity->summary;
            $this->description = (string) $entity->description;
            $this->gmNotes = (string) $entity->gm_notes;
            $this->scope = $entity->isWorldEntity() ? 'world' : 'campaign';
            $this->tags = $entity->tags->pluck('name')->implode(', ');

            foreach ($campaign->gameSystem->fieldDefinitions as $definition) {
                $value = $entity->fieldValue($definition);
                $this->fields[$definition->id] = $definition->type->input($value);
            }

            return;
        }

        $this->entity = null;
        $this->entityTypeId = (string) $this->types->first()?->getKey();
        $this->scope = $campaign->world_id ? 'world' : 'campaign';
    }

    /** @return Collection<int, EntityType> */
    #[Computed]
    public function types(): Collection
    {
        return EntityType::query()->availableTo($this->campaign->owner)->orderBy('id')->get();
    }

    /** @return Collection<int, FieldDefinition> */
    #[Computed]
    public function fieldDefinitions(): Collection
    {
        return $this->campaign->gameSystem->fieldDefinitions()->forType($this->entityTypeId ?: null)->ordered()->get();
    }

    /** @return list<string> */
    #[Computed]
    public function existingTags(): array
    {
        return Tag::query()->where('user_id', $this->campaign->user_id)->has('entities')->orderByRaw('lower(name)')->pluck('name')->all();
    }

    public function save(): void
    {
        $this->validate([
            'entityTypeId' => ['required', Rule::in($this->types->modelKeys())],
            'name' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:20000'],
            'gmNotes' => ['nullable', 'string', 'max:20000'],
            'scope' => ['required', Rule::in($this->campaign->world_id ? ['world', 'campaign'] : ['campaign'])],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'tags' => ['nullable', 'string', 'max:1000'],
        ], attributes: [
            'entityTypeId' => __('type'),
            'summary' => __('résumé'),
            'gmNotes' => __('notes MJ'),
            'scope' => __('portée'),
            'image' => __('image'),
        ]);

        $values = [];
        $errors = [];

        foreach ($this->fieldDefinitions as $definition) {
            [$value, $error] = $definition->parse($this->fields[$definition->id] ?? null);

            if ($error !== null) {
                $errors['fields.'.$definition->id] = __(':name : :error.', ['name' => $definition->name, 'error' => $error]);
            }

            $values[$definition->id] = $value;
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $entity = $this->entity ?? new Entity;
        $entity->setFieldValues($values);
        $entity->fill([
            'entity_type_id' => (int) $this->entityTypeId,
            'name' => $this->name,
            'summary' => $this->summary ?: null,
            'description' => $this->description ?: null,
            'gm_notes' => $this->gmNotes ?: null,
        ]);

        if (! $entity->exists) {
            $entity->owner()->associate($this->campaign->owner);

            if ($this->scope === 'world') {
                $entity->world()->associate($this->campaign->world);
            } else {
                $entity->campaign()->associate($this->campaign);
            }
        }

        if ($this->removeImage || $this->image !== null) {
            $entity->deleteImage();
        }

        if ($this->image !== null) {
            $entity->image_path = $this->image->store('entities', Entity::FILES_DISK);
        }

        $entity->save();
        $entity->tags()->sync(Tag::idsFromInput($this->campaign->owner, $this->tags));

        $back = $this->backUrl();
        $back ? $this->redirect($back, navigate: true) : $this->redirectRoute('entities.show', [$this->campaign, $entity], navigate: true);
    }

    /** Adresse de retour, seulement un chemin de ce site (jamais un autre domaine). */
    public function backUrl(): ?string
    {
        return preg_match('#^/(?![/\\\\])[^\s]*$#', $this->back) ? $this->back : null;
    }

    public function render()
    {
        return view('livewire.entities.form')
            ->title($this->entity ? __('Modifier :name', ['name' => $this->entity->name]) : __('Nouvelle entité'));
    }
}
