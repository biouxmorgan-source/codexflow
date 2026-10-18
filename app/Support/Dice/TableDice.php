<?php

namespace App\Support\Dice;

use App\Enums\CampaignRole;
use App\Models\Campaign;
use App\Models\DiceRequest;
use App\Models\DiceRoll;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Support\CampaignFeatures;
use App\Support\Live;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Les dés de la table. Un lancer « à la table » est vu de tous les membres (et de l'écran de
 * table) ; un lancer discret, seulement du MJ et de celui qui a lancé. Un seuil caché reste chez
 * le MJ. Ces règles s'appliquent ici, côté serveur, jamais par le seul masquage de l'interface.
 */
final class TableDice
{
    public const TABLE = 'table';

    public const GM = 'gm';

    public const MAX_PRESETS = 30;

    /** Durée pendant laquelle l'écran de table montre le dernier lancer à la table. */
    public const SCREEN_SECONDS = 30;

    /**
     * Exemples prêts à l'emploi, sans nom de jeu : de quoi remplir le plateau d'un geste.
     *
     * @return list<array{label: string, formula: array<string, mixed>}>
     */
    public static function examples(): array
    {
        return [
            ['label' => __('Additionner'), 'formula' => ['count' => 2, 'sides' => 6, 'reading' => 'sum']],
            ['label' => __('Atteindre un seuil'), 'formula' => ['count' => 1, 'sides' => 20, 'reading' => 'above', 'threshold' => 15]],
            ['label' => __('Rester sous un seuil'), 'formula' => ['count' => 1, 'sides' => 100, 'reading' => 'below']],
            ['label' => __('Seuil par dé'), 'formula' => ['count' => 6, 'sides' => 6, 'reading' => 'count', 'threshold' => 5, 'explode' => 'up']],
            ['label' => __('Dés mélangés'), 'formula' => ['pool' => [['count' => 1, 'sides' => 20], ['count' => 1, 'sides' => 6]], 'reading' => 'sum']],
            ['label' => __('Garder le meilleur'), 'formula' => ['count' => 2, 'sides' => 20, 'reading' => 'keep', 'keep' => 1]],
            ['label' => __('Lire en chiffres'), 'formula' => ['count' => 3, 'sides' => 6, 'reading' => 'digits']],
            ['label' => __('Ouvert en haut et en bas'), 'formula' => ['count' => 1, 'sides' => 100, 'reading' => 'sum', 'explode' => 'both']],
        ];
    }

    /**
     * Lancers enregistrés : ceux de la table (préparés par le MJ), ou ceux d'une personne pour elle-même.
     *
     * @return list<array{id: string, label: string, formula: array<string, mixed>}>
     */
    public static function presets(Campaign $campaign, ?User $owner = null): array
    {
        if ($owner === null) {
            return self::cleanPresets($campaign->dice_presets ?? []);
        }

        $raw = self::membership($campaign, $owner)->value('dice_presets');

        return self::cleanPresets(is_string($raw) ? json_decode($raw, true) : $raw);
    }

    /**
     * Liste propre (import d'archive, duplication) : des formules vérifiées, sans doublon d'identifiant.
     *
     * @return list<array{id: string, label: string, formula: array<string, mixed>}>
     */
    public static function cleanPresets(mixed $presets): array
    {
        return collect(is_array($presets) ? $presets : [])
            ->filter(fn ($preset) => is_array($preset) && filled($preset['label'] ?? null) && is_array($preset['formula'] ?? null))
            ->map(fn (array $preset) => [
                'id' => is_string($preset['id'] ?? null) && $preset['id'] !== '' ? mb_substr($preset['id'], 0, 20) : Str::random(8),
                'label' => mb_substr(trim((string) $preset['label']), 0, 60),
                'formula' => DiceFormula::fromArray($preset['formula'])->toArray(),
            ])
            ->unique('id')
            ->take(self::MAX_PRESETS)
            ->values()
            ->all();
    }

    /** Enregistre un lancer ; avec $id, met à jour celui qui porte cet identifiant. */
    public static function savePreset(Campaign $campaign, string $label, DiceFormula $formula, ?User $owner = null, ?string $id = null): string
    {
        CampaignFeatures::ensure($campaign, 'dice');
        $presets = self::presets($campaign, $owner);
        $entry = ['label' => mb_substr(trim($label), 0, 60), 'formula' => $formula->toArray()];
        $index = $id === null ? false : array_search($id, array_column($presets, 'id'), true);

        if ($index !== false) {
            $presets[$index] = ['id' => $id] + $entry;
        } else {
            if (count($presets) >= self::MAX_PRESETS) {
                throw ValidationException::withMessages(['presetLabel' => __('Trente lancers enregistrés au plus.')]);
            }
            $presets[] = ['id' => $id = Str::random(8)] + $entry;
        }

        self::storePresets($campaign, $owner, $presets);

        return $id;
    }

    public static function removePreset(Campaign $campaign, string $id, ?User $owner = null): void
    {
        $presets = array_values(array_filter(self::presets($campaign, $owner), fn (array $preset) => $preset['id'] !== $id));
        self::storePresets($campaign, $owner, $presets);
    }

    /** @param list<array<string, mixed>> $presets */
    private static function storePresets(Campaign $campaign, ?User $owner, array $presets): void
    {
        if ($owner === null) {
            $campaign->forceFill(['dice_presets' => $presets === [] ? null : $presets])->save();
            self::notify($campaign, self::everyone($campaign));

            return;
        }

        abort_unless(self::membership($campaign, $owner)->exists(), 403);
        self::membership($campaign, $owner)->update(['dice_presets' => $presets === [] ? null : json_encode($presets)]);
    }

    private static function membership(Campaign $campaign, User $user): \Illuminate\Database\Query\Builder
    {
        return DB::table('campaign_memberships')->where('campaign_id', $campaign->id)->where('user_id', $user->id);
    }

    /** Les personnages qu'une personne joue dans la campagne (pour lancer en leur nom). */
    public static function charactersOf(User $user, Campaign $campaign): Collection
    {
        return $campaign->playerCharacters()->where('user_id', $user->id)->where('is_active', true)->with('entity')->orderBy('id')->get();
    }

    /** Les personnages joués, à qui le MJ peut demander un lancer. */
    public static function playedCharacters(Campaign $campaign): Collection
    {
        return $campaign->playerCharacters()->where('is_active', true)->whereNotNull('user_id')->with('entity')->get()
            ->sortBy(fn (PlayerCharacter $character) => mb_strtolower($character->entity?->name ?? ''))
            ->values();
    }

    /** Lancer libre, ou réponse à une demande : vérifié, tiré, enregistré, annoncé. */
    public static function roll(Campaign $campaign, User $user, DiceFormula $formula, string $visibility = self::TABLE, ?string $label = null, ?PlayerCharacter $character = null, ?DiceRequest $request = null): DiceRoll
    {
        CampaignFeatures::ensure($campaign, 'dice');
        abort_unless(in_array($campaign->roleOf($user), [CampaignRole::GameMaster, CampaignRole::Player], true), 403);
        abort_unless(in_array($visibility, [self::TABLE, self::GM], true), 422);
        abort_if($character !== null && ($character->campaign_id !== $campaign->id || $character->user_id !== $user->id), 403);
        $formula = SheetValues::apply($formula, $campaign, $character);
        $formula->ensureComplete();

        $roll = $campaign->diceRolls()->create([
            'user_id' => $user->id,
            'player_character_id' => $character?->id,
            'dice_request_id' => $request?->id,
            'label' => filled($label) ? mb_substr(trim($label), 0, 100) : null,
            'formula' => $formula->toArray(),
            'result' => app(DiceRoller::class)->roll($formula),
            'visibility' => $visibility,
        ]);

        self::notify($campaign, $visibility === self::TABLE ? self::everyone($campaign) : self::gameMasters($campaign)->push($user->id));

        return $roll;
    }

    /**
     * Le MJ demande un lancer : à toute la table (personnages joués à ce moment) ou à certains.
     *
     * @param  list<int>|null  $characterIds  null : toute la table
     */
    public static function request(Campaign $campaign, User $gm, string $label, DiceFormula $formula, ?array $characterIds, string $visibility = self::TABLE): DiceRequest
    {
        CampaignFeatures::ensure($campaign, 'dice');
        abort_unless($campaign->isGameMaster($gm), 403);
        abort_unless(in_array($visibility, [self::TABLE, self::GM], true), 422);

        if ($formula->hideThreshold && $formula->threshold === null) {
            throw ValidationException::withMessages(['threshold' => __('Un seuil caché doit être indiqué.')]);
        }

        $played = self::playedCharacters($campaign)->pluck('id');
        $targets = $characterIds === null ? $played : $played->intersect(array_map('intval', $characterIds));

        if ($targets->isEmpty()) {
            throw ValidationException::withMessages(['targets' => __('Choisissez au moins un personnage joué.')]);
        }

        $request = $campaign->diceRequests()->create([
            'user_id' => $gm->id,
            'label' => mb_substr(trim($label), 0, 100) ?: $formula->dice(),
            'formula' => $formula->toArray(),
            'visibility' => $visibility,
            'character_ids' => $targets->values()->all(),
        ]);

        self::notify($campaign, self::gameMasters($campaign)->concat(self::usersOf($campaign, $request->characterIds())));

        return $request;
    }

    /** Le joueur répond : une fois par personnage visé, tant que la demande est ouverte. */
    public static function answer(DiceRequest $request, User $user, PlayerCharacter $character, ?int $threshold = null, int $bonus = 0): DiceRoll
    {
        abort_unless($request->isOpen(), 410);
        abort_unless(in_array($character->id, $request->characterIds(), true) && $character->user_id === $user->id, 403);

        if ($request->rolls()->where('player_character_id', $character->id)->exists()) {
            throw ValidationException::withMessages(['answer' => __('Ce personnage a déjà lancé.')]);
        }

        $formula = $request->formula()->completedWith($threshold, max(-999, min(999, $bonus)));
        $roll = self::roll($request->campaign, $user, $formula, $request->visibility, $request->label, $character, $request);

        // Tout le monde a lancé : la demande se range d'elle-même.
        if ($request->rolls()->distinct()->count('player_character_id') >= count($request->characterIds())) {
            $request->forceFill(['closed_at' => now()])->save();
        }
        self::notify($request->campaign, self::gameMasters($request->campaign));

        return $roll;
    }

    public static function close(DiceRequest $request): void
    {
        $request->forceFill(['closed_at' => now()])->save();
        self::notify($request->campaign, self::gameMasters($request->campaign)->concat(self::usersOf($request->campaign, $request->characterIds())));
    }

    /** Lancers qu'une personne a le droit de voir, du plus récent au plus ancien. */
    public static function visibleRolls(Campaign $campaign, User $user, int $limit = 30): Collection
    {
        return $campaign->diceRolls()
            ->when(! $campaign->isGameMaster($user), fn (Builder $query) => $query->where(fn (Builder $q) => $q
                ->where('visibility', self::TABLE)
                ->orWhere('user_id', $user->id)))
            ->with(['character.entity', 'user'])
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public static function canSee(DiceRoll $roll, ?User $user): bool
    {
        if ($user === null) {
            return $roll->visibility === self::TABLE;
        }

        return $roll->campaign->roleOf($user) !== null
            && ($roll->visibility === self::TABLE || $roll->user_id === $user->id || $roll->campaign->isGameMaster($user));
    }

    /** Le seuil caché n'apparaît qu'au MJ. */
    public static function showsThreshold(DiceRoll $roll, bool $isGameMaster): bool
    {
        return $isGameMaster || ! ($roll->formula['hide_threshold'] ?? false);
    }

    /**
     * Ce qu'une personne voit d'un lancer : le seuil caché et la réussite retirés pour un joueur.
     *
     * @return array<string, mixed>
     */
    public static function present(DiceRoll $roll, bool $isGameMaster): array
    {
        $formula = $roll->formula();
        $result = $roll->result;
        $shows = self::showsThreshold($roll, $isGameMaster);

        if (! $shows) {
            $result['success'] = $result['margin'] = $result['threshold'] = null;
        }

        return [
            'id' => $roll->id,
            'who' => $roll->rollerName(),
            'label' => $roll->label,
            'describe' => $formula->describe($shows),
            'sides' => $formula->sides,
            'reading' => $formula->reading,
            'count_high' => $formula->countHigh,
            'modifier' => $formula->modifier,
            'result' => $result,
            'headline' => DiceRoller::headline($result, $shows),
            'discreet' => $roll->visibility === self::GM,
            'at' => $roll->created_at,
        ];
    }

    /** Demandes ouvertes qui attendent un lancer de cette personne : [demande, personnage]. */
    public static function pendingFor(User $user, Campaign $campaign): Collection
    {
        $mine = self::charactersOf($user, $campaign)->keyBy('id');

        if ($mine->isEmpty() || ! CampaignFeatures::usable($campaign, 'dice')) {
            return collect();
        }

        return $campaign->diceRequests()->whereNull('closed_at')->with('rolls:id,dice_request_id,player_character_id')->oldest('id')->get()
            ->flatMap(fn (DiceRequest $request) => collect($request->characterIds())
                ->filter(fn (int $id) => $mine->has($id) && ! $request->rolls->contains('player_character_id', $id))
                ->map(fn (int $id) => ['request' => $request, 'character' => $mine->get($id)]))
            ->values();
    }

    /** Le dernier lancer à la table, montré quelques instants sur l'écran de table. */
    public static function forScreen(Campaign $campaign): ?array
    {
        if (! CampaignFeatures::usable($campaign, 'dice')) {
            return null;
        }

        $roll = $campaign->diceRolls()->where('visibility', self::TABLE)
            ->where('created_at', '>=', now()->subSeconds(self::SCREEN_SECONDS))
            ->with(['character.entity', 'user'])
            ->latest('id')
            ->first();

        return $roll === null ? null : self::present($roll, false);
    }

    /** @return Collection<int, int> */
    private static function everyone(Campaign $campaign): Collection
    {
        return $campaign->members()->pluck('users.id')->push($campaign->user_id)->unique()->values();
    }

    /** @return Collection<int, int> */
    private static function gameMasters(Campaign $campaign): Collection
    {
        return $campaign->members()->wherePivot('role', CampaignRole::GameMaster->value)->pluck('users.id')->values();
    }

    /** @param list<int> $characterIds */
    private static function usersOf(Campaign $campaign, array $characterIds): Collection
    {
        return $campaign->playerCharacters()->whereIn('id', $characterIds)->whereNotNull('user_id')->pluck('user_id');
    }

    private static function notify(Campaign $campaign, Collection $userIds): void
    {
        $userIds->unique()->each(fn (int $id) => Live::user($id, 'dice', $campaign->id));
    }
}
