<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Support\Import\AiImportPrompt;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * « Préparer l'import avec une IA » : le prompt à copier, ou à télécharger en texte ou en skill Claude.
 */
class AiImportPromptController extends Controller
{
    public function show(Campaign $campaign): View
    {
        Gate::authorize('update', $campaign);

        return view('pages.import-ai', ['campaign' => $campaign, 'prompt' => AiImportPrompt::for($campaign)]);
    }

    public function markdown(Campaign $campaign): Response
    {
        Gate::authorize('update', $campaign);

        return response(AiImportPrompt::for($campaign), 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="loremundi-prompt-import.md"',
        ]);
    }

    public function skill(Campaign $campaign): BinaryFileResponse
    {
        Gate::authorize('update', $campaign);

        return response()->download(AiImportPrompt::skillArchive($campaign), 'loremundi-import-skill.zip', ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend();
    }
}
