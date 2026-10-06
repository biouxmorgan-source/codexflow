<?php

namespace App\Livewire\Entities;

use App\Enums\Zone;
use App\Livewire\Concerns\SuggestsEntities;
use App\Models\Attachment;
use App\Models\Campaign;
use App\Models\CampaignEntityState;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Support\EntityLinks;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Show extends Component
{
    use SuggestsEntities;
    use WithFileUploads;

    /** Libellés proposés pour les relations ; le MJ peut en écrire d'autres. */
    public const RELATION_LABELS = ['connaît', 'habite à', 'travaille pour', 'membre de', 'possède', 'allié de', 'ennemi de', 'parent de', 'amoureux de', 'se trouve à'];

    /** Types acceptés en pièce jointe : images, PDF, textes et documents bureautiques courants. */
    public const ATTACHMENT_MIMES = 'jpg,jpeg,png,webp,gif,pdf,txt,md,doc,docx,odt,xls,xlsx,ods';

    public Campaign $campaign;

    public Entity $entity;

    public string $status = '';

    public string $stateNotes = '';

    public bool $stateSaved = false;

    /** @var array<int, TemporaryUploadedFile> */
    public array $uploads = [];

    public string $uploadZone = 'gm';

    public ?int $relationTargetId = null;

    public string $relationLabel = '';

    public string $relationReverse = '';

    public string $relationZone = 'public';

    public bool $relationCampaignOnly = false;

    public function mount(Campaign $campaign, Entity $entity): void
    {
        $this->authorize('update', $campaign);
        abort_unless($campaign->availableEntities()->whereKey($entity->getKey())->exists(), 404);
        $this->authorize('view', $entity);

        $state = $this->state();
        $this->status = (string) $state->status;
        $this->stateNotes = (string) $state->gm_notes;
    }

    public function saveState(): void
    {
        $this->authorize('update', $this->entity);

        $this->validate([
            'status' => ['nullable', 'string', 'max:60'],
            'stateNotes' => ['nullable', 'string', 'max:20000'],
        ], attributes: ['status' => 'statut', 'stateNotes' => 'notes de campagne']);

        $state = $this->state();
        $state->fill([
            'status' => $this->status ?: null,
            'gm_notes' => $this->stateNotes ?: null,
        ]);

        if ($state->exists || $state->status !== null || $state->gm_notes !== null) {
            $state->save();
        }

        $this->stateSaved = true;
    }

    public function saveUploads(): void
    {
        $this->authorize('update', $this->entity);

        $this->validate([
            'uploads' => ['required', 'array', 'max:10'],
            'uploads.*' => ['file', 'mimes:'.self::ATTACHMENT_MIMES, 'max:20480'],
            'uploadZone' => ['required', Rule::enum(Zone::class)],
        ], attributes: ['uploads' => 'fichiers', 'uploads.*' => 'fichier', 'uploadZone' => 'zone']);

        foreach ($this->uploads as $file) {
            $attachment = new Attachment([
                'zone' => $this->uploadZone,
                'disk' => Entity::FILES_DISK,
                'path' => $file->store('attachments/'.$this->entity->getKey(), Entity::FILES_DISK),
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
            $attachment->owner()->associate(auth()->user());
            $this->entity->attachments()->save($attachment);
        }

        $this->reset('uploads');
        $this->entity->unsetRelation('attachments');
    }

    public function deleteAttachment(int $attachmentId): void
    {
        $attachment = $this->entity->attachments()->findOrFail($attachmentId);
        $this->authorize('delete', $attachment);

        $attachment->delete();
        $this->entity->unsetRelation('attachments');
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->entity);

        $this->entity->delete();

        $this->redirectRoute('campaigns.show', $this->campaign, navigate: true);
    }

    public function addRelation(): void
    {
        $this->authorize('update', $this->entity);

        $this->validate([
            'relationTargetId' => ['required', 'integer', Rule::notIn([$this->entity->id])],
            'relationLabel' => ['required', 'string', 'max:100'],
            'relationReverse' => ['nullable', 'string', 'max:100'],
            'relationZone' => ['required', Rule::enum(Zone::class)],
        ], [
            'relationTargetId.required' => 'Choisissez la fiche liée.',
            'relationTargetId.not_in' => 'Une fiche ne peut pas être liée à elle-même.',
        ], [
            'relationLabel' => 'relation',
            'relationReverse' => 'relation inverse',
        ]);

        $target = $this->campaign->availableEntities()->find($this->relationTargetId);

        if ($target === null) {
            $this->addError('relationTargetId', 'Cette fiche n\'existe pas dans la campagne.');

            return;
        }

        $this->authorize('update', $target);

        // Entre deux fiches du monde, la relation vaut pour tout le monde, sauf demande contraire.
        $worldWide = $this->entity->isWorldEntity() && $target->isWorldEntity() && ! $this->relationCampaignOnly;

        $relation = new EntityRelation([
            'label' => trim($this->relationLabel),
            'reverse_label' => trim($this->relationReverse) ?: null,
            'zone' => $this->relationZone,
        ]);
        $relation->owner()->associate(auth()->user());
        $relation->from()->associate($this->entity);
        $relation->to()->associate($target);
        $relation->campaign()->associate($worldWide ? null : $this->campaign);
        $relation->save();

        $this->reset('relationTargetId', 'relationLabel', 'relationReverse', 'relationCampaignOnly');
    }

    public function deleteRelation(int $relationId): void
    {
        $this->authorize('update', $this->entity);

        $relation = $this->entity->relationsIn($this->campaign)->findOrFail($relationId);
        abort_unless($relation->user_id === auth()->id(), 403);

        $relation->delete();
    }

    private function state(): CampaignEntityState
    {
        return $this->entity->stateIn($this->campaign);
    }

    public function render()
    {
        $attachments = $this->entity->attachments;
        $fields = $this->campaign->gameSystem->fieldDefinitions()->forType($this->entity->entity_type_id)->ordered()->get();
        $publicFields = $fields->where('zone', Zone::Public);
        $gmFields = $fields->where('zone', Zone::GameMaster);
        $filled = fn ($definitions) => $definitions->contains(fn ($definition) => $this->entity->fieldValue($definition) !== null);

        $relations = $this->entity->relationsIn($this->campaign)->orderBy('label')->get()
            ->filter(fn (EntityRelation $relation) => $relation->from !== null && $relation->to !== null);

        return view('livewire.entities.show', [
            'scenes' => EntityLinks::scenes($this->entity, $this->campaign),
            'publicRelations' => $relations->where('zone', Zone::Public),
            'gmRelations' => $relations->where('zone', Zone::GameMaster),
            'relationLabels' => collect(self::RELATION_LABELS)
                ->merge(EntityRelation::where('user_id', auth()->id())->distinct()->pluck('label'))
                ->unique()->sort()->values(),
            'otherCampaigns' => $this->entity->isWorldEntity()
                ? $this->entity->world->campaigns()->whereKeyNot($this->campaign->getKey())->count()
                : 0,
            'description' => EntityLinks::render($this->entity->description, $this->campaign),
            'gmNotes' => EntityLinks::render($this->entity->gm_notes, $this->campaign),
            'backlinks' => EntityLinks::backlinks($this->entity, $this->campaign),
            'publicAttachments' => $attachments->where('zone', Zone::Public),
            'gmAttachments' => $attachments->where('zone', Zone::GameMaster),
            'publicFields' => $publicFields,
            'gmFields' => $gmFields,
            'hasPublicFields' => $filled($publicFields),
            'hasGmFields' => $filled($gmFields),
        ])->title($this->entity->name);
    }
}
