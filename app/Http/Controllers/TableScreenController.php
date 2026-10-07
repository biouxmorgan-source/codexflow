<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Entity;
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

        abort_unless(($display['kind'] ?? null) === 'document', 404);
        $document = $display['document'];
        abort_unless(in_array($document->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'], true), 404);

        return Storage::disk($document->disk)->response($document->path, $document->original_name, [
            'Content-Type' => $document->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], 'inline');
    }

    public function image(Campaign $campaign): StreamedResponse
    {
        abort_unless(TableDisplay::canWatch(auth()->user(), $campaign), 403);
        $display = TableDisplay::current($campaign);

        abort_unless(($display['kind'] ?? null) === 'entity' && $display['entity']->hasImage(), 404);

        return Storage::disk(Entity::FILES_DISK)->response($display['entity']->image_path, null, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
