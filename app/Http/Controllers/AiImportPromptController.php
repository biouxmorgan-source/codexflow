<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Support\Import\AiImportPrompt;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * « Préparer l'import avec une IA » : le prompt à copier, ou à télécharger en texte ou en skill Claude.
 * ?lang= choisit la langue du contenu importé (celle de l'interface par défaut).
 */
class AiImportPromptController extends Controller
{
    public function show(Request $request, Campaign $campaign): View
    {
        Gate::authorize('update', $campaign);

        return $this->page($campaign, $request);
    }

    /** Version générique, sans campagne : l'IA produit une archive à importer depuis « Mes campagnes ». */
    public function showArchive(Request $request): View
    {
        return $this->page(null, $request);
    }

    public function markdownArchive(Request $request): Response
    {
        return $this->markdownResponse(AiImportPrompt::for(null, $request->string('lang')->value()));
    }

    public function skillArchive(Request $request): BinaryFileResponse
    {
        return $this->skillResponse(AiImportPrompt::skillArchive(null, $request->string('lang')->value()));
    }

    public function markdown(Request $request, Campaign $campaign): Response
    {
        Gate::authorize('update', $campaign);

        return $this->markdownResponse(AiImportPrompt::for($campaign, $request->string('lang')->value()));
    }

    public function skill(Request $request, Campaign $campaign): BinaryFileResponse
    {
        Gate::authorize('update', $campaign);

        return $this->skillResponse(AiImportPrompt::skillArchive($campaign, $request->string('lang')->value()));
    }

    private function page(?Campaign $campaign, Request $request): View
    {
        $language = AiImportPrompt::language($request->string('lang')->value());

        return view('pages.import-ai', [
            'campaign' => $campaign,
            'language' => $language,
            'prompt' => AiImportPrompt::for($campaign, $language),
        ]);
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
