<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Document;
use App\Models\Entity;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Les fichiers sont stockés hors du dossier public : chaque téléchargement passe par les policies.
 */
class FileController
{
    /** Types affichés directement dans le navigateur ; les autres sont téléchargés. */
    private const INLINE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];

    public function attachment(Attachment $attachment): StreamedResponse
    {
        Gate::authorize('view', $attachment);

        $disposition = in_array($attachment->mime_type, self::INLINE_TYPES, true) ? 'inline' : 'attachment';

        return Storage::disk($attachment->disk)->response(
            $attachment->path,
            $attachment->original_name,
            ['Content-Type' => $attachment->mime_type, 'X-Content-Type-Options' => 'nosniff'],
            $disposition,
        );
    }

    public function document(Document $document): StreamedResponse
    {
        Gate::authorize('view', $document);

        $disposition = in_array($document->mime_type, self::INLINE_TYPES, true) ? 'inline' : 'attachment';

        return Storage::disk($document->disk)->response(
            $document->path,
            $document->original_name,
            ['Content-Type' => $document->mime_type, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, max-age=3600'],
            $disposition,
        );
    }

    public function entityImage(Entity $entity): StreamedResponse
    {
        Gate::authorize('view', $entity);
        abort_unless($entity->hasImage(), 404);

        return Storage::disk(Entity::FILES_DISK)->response($entity->image_path, null, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
