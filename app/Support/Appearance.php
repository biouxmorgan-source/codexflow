<?php

namespace App\Support;

use App\Models\User;

/**
 * Préférences d'affichage d'un utilisateur : thème, couleur d'accent, taille du texte.
 * Appliquées par des attributs sur <html>, lus par resources/css/app.css.
 */
class Appearance
{
    public const CHOICES = [
        'theme' => ['system' => 'Comme l\'appareil', 'light' => 'Clair', 'dark' => 'Sombre'],
        'accent' => ['codex' => 'Sarcelle', 'blue' => 'Bleu nuit', 'green' => 'Vert forêt', 'violet' => 'Violet', 'red' => 'Bordeaux'],
        'size' => ['normal' => 'Normale', 'large' => 'Grande', 'xlarge' => 'Très grande'],
    ];

    public const DEFAULTS = ['theme' => 'system', 'accent' => 'codex', 'size' => 'normal'];

    /** @return array<string, string> attributs data-* pour <html> */
    public static function attributes(?User $user): array
    {
        $theme = $user?->preference('theme') ?? self::DEFAULTS['theme'];

        return [
            'data-theme-choice' => $theme,
            // Un choix explicite est rendu tel quel : la navigation Livewire recopie les attributs de <html>.
            ...($theme === 'system' ? [] : ['data-theme' => $theme]),
            'data-accent' => $user?->preference('accent') ?? self::DEFAULTS['accent'],
            'data-size' => $user?->preference('size') ?? self::DEFAULTS['size'],
        ];
    }
}
