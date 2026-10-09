<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * Manifeste de l'application installable, dans la langue de la personne : nom, description
 * et langue suivent l'interface. Icônes « maskable » à part, avec leur marge de sécurité.
 */
class ManifestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'name' => 'LoreMundi',
            'short_name' => 'LoreMundi',
            'description' => __('L’assistant du maître de jeu de rôle et de ses joueurs.'),
            'lang' => str_replace('_', '-', app()->getLocale()),
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => '#faf8f4',
            'theme_color' => '#1d2633',
            'icons' => [
                ['src' => '/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icons/icon-maskable-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
                ['src' => '/icons/icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json', 'Cache-Control' => 'public, max-age=3600', 'Vary' => 'Cookie, Accept-Language'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
