<?php

namespace App\Http\Controllers;

use App\Enums\Zone;
use App\Models\Attachment;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\PlayerCharacter;
use App\Support\EntityLinks;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Ce qu'un personnage a reçu, vu par son joueur : zone publique d'une fiche révélée,
 * documents donnés. Rien n'est servi sans révélation préalable au personnage.
 */
class CharacterKnowledgeController extends Controller
{
    public function entity(Campaign $campaign, PlayerCharacter $character, Entity $entity): View
    {
        $this->authorizeKnown($campaign, $character, $entity);

        // Mode « Voir comme… » du MJ : gardé de lien en lien, avec son bandeau.
        $viewAs = request()->boolean('comme') && $campaign->isGameMaster(request()->user()) ? ['comme' => 1] : [];
        $known = $character->grants()->where('kind', 'entity')->pluck('entity_id')->flip();
        $link = fn (Entity $linked) => isset($known[$linked->id])
            ? route('characters.entity', [$campaign, $character, $linked, ...$viewAs])
            : null;

        $fields = $campaign->gameSystem->fieldDefinitions()
            ->forType($entity->entity_type_id)
            ->where('zone', Zone::Public)
            ->ordered()
            ->get()
            ->filter(fn ($definition) => $entity->fieldValueIn($definition, $campaign) !== null);

        // Relations publiques vers des fiches que le personnage connaît (ou vers lui-même) : jamais
        // une relation de la zone MJ, jamais le nom d'une fiche qu'il ignore.
        $relations = $entity->relationsIn($campaign)->where('zone', Zone::Public)->orderBy('label')->get()
            ->filter(fn (EntityRelation $relation) => $relation->from !== null && $relation->to !== null)
            ->map(fn (EntityRelation $relation) => ['relation' => $relation, 'other' => $relation->otherSide($entity)])
            ->filter(fn (array $item) => $item['other']->id === $character->entity_id || isset($known[$item['other']->id]))
            ->values();

        return view('characters.entity', [
            'relations' => $relations,
            'attachments' => $entity->attachments()->where('zone', Zone::Public)->get(),
            'link' => $link,
            'campaign' => $campaign,
            'character' => $character->load('entity'),
            'entity' => $entity->load('type'),
            'fields' => $fields,
            'viewAs' => $viewAs,
            'description' => EntityLinks::render($entity->description, $campaign, $link),
        ]);
    }

    public function entityImage(Campaign $campaign, PlayerCharacter $character, Entity $entity): StreamedResponse
    {
        $this->authorizeKnown($campaign, $character, $entity);
        abort_unless($entity->hasImage(), 404);

        return Storage::disk(Entity::FILES_DISK)->response($entity->image_path, null, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /** Fichier joint à la zone publique d'une fiche révélée (illustration, plan…). */
    public function attachment(Campaign $campaign, PlayerCharacter $character, Entity $entity, Attachment $attachment): StreamedResponse
    {
        $this->authorizeKnown($campaign, $character, $entity);
        abort_unless($attachment->entity_id === $entity->id && $attachment->zone === Zone::Public, 404);

        return Storage::disk($attachment->disk)->response($attachment->path, $attachment->original_name, [
            'Content-Type' => $attachment->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600',
        ], $attachment->isImage() || $attachment->mime_type === 'application/pdf' ? 'inline' : 'attachment');
    }

    public function document(Campaign $campaign, PlayerCharacter $character, Document $document): StreamedResponse
    {
        abort_unless($character->campaign_id === $campaign->id, 404);
        Gate::authorize('view', $character);
        abort_unless($character->grants()->where('document_id', $document->id)->exists(), 404);

        return Storage::disk($document->disk)->response($document->path, $document->original_name, [
            'Content-Type' => $document->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600',
        ], 'inline');
    }

    private function authorizeKnown(Campaign $campaign, PlayerCharacter $character, Entity $entity): void
    {
        abort_unless($character->campaign_id === $campaign->id, 404);
        Gate::authorize('view', $character);
        // Même réponse qu'une fiche inexistante : on ne confirme pas l'existence d'un secret.
        abort_unless($entity->id !== $character->entity_id && $character->knows($entity), 404);
    }
}
