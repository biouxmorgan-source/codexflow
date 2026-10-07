<?php

namespace App\Http\Middleware;

use App\Support\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/** Applique la langue de l'interface (compte, visite ou navigateur). */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Locale::resolve($request);
        app()->setLocale($locale);
        Carbon::setLocale($locale);

        // Retient la langue du navigateur d'un compte sans choix explicite, pour lui écrire
        // dans sa langue quand il n'est pas là (notifications).
        $user = $request->user();

        if ($user && ! isset($user->preferences['locale']) && ($user->preferences['browser_locale'] ?? null) !== $locale) {
            $user->forceFill(['preferences' => ['browser_locale' => $locale] + ($user->preferences ?? [])])->saveQuietly();
        }

        return $next($request);
    }
}
