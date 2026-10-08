<?php

namespace App\Support\Import;

use App\Enums\FieldType;
use App\Enums\Zone;
use App\Models\Campaign;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use ZipArchive;

/**
 * Prompt à coller dans une IA, avec les PDF d'un jeu de rôle, pour obtenir les fichiers
 * d'import de LoreMundi et leur guide. Il décrit les formats exacts de l'import et reprend
 * ce que la campagne contient déjà (jeu, types de fiche, champs) pour que l'IA s'y raccorde.
 * Rien n'est envoyé à une IA par LoreMundi : l'utilisateur colle le texte dans la sienne.
 */
class AiImportPrompt
{
    /** Vocabulaire de la colonne « Type » de l'import des champs. */
    private const FIELD_TYPES = [
        'text' => 'texte', 'long_text' => 'texte long', 'number' => 'nombre', 'boolean' => 'oui/non',
        'date' => 'date', 'select' => 'liste', 'counter' => 'compteur', 'link' => 'lien',
        'file' => 'fichier', 'entity' => 'référence',
    ];

    public static function for(Campaign $campaign, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $types = EntityType::query()->availableTo($campaign->owner)->orderBy('id')->pluck('name')->unique()->values()->all();

        $fields = $campaign->gameSystem->fieldDefinitions()->with('entityType')->orderBy('position')->get()
            ->map(fn (FieldDefinition $field) => self::describe($field))
            ->all();

        return trim(view('prompts.import-ia', [
            'language' => self::LANGUAGES[$locale] ?? self::LANGUAGES['fr'],
            'campaign' => $campaign->name,
            'game' => $campaign->gameSystem->name,
            'world' => $campaign->world?->name ?? 'aucun',
            'types' => implode(', ', $types),
            'fields' => $fields,
        ])->render())."\n";
    }

    /**
     * Le même prompt sous forme de skill Claude (dossier avec SKILL.md), dans une archive .zip.
     */
    public static function skillArchive(Campaign $campaign, ?string $locale = null): string
    {
        $skill = "---\nname: loremundi-import\ndescription: Prépare les fichiers CSV d'import LoreMundi et leur guide à partir de PDF de jeu de rôle (fiches, champs, règles, scènes, aides de jeu).\n---\n\n"
            .self::for($campaign, $locale);

        $path = tempnam(sys_get_temp_dir(), 'loremundi-skill-');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('loremundi-import/SKILL.md', $skill);
        $zip->close();

        return $path;
    }

    private static function describe(FieldDefinition $field): string
    {
        $parts = [
            self::FIELD_TYPES[$field->type->value] ?? $field->type->value,
            $field->zone === Zone::GameMaster ? 'zone MJ' : 'zone publique',
        ];

        if ($field->group) {
            $parts[] = 'groupe '.$field->group;
        }

        if ($field->type === FieldType::Select && $field->options) {
            $parts[] = 'choix '.implode('|', $field->options);
        }

        if ($field->entityType) {
            $parts[] = 'fiches '.$field->entityType->name;
        }

        return '« '.$field->name.' » : '.implode(', ', $parts);
    }

    /** Langue dans laquelle l'IA écrit, nommée en français comme le reste du prompt. */
    private const LANGUAGES = [
        'fr' => 'français', 'en' => 'anglais', 'de' => 'allemand', 'es' => 'espagnol',
        'it' => 'italien', 'pt' => 'portugais', 'nl' => 'néerlandais', 'pl' => 'polonais',
    ];
}
