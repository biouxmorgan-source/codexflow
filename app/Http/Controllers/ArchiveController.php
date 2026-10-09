<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Support\Archive\CampaignExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArchiveController extends Controller
{
    /** Archive complète de la campagne, pour la sauvegarder ou la confier à un autre MJ. */
    public function campaign(Request $request, Campaign $campaign): BinaryFileResponse
    {
        // L'archive du contenu préparé : propriétaire et co-MJ. La sauvegarde complète (la table,
        // ses messages et son journal) reste au propriétaire.
        $complete = $request->boolean('complete');
        Gate::authorize($complete ? 'duplicate' : 'update', $campaign);

        $path = (new CampaignExport($campaign, $complete))->write();

        return response()->download($path, 'loremundi-'.str($campaign->name)->slug().($complete ? '-sauvegarde-complete' : '').'.zip', ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend();
    }

    /** Modèle du jeu : types de fiche, champs, étiquettes et, au choix, règles. */
    public function template(Request $request, Campaign $campaign): StreamedResponse
    {
        Gate::authorize('update', $campaign);
        Gate::authorize('view', $campaign->gameSystem);

        $data = CampaignExport::template($campaign, $request->boolean('regles'));

        return response()->streamDownload(
            fn () => print (json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)),
            'loremundi-modele-'.str($data['name'])->slug().'.json',
            ['Content-Type' => 'application/json'],
        );
    }
}
