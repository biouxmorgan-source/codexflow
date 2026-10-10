<?php

namespace App\Http\Controllers;

use App\Enums\FieldType;
use App\Enums\Zone;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\FieldDefinition;
use App\Models\Rule;
use App\Models\Scenario;
use App\Models\Scene;
use App\Support\Import\Normalize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Export CSV dans le format exact de l'import : le fichier obtenu peut servir de modèle
 * pour préparer d'autres données, puis être réimporté tel quel.
 * Réservé au MJ : l'export contient les notes MJ et la zone MJ des fiches.
 */
class ExportController extends Controller
{
    private const FILENAMES = ['fiches' => 'fiches', 'champs' => 'champs', 'regles' => 'regles', 'scenes' => 'scenes'];

    public function __invoke(Request $request, Campaign $campaign, string $kind): StreamedResponse
    {
        Gate::authorize('update', $campaign);

        $rows = match ($kind) {
            'fiches' => $this->entities($campaign, $request->integer('type') ?: null),
            'champs' => $this->fields($campaign),
            'regles' => $this->rules($campaign),
            'scenes' => $this->scenes($campaign),
        };

        $filename = 'sagawyn-'.str($campaign->name)->slug().'-'.self::FILENAMES[$kind].'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            foreach ($rows as $row) {
                fputcsv($out, $row, ';', '"', '');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return list<list<string>> */
    private function entities(Campaign $campaign, ?int $typeId): array
    {
        $entities = $campaign->availableEntities()
            ->with('type')
            ->when($typeId, fn ($query) => $query->where('entity_type_id', $typeId))
            ->orderBy('name')
            ->get();

        $typeIds = $entities->pluck('entity_type_id')->unique();
        $definitions = $campaign->gameSystem->fieldDefinitions()->ordered()->get()
            ->filter(fn (FieldDefinition $definition) => $definition->typeIds() === [] || array_intersect($definition->typeIds(), $typeIds->all()) !== []);

        // Une colonne par nom de champ : un champ « FOR » propre aux personnages et un autre
        // propre aux créatures partagent la même colonne, comme à l'import.
        $columns = $definitions->groupBy(fn (FieldDefinition $definition) => Normalize::key($definition->name));

        $rows = [array_merge(['Nom', 'Type', 'Résumé', 'Description', 'Notes MJ'], $columns->map(fn ($group) => $group->first()->name)->values()->all())];

        foreach ($entities as $entity) {
            $row = [$entity->name, $entity->type->name, (string) $entity->summary, (string) $entity->description, (string) $entity->gm_notes];

            foreach ($columns as $group) {
                $definition = $group->first(fn (FieldDefinition $candidate) => in_array($entity->entity_type_id, $candidate->typeIds(), true))
                    ?? $group->first(fn (FieldDefinition $candidate) => $candidate->typeIds() === []);
                $value = $definition ? $entity->fieldValue($definition) : null;
                // Valeurs en français, comme les en-têtes : le fichier doit pouvoir être réimporté.
                $row[] = $value === null || $value === '' ? '' : $definition->type->format($value, 'fr');
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /** @return list<list<string>> */
    private function fields(Campaign $campaign): array
    {
        $keywords = [
            FieldType::Text->value => 'texte',
            FieldType::LongText->value => 'texte long',
            FieldType::Number->value => 'nombre',
            FieldType::Boolean->value => 'oui/non',
            FieldType::Date->value => 'date',
            FieldType::Select->value => 'liste',
            FieldType::Counter->value => 'compteur',
            FieldType::Link->value => 'lien',
            FieldType::File->value => 'fichier',
            FieldType::EntityRef->value => 'fiche',
        ];

        $rows = [['Nom', 'Groupe', 'Type', 'Zone', 'Choix', 'Type de fiche', 'Modifiable par le joueur']];

        foreach ($campaign->gameSystem->fieldDefinitions()->ordered()->get() as $definition) {
            $rows[] = [
                $definition->name,
                (string) $definition->group,
                $keywords[$definition->type->value],
                self::zone($definition->zone),
                implode('|', $definition->options ?? []),
                $definition->typeIds() === [] ? '' : str_replace(', ', '|', $definition->typeLabel()),
                $definition->player_editable ? 'oui' : 'non',
            ];
        }

        return $rows;
    }

    /** @return list<list<string>> */
    private function rules(Campaign $campaign): array
    {
        $rows = [['Titre', 'Catégorie', 'Résumé', 'Procédure', 'Notes MJ', 'Source', 'Origine', 'Statut', 'Zone', 'Tags']];

        foreach ($campaign->availableRules()->with('tags')->orderBy('category')->orderBy('title')->get() as $rule) {
            /** @var Rule $rule */
            $rows[] = [
                $rule->title,
                (string) $rule->category,
                (string) $rule->summary,
                (string) $rule->procedure,
                (string) $rule->gm_notes,
                (string) $rule->source,
                $rule->origin->label('fr'),
                $rule->status->label('fr'),
                self::zone($rule->zone),
                $rule->tags->pluck('name')->implode(', '),
            ];
        }

        return $rows;
    }

    /** @return list<list<string>> */
    private function scenes(Campaign $campaign): array
    {
        $rows = [['Scénario', 'Résumé du scénario', 'Chapitre', 'Scène', 'Description', 'Statut', 'Fiches', 'Documents', 'Règles']];

        $scenarios = $campaign->scenarios()->orderBy('position')
            ->with(['scenes' => fn ($query) => $query->orderBy('position')->with(['entities', 'documents', 'rules'])])
            ->get();

        foreach ($scenarios as $scenario) {
            /** @var Scenario $scenario */
            if ($scenario->scenes->isEmpty()) {
                continue;
            }

            foreach ($scenario->scenes as $index => $scene) {
                /** @var Scene $scene */
                $rows[] = [
                    $scenario->name,
                    $index === 0 ? (string) $scenario->summary : '',
                    (string) $scene->chapter,
                    $scene->name,
                    (string) $scene->description,
                    $scene->status->label('fr'),
                    $scene->entities->map(fn (Entity $entity) => $entity->name)->implode(' | '),
                    $scene->documents->pluck('title')->implode(' | '),
                    $scene->rules->pluck('title')->implode(' | '),
                ];
            }
        }

        return $rows;
    }

    private static function zone(Zone $zone): string
    {
        return $zone === Zone::GameMaster ? 'MJ' : 'publique';
    }
}
