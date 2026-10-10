<?php

namespace App\Http\Middleware;

use App\Support\Monitoring\ResponseTimes;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mesure la durée de chaque requête, du démarrage de Laravel à la réponse prête,
 * et l'ajoute aux totaux de l'heure une fois la réponse envoyée.
 */
class RecordResponseTime
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $start = defined('LARAVEL_START') ? LARAVEL_START : (float) $request->server('REQUEST_TIME_FLOAT', microtime(true));
        $request->attributes->set('response_ms', (int) round((microtime(true) - $start) * 1000));

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        $ms = $request->attributes->get('response_ms');

        if (is_int($ms)) {
            ResponseTimes::record($ms, $response->getStatusCode() >= 500);
        }
    }
}
