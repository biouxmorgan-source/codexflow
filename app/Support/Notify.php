<?php

namespace App\Support;

use App\Enums\CampaignRole;
use App\Models\Campaign;
use App\Models\CharacterGrant;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Notifications\CampaignEvent;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Envoie les notifications d'une campagne. Chaque texte est écrit pour son destinataire :
 * le joueur n'y lit que ce que son personnage a reçu, jamais la zone MJ.
 */
class Notify
{
    /**
     * Le joueur du personnage, s'il en a un et si ce n'est pas lui qui agit.
     *
     * @param  string|Closure(string): string  $text  texte, ou fonction qui l'écrit dans la langue (code) du destinataire
     */
    public static function player(PlayerCharacter $character, string $kind, string|Closure $text, string $url): void
    {
        $player = $character->player;

        if ($player === null || $player->id === auth()->id()) {
            return;
        }

        $player->notify(new CampaignEvent($character->campaign, $kind, self::textFor($text, $player), $url, ['character_id' => $character->id]));
        Live::user($player->id, $kind, $character->campaign_id);
    }

    /**
     * Les MJ de la campagne, sauf celui qui agit.
     *
     * @param  string|Closure(string): string  $text  texte, ou fonction qui l'écrit dans la langue (code) du destinataire
     */
    public static function gameMasters(Campaign $campaign, string $kind, string|Closure $text, string $url, ?PlayerCharacter $character = null): void
    {
        $gms = $campaign->members()->wherePivot('role', CampaignRole::GameMaster->value)->whereKeyNot(auth()->id() ?? 0)->get();

        self::sendEach($gms, $campaign, $kind, $text, $url, $character ? ['character_id' => $character->id] : []);
        $gms->each(fn (User $gm) => Live::user($gm->id, $kind, $campaign->id));
    }

    /**
     * Tous les joueurs de la campagne, sauf celui qui agit.
     *
     * @param  string|Closure(string): string  $text  texte, ou fonction qui l'écrit dans la langue (code) du destinataire
     */
    public static function players(Campaign $campaign, string $kind, string|Closure $text, string $url): void
    {
        $players = $campaign->members()->wherePivot('role', CampaignRole::Player->value)->whereKeyNot(auth()->id() ?? 0)->get();

        self::sendEach($players, $campaign, $kind, $text, $url);
        $players->each(fn (User $player) => Live::user($player->id, $kind, $campaign->id));
    }

    /** Élément révélé, donné ou repris par le MJ. */
    public static function grant(CharacterGrant $grant, PlayerCharacter $character, bool $revoked = false): void
    {
        $campaign = $character->campaign;
        $label = $grant->label();

        if ($revoked) {
            self::player($character, 'revoke', fn (string $locale) => __('Le MJ a repris :label.', ['label' => self::quote($label, $locale)], $locale), route('characters.show', [$campaign, $character]));

            return;
        }

        [$text, $url] = match ($grant->kind) {
            'entity' => [fn (string $locale) => __('Le MJ vous a révélé la fiche :label.', ['label' => self::quote($label, $locale)], $locale), route('characters.entity', [$campaign, $character, $grant->entity_id])],
            'information' => [fn (string $locale) => __('Le MJ vous a révélé une information : :label.', ['label' => self::quote($label, $locale)], $locale), route('characters.show', [$campaign, $character]).'#section-knowledge'],
            'possession' => [fn (string $locale) => __('Le MJ vous a donné :label.', ['label' => self::quote($label, $locale)], $locale), route('characters.show', [$campaign, $character]).'#section-possession'],
            'document' => [fn (string $locale) => __('Le MJ vous a donné le document :label.', ['label' => self::quote($label, $locale)], $locale), route('characters.document', [$campaign, $character, $grant->document_id])],
            'rule' => [fn (string $locale) => __('Le MJ vous a ouvert la règle :label.', ['label' => self::quote($label, $locale)], $locale), route('characters.show', [$campaign, $character]).'#section-rule'],
        };

        self::player($character, 'grant', $text, $url);
    }

    /** Un personnage donne ou transmet quelque chose à un autre : son joueur et le MJ sont prévenus. */
    public static function exchange(CharacterGrant $grant, string $label, PlayerCharacter $from, PlayerCharacter $to): void
    {
        $campaign = $to->campaign;
        $sheet = route('characters.show', [$campaign, $to]);
        $replace = ['giver' => $from->entity->name, 'receiver' => $to->entity->name, 'label' => $label];

        // Message au joueur qui reçoit, puis au MJ, selon la nature de ce qui est transmis.
        [$toPlayer, $toGameMasters] = match ($grant->kind) {
            'possession' => [
                fn (string $locale) => __(':giver vous a donné :label.', self::quoted($replace, $locale), $locale),
                fn (string $locale) => __(':giver a donné :label à :receiver.', self::quoted($replace, $locale), $locale),
            ],
            'entity' => [
                fn (string $locale) => __(':giver vous a montré la fiche :label.', self::quoted($replace, $locale), $locale),
                fn (string $locale) => __(':giver a montré la fiche :label à :receiver.', self::quoted($replace, $locale), $locale),
            ],
            'document' => [
                fn (string $locale) => __(':giver vous a transmis le document :label.', self::quoted($replace, $locale), $locale),
                fn (string $locale) => __(':giver a transmis le document :label à :receiver.', self::quoted($replace, $locale), $locale),
            ],
            'rule' => [
                fn (string $locale) => __(':giver vous a expliqué la règle :label.', self::quoted($replace, $locale), $locale),
                fn (string $locale) => __(':giver a expliqué la règle :label à :receiver.', self::quoted($replace, $locale), $locale),
            ],
            default => [
                fn (string $locale) => __(':giver vous a confié une information : :label.', self::quoted($replace, $locale), $locale),
                fn (string $locale) => __(':giver a confié une information : :label à :receiver.', self::quoted($replace, $locale), $locale),
            ],
        };

        $url = match ($grant->kind) {
            'entity' => route('characters.entity', [$campaign, $to, $grant->entity_id]),
            'document' => route('characters.document', [$campaign, $to, $grant->document_id]),
            'possession' => $sheet.'#section-possession',
            'rule' => $sheet.'#section-rule',
            default => $sheet.'#section-knowledge',
        };

        self::player($to, 'grant', $toPlayer, $url);
        self::gameMasters($campaign, 'grant', $toGameMasters, $sheet, $to);
    }

    /** Le joueur a noté une connaissance ou ajouté un objet : le MJ le voit, et valide l'objet. */
    public static function playerAddition(CharacterGrant $grant, PlayerCharacter $character): void
    {
        $replace = ['name' => $character->entity->name, 'label' => $grant->label()];
        $section = $grant->kind === 'possession' ? 'possession' : 'knowledge';
        $text = $grant->kind === 'possession'
            ? fn (string $locale) => __(':name a ajouté un objet à valider : :label.', self::quoted($replace, $locale), $locale)
            : fn (string $locale) => __(':name a noté une connaissance : :label.', self::quoted($replace, $locale), $locale);

        self::gameMasters($character->campaign, 'grant', $text, route('characters.show', [$character->campaign_id, $character]).'#section-'.$section, $character);
    }

    /** Texte écrit pour un destinataire, dans sa langue. */
    public static function textFor(string|Closure $text, User $recipient): string
    {
        return $text instanceof Closure ? $text(Locale::for($recipient)) : $text;
    }

    /**
     * Une notification par destinataire, chacune dans sa langue.
     *
     * @param  Collection<int, User>  $recipients
     * @param  array<string, int|string>  $extra
     */
    private static function sendEach(Collection $recipients, Campaign $campaign, string $kind, string|Closure $text, string $url, array $extra = []): void
    {
        if (! $text instanceof Closure) {
            Notification::send($recipients, new CampaignEvent($campaign, $kind, $text, $url, $extra));

            return;
        }

        $recipients->each(fn (User $recipient) => $recipient->notify(new CampaignEvent($campaign, $kind, self::textFor($text, $recipient), $url, $extra)));
    }

    public static function excerpt(string $text, int $length = 120): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        return mb_strlen($text) > $length ? mb_substr($text, 0, $length - 1).'…' : $text;
    }

    public static function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /** Titre cité, avec les guillemets de la langue du destinataire. */
    private static function quote(string $text, string $locale): string
    {
        return __('« :text »', ['text' => $text], $locale);
    }

    /** @param  array<string, string>  $replace  la valeur « label » est citée */
    private static function quoted(array $replace, string $locale): array
    {
        return ['label' => self::quote($replace['label'], $locale)] + $replace;
    }
}
