<?php

namespace App\Support\Demo;

use App\Models\Attachment;
use App\Models\AudioTrack;
use App\Models\Campaign;
use App\Models\CampaignEntityState;
use App\Models\CampaignInvitation;
use App\Models\Document;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\FeedbackRequest;
use App\Models\FieldDefinition;
use App\Models\GameSystem;
use App\Models\PlayerCharacter;
use App\Models\Rule;
use App\Models\Scenario;
use App\Models\Scene;
use App\Models\Secret;
use App\Models\TableMap;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Models\World;
use App\Support\Plans\Plans;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

/**
 * Campagne de démonstration verrouillée : un compte démo visite sa copie de la démonstration,
 * chaque fonction visible, mais n'en modifie pas le contenu (fiches, scènes, règles, documents,
 * secrets, cartes, champs, invitations…), pour qu'elle ne devienne pas une table de jeu.
 *
 * Restent permis, comme bac à sable : lancer une séance (scènes jouées, notes, événements de la
 * chronologie notés en séance), déplacer les jetons de la carte, la liste « À jouer », les
 * réglages de l'écran de table.
 *
 * Le verrou tient côté serveur, sur les modèles eux-mêmes : quelle que soit la page ou l'action,
 * une écriture dans la démonstration d'un compte démo est refusée (DemoLockedException).
 */
class DemoLock
{
    /** Contenu verrouillé : modèle => colonnes qui le rattachent à une campagne, un monde, un jeu ou une fiche. */
    private const LOCKED = [
        Campaign::class => [],
        World::class => [],
        GameSystem::class => [],
        Entity::class => ['campaign_id', 'world_id'],
        EntityRelation::class => ['campaign_id', 'from_entity_id'],
        Attachment::class => ['entity_id'],
        CampaignEntityState::class => ['campaign_id'],
        CampaignInvitation::class => ['campaign_id'],
        Document::class => ['campaign_id', 'world_id', 'game_system_id'],
        FieldDefinition::class => ['game_system_id'],
        Rule::class => ['campaign_id', 'game_system_id'],
        Scenario::class => ['campaign_id'],
        Scene::class => ['scenario_id'],
        Secret::class => ['campaign_id'],
        TableMap::class => ['campaign_id'],
        TimelineEvent::class => ['campaign_id'],
        AudioTrack::class => ['campaign_id'],
        PlayerCharacter::class => ['campaign_id'],
        FeedbackRequest::class => ['campaign_id'],
    ];

    /** Colonnes qui restent modifiables : l'écran de table de la campagne, l'avancée des scènes en séance. */
    private const SANDBOX_COLUMNS = [
        Campaign::class => ['table_display', 'table_shared', 'table_audio', 'table_theme', 'disabled_features', 'updated_at'],
        Scene::class => ['status', 'updated_at'],
    ];

    private static bool $bypassed = false;

    public static function register(): void
    {
        foreach (array_keys(self::LOCKED) as $class) {
            foreach (['saving', 'deleting'] as $event) {
                Event::listen("eloquent.$event: $class", function (Model $model) use ($event) {
                    self::guard($model, $event === 'deleting');
                });
            }
        }
    }

    /** Écrit dans la démonstration sans le verrou (son chargement). */
    public static function bypass(callable $callback): mixed
    {
        $previous = self::$bypassed;
        self::$bypassed = true;

        try {
            return $callback();
        } finally {
            self::$bypassed = $previous;
        }
    }

    private static function guard(Model $model, bool $deleting): void
    {
        $user = auth()->user();

        // Seul un compte démo peut écrire dans une démonstration verrouillée : c'est la sienne.
        if (self::$bypassed || ! $user instanceof User || Plans::effective($user) !== Plans::DEMO) {
            return;
        }

        if (! $deleting && self::isSandbox($model)) {
            return;
        }

        if (self::touchesLockedScope($model, $user)) {
            throw new DemoLockedException;
        }
    }

    private static function isSandbox(Model $model): bool
    {
        // Un événement noté pendant une séance.
        if ($model instanceof TimelineEvent && ! $model->exists) {
            return $model->play_session_id !== null;
        }

        $columns = self::SANDBOX_COLUMNS[$model::class] ?? null;

        return $columns !== null && $model->exists && array_diff(array_keys($model->getDirty()), $columns) === [];
    }

    private static function touchesLockedScope(Model $model, User $user): bool
    {
        $scope = self::scope($user);

        if ($scope['campaign_id'] === []) {
            return false;
        }

        return match (true) {
            $model instanceof Campaign => in_array($model->getKey(), $scope['campaign_id'], true),
            $model instanceof World => in_array($model->getKey(), $scope['world_id'], true),
            $model instanceof GameSystem => in_array($model->getKey(), $scope['game_system_id'], true),
            default => self::belongsToScope($model, $scope),
        };
    }

    /** @param array<string, list<int>> $scope */
    private static function belongsToScope(Model $model, array $scope): bool
    {
        foreach (self::LOCKED[$model::class] ?? [] as $column) {
            // L'ancienne valeur aussi : on ne sort pas une fiche de la démonstration pour la modifier.
            foreach (array_unique([$model->getAttribute($column), $model->getOriginal($column)]) as $value) {
                if ($value === null) {
                    continue;
                }

                $locked = match ($column) {
                    'campaign_id', 'world_id', 'game_system_id' => in_array((int) $value, $scope[$column], true),
                    'entity_id', 'from_entity_id' => ($parent = Entity::find($value)) !== null && self::belongsToScope($parent, $scope),
                    'scenario_id' => ($parent = Scenario::find($value)) !== null && self::belongsToScope($parent, $scope),
                    default => false,
                };

                if ($locked) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @return array{campaign_id: list<int>, world_id: list<int>, game_system_id: list<int>} campagnes de démonstration du compte, leurs mondes et leurs jeux */
    private static function scope(User $user): array
    {
        $campaigns = Campaign::where('user_id', $user->id)->where('is_demo', true)->get(['id', 'world_id', 'game_system_id']);

        return [
            'campaign_id' => $campaigns->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'world_id' => $campaigns->pluck('world_id')->filter()->map(fn ($id) => (int) $id)->values()->all(),
            'game_system_id' => $campaigns->pluck('game_system_id')->filter()->map(fn ($id) => (int) $id)->values()->all(),
        ];
    }
}
