<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Marque une réponse comme consultable hors ligne : le service worker ne garde en cache
 * que les pages portant cet en-tête (la fiche du personnage et ce qu'elle contient).
 * Le cache est vidé à la déconnexion.
 */
class AvailableOffline
{
    public const HEADER = 'X-Codexflow-Offline';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && $response->getStatusCode() === 200) {
            $response->headers->set(self::HEADER, '1');
        }

        return $response;
    }
}
