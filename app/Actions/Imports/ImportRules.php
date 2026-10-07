<?php

namespace App\Actions\Imports;

use App\Enums\RuleOrigin;
use App\Enums\RuleStatus;
use App\Enums\Zone;
use App\Models\Campaign;
use App\Models\Rule;
use App\Models\Tag;
use App\Models\User;
use App\Support\Import\Normalize;
use App\Support\Import\TabularFile;
use Illuminate\Support\Facades\DB;

/**
 * Import d'une liste de règles ou d'aides de jeu (une ligne par règle). Colonnes reconnues :
 * Titre, Catégorie, Résumé, Procédure, Notes MJ, Source, Origine, Statut, Zone, Tags.
 */
class ImportRules
{
    public const COLUMNS = [
        'title' => ['titre', 'nom', 'title', 'name', 'terme'],
        'category' => ['categorie', 'category', 'rubrique'],
        'summary' => ['resume', 'summary', 'definition'],
        'procedure' => ['procedure', 'texte', 'description', 'regle', 'contenu'],
        'gm_notes' => ['notesmj', 'notes', 'gmnotes', 'secret'],
        'source' => ['source', 'reference', 'page'],
        'origin' => ['origine', 'origin'],
        'status' => ['statut', 'status'],
        'zone' => ['zone', 'visibilite'],
        'tags' => ['tags', 'tag', 'etiquettes', 'motscles'],
    ];

    private const LIMITS = ['title' => 255, 'category' => 100, 'summary' => 2000, 'procedure' => 20000, 'gm_notes' => 20000, 'source' => 255, 'tags' => 1000];

    /** @var array<string, int> */
    private array $columns = [];

    /**
     * @param  string  $scope  « game » (règle du jeu, partagée par ses campagnes) ou « campaign »
     */
    public function __construct(
        private readonly Campaign $campaign,
        private readonly User $user,
        private readonly TabularFile $table,
        private readonly string $scope,
        private readonly bool $updateExisting = true,
    ) {
        foreach ($table->headers as $index => $header) {
            foreach (self::COLUMNS as $target => $aliases) {
                if (! isset($this->columns[$target]) && in_array(Normalize::key($header), $aliases, true)) {
                    $this->columns[$target] = $index;
                }
            }
        }
    }

    /**
     * @return array{
     *     errors: list<string>,
     *     rows: list<array{line: int, name: string, category: string, zone: string, action: string, errors: list<string>}>,
     *     valid: int,
     * }
     */
    public function plan(): array
    {
        return $this->build()['plan'];
    }

    /**
     * @return array{created: int, updated: int}
     */
    public function run(): array
    {
        $build = $this->build();

        return DB::transaction(function () use ($build) {
            $created = 0;
            $updated = 0;

            foreach ($build['valid'] as $row) {
                $rule = $row['existing'] ?? new Rule;

                foreach ($row['attributes'] as $attribute => $value) {
                    if ($value !== null || ! $rule->exists) {
                        $rule->{$attribute} = $value;
                    }
                }

                if ($rule->exists) {
                    $updated++;
                } else {
                    $rule->owner()->associate($this->user);
                    $rule->game_system_id = $this->scope === 'game' ? $this->campaign->game_system_id : null;
                    $rule->campaign_id = $this->scope === 'game' ? null : $this->campaign->id;
                    $created++;
                }

                $rule->save();

                if ($row['tags'] !== '') {
                    $rule->tags()->syncWithoutDetaching(Tag::idsFromInput($this->user, $row['tags']));
                }
            }

            return ['created' => $created, 'updated' => $updated];
        });
    }

    private function build(): array
    {
        if (! isset($this->columns['title'])) {
            return [
                'plan' => ['errors' => [__('Le fichier doit avoir une colonne « Titre ».')], 'rows' => [], 'valid' => 0],
                'valid' => [],
            ];
        }

        // Seules les règles du rattachement choisi peuvent être mises à jour.
        $existing = Rule::query()
            ->when($this->scope === 'game',
                fn ($q) => $q->where('game_system_id', $this->campaign->game_system_id),
                fn ($q) => $q->where('campaign_id', $this->campaign->id))
            ->get()
            ->keyBy(fn (Rule $rule) => mb_strtolower($rule->title));

        $rows = [];
        $valid = [];
        $seen = [];

        foreach ($this->table->rows as $row) {
            $cell = fn (string $target) => isset($this->columns[$target]) ? ($row['cells'][$this->columns[$target]] ?? '') : '';
            $errors = [];

            foreach (self::LIMITS as $target => $limit) {
                if (mb_strlen($cell($target)) > $limit) {
                    $errors[] = $target === 'title' ? __('titre trop long (:max caractères maximum)', ['max' => $limit]) : __(':field : dépasse :max caractères', ['field' => $this->label($target), 'max' => $limit]);
                }
            }

            $title = $cell('title');
            $origin = self::origin($cell('origin'));
            $status = self::status($cell('status'));
            $zone = Normalize::zone($cell('zone'));

            if ($title === '') {
                $errors[] = __('titre manquant');
            } elseif (isset($seen[mb_strtolower($title)])) {
                $errors[] = __('déjà présente ligne :line', ['line' => $seen[mb_strtolower($title)]]);
            } else {
                $seen[mb_strtolower($title)] = $row['line'];
            }

            if ($origin === null) {
                $errors[] = __('origine « :name » inconnue (référence, maison ou test)', ['name' => $cell('origin')]);
            }

            if ($status === null) {
                $errors[] = __('statut « :name » inconnu', ['name' => $cell('status')]);
            }

            if ($zone === null) {
                $errors[] = __('zone « :name » inconnue (publique ou MJ)', ['name' => $cell('zone')]);
            }

            $match = $this->updateExisting && $title !== '' ? $existing->get(mb_strtolower($title)) : null;

            $rows[] = [
                'line' => $row['line'],
                'name' => $title,
                'category' => $cell('category'),
                'zone' => $zone?->label() ?? $cell('zone'),
                'action' => $match ? 'update' : 'create',
                'errors' => $errors,
            ];

            if ($errors === []) {
                $valid[] = [
                    'existing' => $match,
                    'tags' => $cell('tags'),
                    // Une case vide ne remplace pas la valeur d'une règle existante.
                    'attributes' => [
                        'title' => $title,
                        'category' => $cell('category') ?: null,
                        'summary' => $cell('summary') ?: null,
                        'procedure' => $cell('procedure') ?: null,
                        'gm_notes' => $cell('gm_notes') ?: null,
                        'source' => $cell('source') ?: null,
                        'origin' => $cell('origin') === '' && $match ? null : $origin,
                        'status' => $cell('status') === '' && $match ? null : $status,
                        'zone' => $cell('zone') === '' && $match ? null : $zone,
                    ],
                ];
            }
        }

        return ['plan' => ['errors' => [], 'rows' => $rows, 'valid' => count($valid)], 'valid' => $valid];
    }

    private function label(string $target): string
    {
        return match ($target) {
            'category' => __('catégorie'),
            'summary' => __('résumé'),
            'procedure' => __('procédure'),
            'gm_notes' => __('notes MJ'),
            'source' => __('source'),
            'tags' => __('tags'),
            default => $target,
        };
    }

    private static function origin(string $value): ?RuleOrigin
    {
        return match (Normalize::key($value)) {
            '', 'reference', 'officielle', 'livre' => RuleOrigin::Reference,
            'maison', 'house', 'perso', 'personnelle' => RuleOrigin::House,
            'test', 'essai' => RuleOrigin::Test,
            default => null,
        };
    }

    private static function status(string $value): ?RuleStatus
    {
        $key = Normalize::key($value);

        if ($key === '') {
            return RuleStatus::Available;
        }

        foreach (RuleStatus::cases() as $status) {
            // Libellé français (format d'échange) ou dans la langue de l'utilisateur.
            if (in_array($key, [Normalize::key($status->value), Normalize::key($status->label('fr')), Normalize::key($status->label())], true)) {
                return $status;
            }
        }

        return null;
    }
}
