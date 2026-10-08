<?php

namespace App\Support;

use App\Models\BugReport;
use App\Models\Recette;
use Illuminate\Support\Facades\DB;

/**
 * Cahiers de recette livrés avec l'application (database/data/recettes/*.json) : chacun est
 * conservé dans la console d'administration, et ses bugs et écarts rejoignent le backlog.
 */
class RecetteImport
{
    /** @return list<string> titres des cahiers ajoutés */
    public static function all(): array
    {
        $added = [];

        foreach (glob(database_path('data/recettes/*.json')) ?: [] as $path) {
            $recette = self::file($path);

            if ($recette) {
                $added[] = $recette->title.' ('.$recette->version.')';
            }
        }

        return $added;
    }

    /** Ajoute un cahier ; null s'il est déjà là. */
    public static function file(string $path): ?Recette
    {
        $data = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        if (Recette::where('title', $data['title'])->where('version', $data['version'])->exists()) {
            return null;
        }

        return DB::transaction(function () use ($data) {
            $recette = Recette::create([
                'title' => $data['title'],
                'version' => $data['version'],
                'tested_on' => $data['tested_on'],
                'score' => $data['score'] ?? null,
                'score_before' => $data['score_before'] ?? null,
                'summary' => $data['summary'] ?? null,
            ]);

            $position = 0;
            foreach ($data['sections'] ?? [] as $section) {
                foreach ($section['items'] as $item) {
                    $recette->items()->create([
                        'section' => $section['name'],
                        'position' => ++$position,
                        'feature' => $item['feature'],
                        'expected' => $item['expected'],
                        'tests' => $item['tests'],
                        'gaps' => $item['gaps'],
                        'result' => $item['result'],
                        'score' => $item['score'],
                        'score_before' => $item['score_before'] ?? null,
                    ]);
                }
            }

            foreach ($data['backlog'] ?? [] as $entry) {
                BugReport::create([
                    'kind' => $entry['kind'],
                    'title' => $entry['title'],
                    'message' => $entry['message'],
                    'status' => $entry['status'],
                    'priority' => $entry['priority'],
                    'fixed_in' => $entry['fixed_in'] ?? null,
                    'source' => 'recette',
                    'version' => $data['version'],
                ]);
            }

            return $recette;
        });
    }
}
