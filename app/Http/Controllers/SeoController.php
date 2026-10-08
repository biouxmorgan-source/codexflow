<?php

namespace App\Http\Controllers;

use App\Support\Locale;
use Illuminate\Http\Response;

/**
 * Ce que lisent les moteurs de recherche : seules les pages publiques (accueil, aide,
 * confidentialité) sont proposées ; l'application, derrière la connexion, n'est pas explorée.
 */
class SeoController extends Controller
{
    /** Chemins jamais explorés : l'application et les liens d'invitation (jetons). */
    private const PRIVATE_PATHS = ['/campagnes', '/admin', '/invitation', '/preferences', '/mes-donnees', '/notifications', '/recherche', '/abonnement', '/signaler-un-probleme'];

    public function robots(): Response
    {
        $lines = ['User-agent: *', ...array_map(fn (string $path) => 'Disallow: '.$path, self::PRIVATE_PATHS), '', 'Sitemap: '.route('sitemap')];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response
    {
        $locales = array_keys(Locale::available());
        $pages = [url('/') => '1.0', route('help') => '0.6', route('privacy') => '0.2'];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach ($pages as $page => $priority) {
            foreach ($locales as $locale) {
                $xml .= '  <url><loc>'.e($page.'?lang='.$locale).'</loc><priority>'.$priority.'</priority>';
                foreach ($locales as $alternate) {
                    $xml .= '<xhtml:link rel="alternate" hreflang="'.$alternate.'" href="'.e($page.'?lang='.$alternate).'"/>';
                }
                $xml .= '</url>'."\n";
            }
        }

        return response($xml.'</urlset>'."\n", 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
