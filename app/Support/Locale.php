<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Langue de l'interface : choix du compte, sinon langue du navigateur, sinon le français.
 * Les textes sont écrits en français dans le code ; lang/{langue}.json donne leur traduction.
 */
class Locale
{
    public const DEFAULT = 'fr';

    /** @return array<string, string> langues proposées, nommées dans leur propre langue */
    public static function available(): array
    {
        return config('codexflow.locales');
    }

    public static function isAvailable(?string $locale): bool
    {
        return $locale !== null && array_key_exists($locale, self::available());
    }

    /** Langue à utiliser pour cette requête ; ?lang=xx la fixe pour la visite (pages d'accueil). */
    public static function resolve(Request $request): string
    {
        $asked = $request->query('lang');

        if (is_string($asked) && self::isAvailable($asked) && $request->hasSession()) {
            $request->session()->put('locale', $asked);
        }

        $chosen = $request->user()?->preferences['locale'] ?? null;

        if (self::isAvailable($chosen)) {
            return $chosen;
        }

        $session = $request->hasSession() ? $request->session()->get('locale') : null;

        if (self::isAvailable($session)) {
            return $session;
        }

        return self::fromBrowser($request);
    }

    public static function fromBrowser(Request $request): string
    {
        foreach ($request->getLanguages() as $language) {
            $code = strtolower(substr($language, 0, 2));

            if (self::isAvailable($code)) {
                return $code;
            }
        }

        return self::DEFAULT;
    }

    /** Langue d'un autre utilisateur, pour lui écrire (notifications, messages enregistrés). */
    public static function for(?User $user): string
    {
        foreach (['locale', 'browser_locale'] as $key) {
            $locale = $user?->preferences[$key] ?? null;

            if (self::isAvailable($locale)) {
                return $locale;
            }
        }

        return self::DEFAULT;
    }
}
