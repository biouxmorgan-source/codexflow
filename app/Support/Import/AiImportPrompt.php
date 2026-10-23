<?php

namespace App\Support\Import;

use App\Enums\FieldType;
use App\Enums\Zone;
use App\Models\Campaign;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use ZipArchive;

/**
 * Prompt (en anglais) à coller dans une IA, avec les PDF d'un jeu de rôle, pour obtenir les fichiers
 * d'import de SagaWyn et leur guide. Il décrit les formats exacts de l'import et reprend
 * ce que la campagne contient déjà (jeu, types de fiche, champs) pour que l'IA s'y raccorde.
 * Rien n'est envoyé à une IA par SagaWyn : l'utilisateur colle le texte dans la sienne.
 */
class AiImportPrompt
{
    /** Vocabulaire de la colonne « Type » de l'import des champs. */
    private const FIELD_TYPES = [
        'text' => 'texte', 'long_text' => 'texte long', 'number' => 'nombre', 'boolean' => 'oui/non',
        'date' => 'date', 'select' => 'liste', 'counter' => 'compteur', 'link' => 'lien',
        'file' => 'fichier', 'entity' => 'référence',
    ];

    /**
     * Sans campagne, le prompt est générique : l'IA produit une archive de campagne (.zip)
     * qui crée jeu, monde et campagne d'un coup (« Mes campagnes → Importer une campagne »).
     *
     * Le prompt est écrit en anglais, que toutes les IA suivent le mieux ; $language (code d'une
     * langue de l'interface, celle du MJ par défaut) est la langue du contenu importé. Les noms des
     * écrans de SagaWyn restent dans la langue de l'interface, celle que le MJ a sous les yeux.
     */
    public static function for(?Campaign $campaign, ?string $language = null, bool $skill = false): string
    {
        $language = self::language($language);

        return trim(view('prompts.import-ia', [
            'language' => self::LANGUAGES[$language],
            'planned' => __('Ce qui est prévu', [], $language),
            'happened' => __('Ce qui s’est passé', [], $language),
            'ui' => fn (string $label, array $replace = []) => '"'.str_replace(['« ', ' »'], '"', __($label, $replace)).'"',
            'archive' => $campaign === null,
            'archiveFormat' => $campaign === null ? self::archiveFormat() : null,
            'campaign' => $campaign?->name,
            'game' => $campaign?->gameSystem->name,
            'world' => $campaign === null ? null : ($campaign->world?->name ?? 'none'),
            'types' => $campaign === null ? null : implode(', ', EntityType::query()->availableTo($campaign->owner)->orderBy('id')->pluck('name')->unique()->values()->all()),
            'fields' => $campaign === null ? [] : $campaign->gameSystem->fieldDefinitions()->with('entityType')->orderBy('position')->get()
                ->map(fn (FieldDefinition $field) => self::describe($field))
                ->all(),
            'skill' => $skill,
        ])->render())."\n";
    }

    /** La langue demandée si elle est connue, sinon celle de l'interface. */
    public static function language(?string $language): string
    {
        foreach ([$language, app()->getLocale()] as $code) {
            if ($code !== null && isset(self::LANGUAGES[$code])) {
                return $code;
            }
        }

        return 'fr';
    }

    /** Le format de l'archive (references/archive-format.md du skill), titres décalés d'un niveau. */
    private static function archiveFormat(): string
    {
        $text = (string) file_get_contents(resource_path('skills/sagawyn-import/references/archive-format.md'));
        $text = (string) preg_replace('/\A# .*\n+/', '', $text);

        return trim((string) preg_replace('/^(#{2,}) /m', '#$1 ', $text));
    }

    /** Fichiers joints au skill, à côté de SKILL.md (resources/skills/sagawyn-import). */
    private const SKILL_FILES = ['references/archive-format.md', 'scripts/check.py'];

    /**
     * Le même prompt sous forme de skill Claude (dossier avec SKILL.md, le format de l'archive
     * de campagne et un script de vérification des fichiers), dans une archive .zip.
     */
    public static function skillArchive(?Campaign $campaign, ?string $language = null): string
    {
        $skill = "---\nname: sagawyn-import\ndescription: Turns a tabletop RPG rulebook, scenario or campaign book (PDF) into SagaWyn import files (entries, fields, rules, scenes, named documents) with the book's full content and an import guide, written in the language the user asks for.\n---\n\n"
            .self::for($campaign, $language, skill: true);

        $path = tempnam(sys_get_temp_dir(), 'sagawyn-skill-');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('sagawyn-import/SKILL.md', $skill);

        foreach (self::SKILL_FILES as $file) {
            $zip->addFile(resource_path('skills/sagawyn-import/'.$file), 'sagawyn-import/'.$file);
        }

        $zip->close();

        return $path;
    }

    private static function describe(FieldDefinition $field): string
    {
        $parts = [
            'type `'.(self::FIELD_TYPES[$field->type->value] ?? $field->type->value).'`',
            $field->zone === Zone::GameMaster ? 'zone `MJ`' : 'zone `publique`',
        ];

        if ($field->group) {
            $parts[] = 'group '.$field->group;
        }

        if ($field->type === FieldType::Select && $field->options) {
            $parts[] = 'choices '.implode('|', $field->options);
        }

        if ($field->entity_type_id !== null) {
            $parts[] = 'entry type '.$field->typeLabel();
        }

        return '"'.$field->name.'": '.implode(', ', $parts);
    }

    /** Langues proposées pour le contenu importé, nommées en anglais comme le reste du prompt. */
    public const LANGUAGES = [
        'fr' => 'French', 'en' => 'English', 'de' => 'German', 'es' => 'Spanish',
        'it' => 'Italian', 'pt' => 'Portuguese', 'nl' => 'Dutch', 'pl' => 'Polish',
    ];
}
