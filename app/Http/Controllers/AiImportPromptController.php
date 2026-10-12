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

    /** Version générique, sans campagne : l'IA produit une archive à importer depuis « Mes campagnes ». */
    public function showArchive(): View
    {
        return view('pages.import-ai', ['campaign' => null, 'prompt' => AiImportPrompt::for(null)]);
    }

    public function markdownArchive(): Response
    {
        return $this->markdownResponse(AiImportPrompt::for(null));
    }

    public function skillArchive(): BinaryFileResponse
    {
        return $this->skillResponse(AiImportPrompt::skillArchive(null));
    }

    public function markdown(Campaign $campaign): Response
    {
        Gate::authorize('update', $campaign);

        return $this->markdownResponse(AiImportPrompt::for($campaign));
    }

    public function skill(Campaign $campaign): BinaryFileResponse
    {
        Gate::authorize('update', $campaign);

        return $this->skillResponse(AiImportPrompt::skillArchive($campaign));
    }

    private function markdownResponse(string $prompt): Response
    {
        return response($prompt, 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="sagawyn-prompt-import.md"',
        ]);
    }

    private function skillResponse(string $path): BinaryFileResponse
    {
        return response()->download($path, 'sagawyn-import-skill.zip', ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend();
    }
}
