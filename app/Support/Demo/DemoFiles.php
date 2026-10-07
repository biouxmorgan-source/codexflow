<?php

namespace App\Support\Demo;

/**
 * Fichiers de la campagne de démonstration, dessinés par le code plutôt que livrés en binaire :
 * le dépôt reste lisible, et la démonstration montre quand même des portraits, une carte et des PDF.
 */
final class DemoFiles
{
    /** Portrait stylisé : une silhouette claire sur un fond coloré, en PNG carré. */
    public static function portrait(string $color, int $size = 480): string
    {
        $image = imagecreatetruecolor($size, $size);
        [$r, $g, $b] = self::rgb($color);

        // Fond dégradé, du ton choisi vers une version plus sombre.
        for ($y = 0; $y < $size; $y++) {
            $shade = 1 - 0.45 * ($y / $size);
            $line = imagecolorallocate($image, (int) ($r * $shade), (int) ($g * $shade), (int) ($b * $shade));
            imagefilledrectangle($image, 0, $y, $size, $y, $line);
        }

        $light = imagecolorallocatealpha($image, 255, 252, 245, 30);
        imagefilledellipse($image, (int) ($size * 0.5), (int) ($size * 0.38), (int) ($size * 0.3), (int) ($size * 0.33), $light);
        imagefilledellipse($image, (int) ($size * 0.5), (int) ($size * 1.05), (int) ($size * 0.78), (int) ($size * 0.95), $light);

        return self::png($image);
    }

    /** Carte du port de Pierrecendre : eau, quais, bâtiments, routes. Un plan, pas une illustration. */
    public static function map(int $width = 1600, int $height = 1100): string
    {
        $image = imagecreatetruecolor($width, $height);
        $water = imagecolorallocate($image, 38, 66, 84);
        $land = imagecolorallocate($image, 72, 66, 58);
        $ash = imagecolorallocate($image, 96, 89, 80);
        $stone = imagecolorallocate($image, 142, 133, 120);
        $roof = imagecolorallocate($image, 58, 44, 38);
        $lantern = imagecolorallocate($image, 214, 164, 74);

        imagefilledrectangle($image, 0, 0, $width, $height, $water);

        // La ville est bâtie sur la coulée de cendre : une masse de terre entaillée par la baie.
        imagefilledpolygon($image, [
            0, 0, $width, 0, $width, 520, 1180, 470, 1020, 620, 760, 600, 600, 760, 330, 700, 0, 560,
        ], $land);
        imagefilledpolygon($image, [0, 0, 420, 0, 300, 240, 0, 300], $ash);

        // Quais le long de la baie.
        imagefilledrectangle($image, 300, 660, 1020, 700, $stone);
        imagefilledrectangle($image, 560, 700, 600, 880, $stone);
        imagefilledrectangle($image, 820, 700, 860, 820, $stone);

        // Routes.
        imagesetthickness($image, 14);
        imageline($image, 660, 660, 700, 120, $ash);
        imageline($image, 380, 640, 1300, 300, $ash);
        imagesetthickness($image, 1);

        // Bâtiments : des rectangles, posés le long des routes.
        foreach ([
            [420, 470, 180, 120], [640, 430, 150, 130], [830, 480, 210, 110],
            [1100, 360, 180, 140], [500, 250, 160, 120], [760, 180, 200, 150],
            [1180, 560, 150, 100], [300, 360, 120, 100],
        ] as [$x, $y, $w, $h]) {
            imagefilledrectangle($image, $x, $y, $x + $w, $y + $h, $roof);
            imagefilledrectangle($image, $x + 10, $y + 10, $x + $w - 10, $y + $h - 10, $stone);
        }

        // La Halle des Serments, plus grande, et le phare sur sa pointe.
        imagefilledpolygon($image, [900, 120, 1120, 120, 1180, 300, 840, 300], $roof);
        imagefilledpolygon($image, [920, 150, 1100, 150, 1150, 280, 870, 280], $stone);
        imagefilledrectangle($image, 1420, 760, 1470, 980, $stone);
        imagefilledellipse($image, 1445, 750, 70, 70, $lantern);

        // Lanternes du quai.
        for ($x = 340; $x < 1020; $x += 120) {
            imagefilledellipse($image, $x, 640, 16, 16, $lantern);
        }

        return self::png($image);
    }

    /** PDF d'une page, en Helvetica, pour les documents de la démonstration. */
    public static function pdf(string $title, array $lines): string
    {
        $stream = 'BT /F1 20 Tf 60 740 Td ('.self::escape($title).") Tj ET\n";
        $y = 700;

        foreach ($lines as $line) {
            $stream .= 'BT /F1 12 Tf 60 '.$y.' Td ('.self::escape($line).") Tj ET\n";
            $y -= 22;
        }

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$object}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf.'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    }

    /** Le PDF n'embarque pas de police : les accents passent par l'encodage WinAnsi. */
    private static function escape(string $text): string
    {
        $converted = @iconv('UTF-8', 'CP1252//TRANSLIT', $text);

        return addcslashes($converted === false ? $text : $converted, '()\\');
    }

    /** @return array{0: int, 1: int, 2: int} */
    private static function rgb(string $hex): array
    {
        $value = (int) hexdec(ltrim($hex, '#'));

        return [($value >> 16) & 255, ($value >> 8) & 255, $value & 255];
    }

    private static function png(\GdImage $image): string
    {
        ob_start();
        imagepng($image, null, 8);

        return (string) ob_get_clean();
    }
}
