<?php

namespace App\Actions\Duplication;

use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityRelation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Duplique une fiche au même endroit (même monde ou même campagne) : « Nom (copie) ».
 *
 * Copié : type, zones publique et MJ, valeurs de champs, image, pièces jointes (fichiers copiés),
 * étiquettes, relations (dans les deux sens, vers les mêmes fiches) et documents liés.
 * Non copié : les états propres aux campagnes d'une fiche du monde (campaign_entity_states),
 * la place de la fiche dans les scènes, les épingles et tout ce qui a été donné aux personnages.
 */
class DuplicateEntity
{
    public function handle(Entity $entity): Entity
    {
        return FileCopies::run(fn (FileCopies $files) => ActivityLog::batch(fn () => DB::transaction(function () use ($entity, $files) {
            $copy = $this->copySheet($entity, $files, self::copyName($entity->name));

            foreach (EntityRelation::where('from_entity_id', $entity->id)->get() as $relation) {
                $this->copyRelation($relation, $copy->id, $relation->to_entity_id, $relation->campaign_id);
            }

            foreach (EntityRelation::where('to_entity_id', $entity->id)->get() as $relation) {
                $this->copyRelation($relation, $relation->from_entity_id, $copy->id, $relation->campaign_id);
            }

            $copy->documents()->sync($entity->documents()->pluck('documents.id'));

            return $copy;
        })));
    }

    /**
     * Copie le contenu de la fiche (sans ses relations ni ses liens), au même endroit
     * ou dans $campaign. Appelé aussi par la duplication d'une campagne.
     */
    public function copySheet(Entity $entity, FileCopies $files, string $name, ?Campaign $campaign = null): Entity
    {
        $copy = $entity->replicate(['image_path']);
        $copy->setRelations([]);
        $copy->name = $name;

        if ($campaign !== null) {
            $copy->owner()->associate($campaign->user_id);
            $copy->world()->dissociate();
            $copy->campaign()->associate($campaign);
        }

        $copy->image_path = $files->copy(Entity::FILES_DISK, $entity->image_path, 'entities');
        $copy->save();

        $copy->tags()->sync($entity->tags()->pluck('tags.id'));

        foreach ($entity->attachments()->get() as $attachment) {
            $path = $files->copy($attachment->disk, $attachment->path, 'attachments/'.$copy->id);

            if ($path === null) {
                continue;
            }

            $duplicate = new Attachment($attachment->only(['zone', 'disk', 'original_name', 'mime_type', 'size']) + ['path' => $path]);
            $duplicate->owner()->associate($copy->user_id);
            $copy->attachments()->save($duplicate);
        }

        return $copy;
    }

    public function copyRelation(EntityRelation $relation, int $fromId, int $toId, ?int $campaignId): EntityRelation
    {
        $copy = $relation->replicate();
        $copy->setRelations([]);
        $copy->from_entity_id = $fromId;
        $copy->to_entity_id = $toId;
        $copy->campaign_id = $campaignId;
        $copy->save();

        return $copy;
    }

    /** « Nom (copie) », dans la limite de la colonne. */
    public static function copyName(string $name): string
    {
        return __(':name (copie)', ['name' => Str::limit($name, 240)]);
    }
}
