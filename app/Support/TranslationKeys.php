<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

/**
 * Textes à traduire : chaque __('…') ou trans_choice('…') du code, la clé étant le texte français.
 * Sert au test qui vérifie que chaque langue a toutes ses traductions, et à la commande lang:missing.
 */
class TranslationKeys
{
    private const PATTERN = '/(?<![\w>:$])(?:__|trans_choice)\(\s*(?:\'((?:[^\'\\\\]|\\\\.)*)\'|"((?:[^"\\\\]|\\\\.)*)")/s';

    /** @return list<string> */
    public static function all(): array
    {
        $keys = [];

        foreach ([app_path(), resource_path('views')] as $directory) {
            foreach (File::allFiles($directory) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                preg_match_all(self::PATTERN, $file->getContents(), $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $keys[] = isset($match[2]) && $match[2] !== ''
                        ? stripcslashes($match[2])
                        : str_replace(['\\\'', '\\\\'], ['\'', '\\'], $match[1]);
                }
            }
        }

        // Clés de groupe (validation.required…) : traduites par les fichiers lang/{langue}/*.php.
        $keys = array_filter($keys, fn (string $key) => ! preg_match('/^[a-z_]+\.[a-z_.]+$/', $key));

        $keys = array_values(array_unique($keys));
        sort($keys);

        return $keys;
    }

    /** @return list<string> textes sans traduction dans lang/{locale}.json */
    public static function missing(string $locale): array
    {
        $path = lang_path($locale.'.json');
        $translations = File::exists($path) ? json_decode(File::get($path), true) : [];

        return array_values(array_filter(self::all(), fn (string $key) => ! isset($translations[$key])));
    }
}
