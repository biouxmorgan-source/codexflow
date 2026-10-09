<?php

namespace App\Http\Middleware;

use App\Models\Campaign;
use App\Support\CampaignFeatures;
use App\Support\Plans\Plans;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Page d'une fonction que le MJ peut couper dans sa campagne, ou que la formule
 * du propriétaire peut ne pas comprendre. Rejoué à chaque action Livewire de la page,
 * pour qu'une page restée ouverte cesse d'agir dès que la fonction est coupée.
 */
class EnsureFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $campaign = $request->route('campaign');
        // Rejoué par Livewire à chaque action d'une page ouverte (middleware persistant) : la campagne peut n'être qu'un identifiant.
        $campaign = is_numeric($campaign) ? Campaign::find($campaign) : $campaign;

        if ($campaign instanceof Campaign) {
            abort_unless(CampaignFeatures::enabled($campaign, $feature), 403, CampaignFeatures::DISABLED);
        }

        $owner = $campaign instanceof Campaign ? $campaign->owner : $request->user();

        abort_if($owner && ! Plans::allows($owner, $feature), 403, Plans::NOT_INCLUDED);

        return $next($request);
    }
}
