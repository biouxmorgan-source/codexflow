<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * En-têtes de sécurité de chaque page : pas d'affichage dans le cadre d'un autre site,
 * pas de devinette du type de fichier, adresse de provenance réduite, HTTPS imposé en production.
 *
 * Scripts : seulement ceux du site et les scripts en ligne portant le nonce de la page.
 * 'unsafe-eval' reste nécessaire à Alpine, qui évalue les expressions x-on / x-data.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Vite::useCspNonce();
        $response = $next($request);
        header_remove('X-Powered-By');
        $response->headers->remove('X-Powered-By');

        // Avec le serveur Vite de développement (composer run dev), les scripts viennent d'une autre origine.
        $scripts = Vite::isRunningHot() ? '' : "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval'; ";

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('Content-Security-Policy', $scripts."frame-ancestors 'self'; object-src 'none'; base-uri 'self'");

        if (app()->isProduction() && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
