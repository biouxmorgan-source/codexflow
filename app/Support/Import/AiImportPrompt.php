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
     */
    public static function for(?Campaign $campaign, ?string $locale = null, bool $skill = false): string
    {
        $locale ??= app()->getLocale();

        return trim(view('prompts.import-ia', [
            'language' => self::LANGUAGES[$locale] ?? self::LANGUAGES['fr'],
            'archive' => $campaign === null,
            'archiveFormat' => $campaign === null ? self::archiveFormat() : null,
            'campaign' => $campaign?->name,
            'game' => $campaign?->gameSystem->name,
            'world' => $campaign === null ? null : ($campaign->world?->name ?? 'aucun'),
            'types' => $campaign === null ? null : implode(', ', EntityType::query()->availableTo($campaign->owner)->orderBy('id')->pluck('name')->unique()->values()->all()),
            'fields' => $campaign === null ? [] : $campaign->gameSystem->fieldDefinitions()->with('entityType')->orderBy('position')->get()
                ->map(fn (FieldDefinition $field) => self::describe($field))
                ->all(),
            'skill' => $skill,
        ])->render())."\n";
    }

    /** Le format de l'archive (references/format-archive.md du skill), titres décalés d'un niveau. */
    private static function archiveFormat(): string
    {
        $text = (string) file_get_contents(resource_path('skills/sagawyn-import/references/format-archive.md'));
        $text = (string) preg_replace('/\A# .*\n+/', '', $text);

        return trim((string) preg_replace('/^(#{2,}) /m', '#$1 ', $text));
    }

    /** Fichiers joints au skill, à côté de SKILL.md (resources/skills/sagawyn-import). */
    private const SKILL_FILES = ['references/format-archive.md', 'scripts/verifier.py'];

    /**
     * Le même prompt sous forme de skill Claude (dossier avec SKILL.md, le format de l'archive
     * de campagne et un script de vérification des fichiers), dans une archive .zip.
     */
    public static function skillArchive(?Campaign $campaign, ?string $locale = null): string
    {
        $skill = "---\nname: sagawyn-import\ndescription: Transforme un livre de scénario ou de campagne de jeu de rôle (PDF) en fichiers d'import SagaWyn (fiches, champs, règles, scènes, documents nommés) avec le contenu complet du livre et un guide d'import.\n---\n\n"
            .self::for($campaign, $locale, skill: true);

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
            self::FIELD_TYPES[$field->type->value] ?? $field->type->value,
            $field->zone === Zone::GameMaster ? 'zone MJ' : 'zone publique',
        ];

        if ($field->group) {
            $parts[] = 'groupe '.$field->group;
        }

        if ($field->type === FieldType::Select && $field->options) {
            $parts[] = 'choix '.implode('|', $field->options);
        }

        if ($field->entity_type_id !== null) {
            $parts[] = 'fiches '.$field->typeLabel();
        }

        return '« '.$field->name.' » : '.implode(', ', $parts);
    }

    /** Langue dans laquelle l'IA écrit, nommée en français comme le reste du prompt. */
    private const LANGUAGES = [
        'fr' => 'français', 'en' => 'anglais', 'de' => 'allemand', 'es' => 'espagnol',
        'it' => 'italien', 'pt' => 'portugais', 'nl' => 'néerlandais', 'pl' => 'polonais',
    ];
}
