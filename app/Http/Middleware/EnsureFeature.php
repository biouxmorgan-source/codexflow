<?php

namespace App\Http\Middleware;

use App\Models\Campaign;
use App\Support\Plans\Plans;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Page d'une fonction que la formule du propriétaire de la campagne peut ne pas comprendre. */
class EnsureFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $campaign = $request->route('campaign');
        $owner = $campaign instanceof Campaign ? $campaign->owner : $request->user();

        abort_if($owner && ! Plans::allows($owner, $feature), 403, Plans::NOT_INCLUDED);

        return $next($request);
    }
}
