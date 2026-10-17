<?php

namespace App\Support;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * QR code en SVG, à scanner avec un téléphone (lien pour rejoindre l'écran de table).
 * Même bibliothèque que le QR code de la double authentification (Fortify).
 */
final class QrCode
{
    public static function svg(string $text, int $size = 192): string
    {
        $svg = (new Writer(new ImageRenderer(
            new RendererStyle($size, 1, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(28, 25, 23))),
            new SvgImageBackEnd,
        )))->writeString($text);

        // Sans la déclaration XML, pour l'insérer dans la page.
        return trim(substr($svg, strpos($svg, "\n") + 1));
    }
}
