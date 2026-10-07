<?php

namespace App\Models;

use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\RuleOrigin;
use App\Enums\RuleStatus;
use App\Enums\SceneStatus;
use App\Enums\Zone;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Une entrée du journal d'audit : un élément créé, modifié ou supprimé, par qui, quand,
 * avec pour chaque attribut l'ancienne et la nouvelle valeur brutes (« diff »).
 * Réservé au MJ pour l'instant : les entrées contiennent la zone MJ.
 */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    /** Libellés des types d'éléments suivis. */
    public const SUBJECTS = [
        'entity' => 'Fiche',
        'entity_state' => 'Fiche (campagne)',
        'scenario' => 'Scénario',
        'scene' => 'Scène',
        'rule' => 'Règle',
        'document' => 'Document',
        'field' => 'Champ',
        'member' => 'Membre',
        'grant' => 'Élément donné',
    ];

    private const LABELS = [
        'name' => 'Nom', 'title' => 'Titre', 'summary' => 'Résumé', 'description' => 'Description',
        'gm_notes' => 'Notes MJ', 'entity_type_id' => 'Type', 'chapter' => 'Chapitre', 'status' => 'Statut',
        'category' => 'Catégorie', 'procedure' => 'Procédure', 'source' => 'Source', 'origin' => 'Origine',
        'zone' => 'Zone', 'group' => 'Groupe', 'type' => 'Type', 'options' => 'Choix', 'original_name' => 'Fichier',
        'image_path' => 'Image', 'scenario_id' => 'Scénario', 'world_id' => 'Monde', 'campaign_id' => 'Campagne',
        'role' => 'Rôle', 'character' => 'Personnage', 'kind' => 'Nature', 'body' => 'Texte', 'quantity' => 'Quantité', 'player_editable' => 'Modifiable par le joueur',
        'added_by_player' => 'Ajouté par le joueur', 'validated' => 'Validé par le MJ',
    ];

    private static ?string $batch = null;

    /** @return array<string, string> libellés traduits des types d'éléments suivis */
    public static function subjects(): array
    {
        return [
            'entity' => __('Fiche'),
            'entity_state' => __('Fiche (campagne)'),
            'scenario' => __('Scénario'),
            'scene' => __('Scène'),
            'rule' => __('Règle'),
            'document' => __('Document'),
            'field' => __('Champ'),
            'member' => __('Membre'),
            'grant' => __('Élément donné'),
        ];
    }

    /** @return array<string, string> libellés traduits des attributs (mêmes clés que LABELS) */
    private static function labels(): array
    {
        return [
            'name' => __('Nom'), 'title' => __('Titre'), 'summary' => __('Résumé'), 'description' => __('Description'),
            'gm_notes' => __('Notes MJ'), 'entity_type_id' => __('Type'), 'chapter' => __('Chapitre'), 'status' => __('Statut'),
            'category' => __('Catégorie'), 'procedure' => __('Procédure'), 'source' => __('Source'), 'origin' => __('Origine'),
            'zone' => __('Zone'), 'group' => __('Groupe'), 'type' => __('Type'), 'options' => __('Choix'), 'original_name' => __('Fichier'),
            'image_path' => __('Image'), 'scenario_id' => __('Scénario'), 'world_id' => __('Monde'), 'campaign_id' => __('Campagne'),
            'role' => __('Rôle'), 'character' => __('Personnage'), 'kind' => __('Nature'), 'body' => __('Texte'), 'quantity' => __('Quantité'), 'player_editable' => __('Modifiable par le joueur'),
            'added_by_player' => __('Ajouté par le joueur'), 'validated' => __('Validé par le MJ'),
        ];
    }

    protected function casts(): array
    {
        return ['diff' => 'array', 'created_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Regroupe les entrées écrites pendant $callback (un import) sous un même identifiant.
     */
    public static function batch(callable $callback): mixed
    {
        $previous = self::$batch;
        self::$batch = (string) Str::uuid();

        try {
            return $callback();
        } finally {
            self::$batch = $previous;
        }
    }

    /**
     * @param  array<string, array{old: mixed, new: mixed}>  $changes
     * @param  array{campaign_id?: ?int, world_id?: ?int, game_system_id?: ?int}  $scope
     */
    public static function record(string $type, int $id, string $label, string $event, array $changes, array $scope): void
    {
        static::query()->create($scope + [
            'user_id' => auth()->id(),
            'subject_type' => $type,
            'subject_id' => $id,
            'subject_label' => Str::limit($label, 250),
            'event' => $event,
            'diff' => $changes,
            'batch' => self::$batch,
        ]);
    }

    /**
     * Entrées visibles depuis une campagne : les siennes, celles de son monde et de son jeu.
     *
     * @param  Builder<ActivityLog>  $query
     */
    public function scopeForCampaign(Builder $query, Campaign $campaign): void
    {
        $query->where(function (Builder $query) use ($campaign) {
            $query->where('campaign_id', $campaign->id)
                ->orWhere('game_system_id', $campaign->game_system_id);

            if ($campaign->world_id) {
                $query->orWhere('world_id', $campaign->world_id);
            }
        });
    }

    public function subjectName(): string
    {
        if ($this->subject_type === 'grant') {
            $kind = $this->diff['kind']['new'] ?? $this->diff['kind']['old'] ?? null;

            return CharacterGrant::kinds()[$kind] ?? self::subjects()['grant'];
        }

        return self::subjects()[$this->subject_type] ?? $this->subject_type;
    }

    /** Échange entre deux personnages (et non don du MJ) : « Lampe de Harvey à Jack ». */
    public function isExchange(): bool
    {
        return $this->subject_type === 'grant' && isset($this->diff['character']['old'], $this->diff['character']['new']);
    }

    /** Connaissance notée ou objet ajouté par le joueur lui-même, ou validation par le MJ. */
    public function isPlayerAddition(): bool
    {
        return $this->subject_type === 'grant' && isset($this->diff['added_by_player']);
    }

    /** « Objet transmis », « Information transmise »… pour le journal d'un personnage. */
    public function exchangeTitle(): string
    {
        $kind = $this->diff['kind']['new'] ?? null;

        return in_array($kind, ['entity', 'information', 'rule'], true)
            ? __(':subject transmise', ['subject' => $this->subjectName()])
            : __(':subject transmis', ['subject' => $this->subjectName()]);
    }

    public function verb(?string $locale = null): string
    {
        if ($this->subject_type === 'grant') {
            $kind = $this->diff['kind']['new'] ?? $this->diff['kind']['old'] ?? null;

            return match (true) {
                $this->isExchange() => $kind === 'possession' ? __('a donné', [], $locale) : __('a transmis', [], $locale),
                $this->isPlayerAddition() => match ($this->event) {
                    'deleted' => __('a effacé', [], $locale),
                    'updated' => __('a validé', [], $locale),
                    default => $kind === 'possession' ? __('a ajouté', [], $locale) : __('a noté', [], $locale),
                },
                $this->event === 'deleted' => in_array($kind, ['entity', 'rule'], true) ? __('a caché', [], $locale) : __('a repris', [], $locale),
                in_array($kind, ['entity', 'information', 'rule'], true) => __('a révélé', [], $locale),
                default => __('a donné', [], $locale),
            };
        }

        if ($this->subject_type === 'member') {
            return match ($this->event) {
                'created' => __('a ajouté', [], $locale),
                'deleted' => __('a retiré', [], $locale),
                default => __('a modifié', [], $locale),
            };
        }

        return match ($this->event) {
            'created' => __('a créé', [], $locale),
            'deleted' => __('a supprimé', [], $locale),
            default => __('a modifié', [], $locale),
        };
    }

    /** Verbe seul, pour l'affichage compact du journal : « Créé », « Révélé »… */
    public function compactVerb(): string
    {
        return match ($this->verb(Locale::DEFAULT)) {
            'a donné' => __('Donné'),
            'a transmis' => __('Transmis'),
            'a effacé' => __('Effacé'),
            'a validé' => __('Validé'),
            'a ajouté' => __('Ajouté'),
            'a noté' => __('Noté'),
            'a caché' => __('Caché'),
            'a repris' => __('Repris'),
            'a révélé' => __('Révélé'),
            'a retiré' => __('Retiré'),
            'a créé' => __('Créé'),
            'a supprimé' => __('Supprimé'),
            default => __('Modifié'),
        };
    }

    /**
     * Modifications lisibles : [libellé, ancienne valeur, nouvelle valeur], valeurs déjà mises en forme.
     *
     * @param  array<int, FieldDefinition>  $definitions  définitions de champs indexées par id
     * @return list<array{0: string, 1: string, 2: string}>
     */
    public function lines(array $definitions = []): array
    {
        $lines = [];
        $order = array_flip(array_keys(self::LABELS));
        $diff = $this->diff ?? [];
        // jsonb ne garde pas l'ordre des clés : nom et titre d'abord, valeurs de champs ensuite.
        uksort($diff, fn (string $a, string $b) => [$order[$a] ?? 999, $a] <=> [$order[$b] ?? 999, $b]);

        foreach ($diff as $key => $change) {
            [$label, $format] = $this->describe($key, $definitions);
            $lines[] = [$label, $format($change['old'] ?? null), $format($change['new'] ?? null)];
        }

        return $lines;
    }

    /** @return array{0: string, 1: callable(mixed): string} */
    private function describe(string $key, array $definitions): array
    {
        $plain = fn (mixed $value): string => match (true) {
            $value === null, $value === '' => '',
            is_bool($value) => $value ? __('Oui') : __('Non'),
            is_array($value) => implode(', ', array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v), $value)),
            default => (string) $value,
        };

        if (preg_match('/^(field_values|overrides)\.(\d+)$/', $key, $match)) {
            $definition = $definitions[(int) $match[2]] ?? null;

            return [
                $definition?->name ?? __('Champ supprimé'),
                fn (mixed $value) => $value === null || $value === '' || ! $definition ? $plain($value) : $definition->type->format($value),
            ];
        }

        $enum = match ($key) {
            'status' => match ($this->subject_type) {
                'scene' => SceneStatus::class,
                'rule' => RuleStatus::class,
                default => null,
            },
            'origin' => RuleOrigin::class,
            'role' => CampaignRole::class,
            'zone' => Zone::class,
            'type' => $this->subject_type === 'field' ? FieldType::class : null,
            default => null,
        };

        if ($enum) {
            return [self::labels()[$key], fn (mixed $value) => is_string($value) && $enum::tryFrom($value) ? $enum::from($value)->label() : $plain($value)];
        }

        if ($key === 'entity_type_id') {
            return [$this->subject_type === 'field' ? __('Type de fiche') : __('Type'), fn (mixed $value) => $value ? (string) EntityType::find($value)?->name : ''];
        }

        if ($key === 'scenario_id') {
            return [__('Scénario'), fn (mixed $value) => $value ? (string) Scenario::find($value)?->name : ''];
        }

        if ($key === 'character') {
            return [__('Personnage'), fn (mixed $value) => $value ? (string) PlayerCharacter::with('entity')->find($value)?->entity?->name : ''];
        }

        if ($key === 'kind' && $this->subject_type === 'grant') {
            return [__('Nature'), fn (mixed $value) => CharacterGrant::kinds()[$value] ?? $plain($value)];
        }

        if ($key === 'options') {
            return [__('Choix'), fn (mixed $value) => $plain(is_string($value) ? json_decode($value, true) : $value)];
        }

        if ($key === 'image_path') {
            return [__('Image'), fn (mixed $value) => $value ? __('oui') : __('aucune')];
        }

        return [self::labels()[$key] ?? $key, $plain];
    }
}
