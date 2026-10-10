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

    /**
     * Couverture d'un jeu ou d'un monde : un ciel dégradé, une lune voilée, des bancs de brume
     * et une ligne d'îles. Une image large, au format d'une bannière.
     */
    public static function cover(string $sky, string $sea, int $width = 1200, int $height = 630): string
    {
        $image = imagecreatetruecolor($width, $height);
        [$r, $g, $b] = self::rgb($sky);
        [$sr, $sg, $sb] = self::rgb($sea);
        $horizon = (int) ($height * 0.62);

        for ($y = 0; $y < $height; $y++) {
            [$cr, $cg, $cb, $shade] = $y < $horizon ? [$r, $g, $b, 0.55 + 0.45 * ($y / $horizon)] : [$sr, $sg, $sb, 1 - 0.5 * (($y - $horizon) / ($height - $horizon))];
            $line = imagecolorallocate($image, (int) ($cr * $shade), (int) ($cg * $shade), (int) ($cb * $shade));
            imagefilledrectangle($image, 0, $y, $width, $y, $line);
        }

        $moon = imagecolorallocatealpha($image, 255, 246, 225, 40);
        imagefilledellipse($image, (int) ($width * 0.72), (int) ($height * 0.3), (int) ($height * 0.22), (int) ($height * 0.22), $moon);

        // Îles sombres posées sur l'horizon.
        $land = imagecolorallocate($image, (int) ($sr * 0.35), (int) ($sg * 0.35), (int) ($sb * 0.35));
        foreach ([[0.05, 0.3, 0.09], [0.38, 0.62, 0.05], [0.8, 1.02, 0.12]] as [$from, $to, $rise]) {
            imagefilledpolygon($image, [
                (int) ($width * $from), $horizon,
                (int) ($width * ($from + ($to - $from) * 0.35)), (int) ($horizon - $height * $rise),
                (int) ($width * ($from + ($to - $from) * 0.6)), (int) ($horizon - $height * $rise * 0.7),
                (int) ($width * $to), $horizon,
            ], $land);
        }

        // Bancs de brume, translucides.
        $mist = imagecolorallocatealpha($image, 240, 240, 236, 100);
        foreach ([0.5, 0.6, 0.7, 0.82] as $i => $level) {
            imagefilledellipse($image, (int) ($width * (0.2 + 0.25 * $i)), (int) ($height * $level), (int) ($width * 0.7), (int) ($height * 0.08), $mist);
        }

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

    /**
     * Ambiance sonore en boucle, synthétisée (aucun enregistrement, donc aucun droit d'auteur) :
     * « tide » la marée sur les marches, « mist » un bourdon de brume, « storm » l'orage sur le phare.
     * WAV mono 16 bits ; la fin se fond dans le début pour que la boucle ne s'entende pas.
     */
    public static function ambience(string $kind, int $seconds = 12, int $rate = 16000): string
    {
        mt_srand(crc32($kind));
        $length = $seconds * $rate;
        $fade = (int) ($rate * 1.5);
        $samples = [];
        $low = $rumble = $air = 0.0;

        for ($i = 0; $i < $length + $fade; $i++) {
            $t = $i / $rate;
            $white = mt_rand() / mt_getrandmax() * 2 - 1;
            $low += 0.04 * ($white - $low);
            $rumble += 0.008 * ($white - $rumble);
            $air += 0.3 * ($white - $air);

            $samples[] = match ($kind) {
                // Deux vagues par boucle : un souffle grave qui monte et se retire.
                'tide' => $low * (0.25 + 0.75 * sin(M_PI * $t / ($seconds / 2)) ** 2) * 2.5 + $air * 0.04,
                // Accord grave tenu (fréquences entières sur la boucle), qui respire, et un peu d'air.
                'mist' => (sin(2 * M_PI * 55 * $t) + 0.6 * sin(2 * M_PI * 82.5 * $t) + 0.35 * sin(2 * M_PI * 110 * $t))
                    * (0.55 + 0.45 * sin(2 * M_PI * $t / ($seconds / 3))) * 0.3 + $low * 0.6,
                // Pluie continue, grondement de fond, et un coup de tonnerre qui roule à 4 s.
                default => $air * 0.18 + $rumble * 4 + ($t > 4 && $t < 9 ? $rumble * 14 * exp(-($t - 4) * 0.9) * (0.6 + 0.4 * sin($t * 23)) : 0),
            };
        }

        for ($i = 0; $i < $fade; $i++) {
            $mix = $i / $fade;
            $samples[$i] = $samples[$i] * $mix + $samples[$length + $i] * (1 - $mix);
        }
        $samples = array_slice($samples, 0, $length);

        $peak = max(0.0001, max(array_map('abs', $samples)));
        $pcm = '';
        foreach ($samples as $sample) {
            $pcm .= pack('v', (int) round($sample / $peak * 0.8 * 32767) & 0xFFFF);
        }

        return 'RIFF'.pack('V', 36 + strlen($pcm)).'WAVEfmt '.pack('VvvVVvv', 16, 1, 1, $rate, $rate * 2, 2, 16)
            .'data'.pack('V', strlen($pcm)).$pcm;
    }

    private static function png(\GdImage $image): string
    {
        ob_start();
        imagepng($image, null, 8);

        return (string) ob_get_clean();
    }
}
