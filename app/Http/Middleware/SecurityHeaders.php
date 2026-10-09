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
 * Tout vient du site par défaut : scripts (et scripts en ligne portant le nonce de la page),
 * images, polices (hébergées avec le site), connexions (plus le serveur temps réel Reverb),
 * formulaires (plus le paiement Stripe vers lequel on est redirigé).
 * 'unsafe-eval' reste nécessaire à Alpine, qui évalue les expressions x-on / x-data ;
 * 'unsafe-inline' pour les styles, que Livewire et Alpine posent directement sur les éléments.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Vite::useCspNonce();
        $response = $next($request);
        header_remove('X-Powered-By');
        $response->headers->remove('X-Powered-By');

        // Avec le serveur Vite de développement (composer run dev), scripts et styles viennent d'une autre origine.
        $scripts = Vite::isRunningHot() ? '' : implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob:",
            "font-src 'self' data:",
            "connect-src 'self'".self::realtime(),
            "worker-src 'self' blob:",
            "media-src 'self' blob:",
            "frame-src 'self'",
            "manifest-src 'self'",
            "form-action 'self' https://checkout.stripe.com https://billing.stripe.com",
        ]).'; ';

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

    /** Le serveur Reverb, s'il est configuré, pour les mises à jour en direct. */
    private static function realtime(): string
    {
        $options = config('broadcasting.connections.reverb.options', []);
        $host = $options['host'] ?? null;

        if (! is_string($host) || $host === '' || ! preg_match('/^[A-Za-z0-9.-]+$/', $host)) {
            return '';
        }

        $secure = ($options['scheme'] ?? 'https') === 'https';
        $port = (int) ($options['port'] ?? ($secure ? 443 : 80));

        return ' '.($secure ? 'wss' : 'ws').'://'.$host.':'.$port;
    }
}
