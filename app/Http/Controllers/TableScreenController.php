<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Entity;
use App\Models\MapToken;
use App\Support\TableDisplay;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Fichiers de l'écran de table : seulement ce que le MJ montre en ce moment, à tous les
 * membres de la campagne. Rien d'autre de la bibliothèque n'est servi aux joueurs ici.
 */
class TableScreenController extends Controller
{
    public function file(Campaign $campaign): StreamedResponse
    {
        abort_unless(TableDisplay::canWatch(auth()->user(), $campaign), 403);
        $display = TableDisplay::current($campaign);

        if (($display['kind'] ?? null) === 'attachment') {
            $attachment = $display['attachment'];

            return Storage::disk($attachment->disk)->response($attachment->path, $attachment->original_name, [
                'Content-Type' => $attachment->mime_type,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ], 'inline');
        }

        abort_unless(in_array($display['kind'] ?? null, ['document', 'map'], true), 404);
        $document = $display['kind'] === 'map' ? $display['map']->document : $display['document'];
        abort_unless(in_array($document->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'], true), 404);

        return Storage::disk($document->disk)->response($document->path, $document->original_name, [
            'Content-Type' => $document->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], 'inline');
    }

    /** Portrait d'un jeton visible de la carte affichée. */
    public function token(Campaign $campaign, MapToken $token): StreamedResponse
    {
        abort_unless(TableDisplay::canWatch(auth()->user(), $campaign), 403);
        $display = TableDisplay::current($campaign);

        abort_unless(($display['kind'] ?? null) === 'map', 404);
        $token = $display['map']->tokens->find($token->id);
        abort_unless($token?->hasPortrait(), 404);

        return Storage::disk(Entity::FILES_DISK)->response($token->entity->image_path, null, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=600',
        ]);
    }

    public function image(Campaign $campaign): StreamedResponse
    {
        abort_unless(TableDisplay::canWatch(auth()->user(), $campaign), 403);
        $display = TableDisplay::current($campaign);

        abort_unless(in_array($display['kind'] ?? null, ['entity', 'portrait'], true) && $display['entity']->hasImage(), 404);

        return Storage::disk(Entity::FILES_DISK)->response($display['entity']->image_path, null, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
