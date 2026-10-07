<?php

namespace App\Support;

/**
 * Habillage de l'écran de table : fond, couleur du texte et typographie. Purement visuel,
 * il ne change rien à ce qui est affiché ni à qui peut le voir.
 */
final class TableTheme
{
    public const DEFAULT = 'nuit';

    /**
     * Pour chaque habillage : les classes du fond et du texte, et la police des titres.
     *
     * @var array<string, array{background: string, text: string, muted: string, accent: string, font: string}>
     */
    public const THEMES = [
        'nuit' => [
            'background' => 'bg-black',
            'text' => 'text-stone-100',
            'muted' => 'text-stone-300',
            'accent' => 'text-flow',
            'font' => 'font-sans',
        ],
        'parchemin' => [
            'background' => 'bg-[#efe3cb] bg-[radial-gradient(ellipse_at_center,#f7eedd_0%,#dcc9a4_100%)]',
            'text' => 'text-[#2b2117]',
            'muted' => 'text-[#5b4a34]',
            'accent' => 'text-[#8a4b1f]',
            'font' => 'font-serif',
        ],
        'ardoise' => [
            'background' => 'bg-[#1b2127] bg-[radial-gradient(ellipse_at_center,#2a333c_0%,#12171b_100%)]',
            'text' => 'text-[#e7edf2]',
            'muted' => 'text-[#a9b6c2]',
            'accent' => 'text-[#7fb2d4]',
            'font' => 'font-sans',
        ],
        'grimoire' => [
            'background' => 'bg-[#140f1c] bg-[radial-gradient(ellipse_at_center,#271c38_0%,#0d0a13_100%)]',
            'text' => 'text-[#ece3f7]',
            'muted' => 'text-[#bda9d6]',
            'accent' => 'text-[#c9a227]',
            'font' => 'font-serif',
        ],
    ];

    /** @return array<string, string> libellés dans la langue de l'interface */
    public static function labels(): array
    {
        return [
            'nuit' => __('Nuit'),
            'parchemin' => __('Parchemin'),
            'ardoise' => __('Ardoise'),
            'grimoire' => __('Grimoire'),
        ];
    }

    /** @return array{background: string, text: string, muted: string, accent: string, font: string} */
    public static function of(?string $name): array
    {
        return self::THEMES[$name] ?? self::THEMES[self::DEFAULT];
    }
}
