<?php

namespace App\Support;

use App\Enums\CampaignRole;
use App\Models\Campaign;
use App\Models\CharacterGrant;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Notifications\CampaignEvent;
use Illuminate\Support\Facades\Notification;

/**
 * Envoie les notifications d'une campagne. Chaque texte est écrit pour son destinataire :
 * le joueur n'y lit que ce que son personnage a reçu, jamais la zone MJ.
 */
class Notify
{
    /** Le joueur du personnage, s'il en a un et si ce n'est pas lui qui agit. */
    public static function player(PlayerCharacter $character, string $kind, string $text, string $url): void
    {
        $player = $character->player;

        if ($player === null || $player->id === auth()->id()) {
            return;
        }

        $player->notify(new CampaignEvent($character->campaign, $kind, $text, $url, ['character_id' => $character->id]));
        Live::user($player->id, $kind, $character->campaign_id);
    }

    /** Les MJ de la campagne, sauf celui qui agit. */
    public static function gameMasters(Campaign $campaign, string $kind, string $text, string $url, ?PlayerCharacter $character = null): void
    {
        $gms = $campaign->members()->wherePivot('role', CampaignRole::GameMaster->value)->whereKeyNot(auth()->id() ?? 0)->get();

        Notification::send($gms, new CampaignEvent($campaign, $kind, $text, $url, $character ? ['character_id' => $character->id] : []));
        $gms->each(fn (User $gm) => Live::user($gm->id, $kind, $campaign->id));
    }

    /** Tous les joueurs de la campagne, sauf celui qui agit. */
    public static function players(Campaign $campaign, string $kind, string $text, string $url): void
    {
        $players = $campaign->members()->wherePivot('role', CampaignRole::Player->value)->whereKeyNot(auth()->id() ?? 0)->get();

        Notification::send($players, new CampaignEvent($campaign, $kind, $text, $url));
        $players->each(fn (User $player) => Live::user($player->id, $kind, $campaign->id));
    }

    /** Élément révélé, donné ou repris par le MJ. */
    public static function grant(CharacterGrant $grant, PlayerCharacter $character, bool $revoked = false): void
    {
        $campaign = $character->campaign;
        $label = '« '.$grant->label().' »';

        if ($revoked) {
            self::player($character, 'revoke', 'Le MJ a repris '.$label.'.', route('characters.show', [$campaign, $character]));

            return;
        }

        [$text, $url] = match ($grant->kind) {
            'entity' => ['Le MJ vous a révélé la fiche '.$label.'.', route('characters.entity', [$campaign, $character, $grant->entity_id])],
            'information' => ['Le MJ vous a révélé une information : '.$label.'.', route('characters.show', [$campaign, $character]).'#section-knowledge'],
            'possession' => ['Le MJ vous a donné '.$label.'.', route('characters.show', [$campaign, $character]).'#section-possession'],
            'document' => ['Le MJ vous a donné le document '.$label.'.', route('characters.document', [$campaign, $character, $grant->document_id])],
            'rule' => ['Le MJ vous a ouvert la règle '.$label.'.', route('characters.show', [$campaign, $character]).'#section-rule'],
        };

        self::player($character, 'grant', $text, $url);
    }

    /** Un personnage donne ou transmet quelque chose à un autre : son joueur et le MJ sont prévenus. */
    public static function exchange(CharacterGrant $grant, string $label, PlayerCharacter $from, PlayerCharacter $to): void
    {
        $campaign = $to->campaign;
        $label = '« '.$label.' »';
        $sheet = route('characters.show', [$campaign, $to]);
        $verb = match ($grant->kind) {
            'possession' => 'donné '.$label,
            'entity' => 'montré la fiche '.$label,
            'document' => 'transmis le document '.$label,
            'rule' => 'expliqué la règle '.$label,
            default => 'confié une information : '.$label,
        };

        $url = match ($grant->kind) {
            'entity' => route('characters.entity', [$campaign, $to, $grant->entity_id]),
            'document' => route('characters.document', [$campaign, $to, $grant->document_id]),
            'possession' => $sheet.'#section-possession',
            'rule' => $sheet.'#section-rule',
            default => $sheet.'#section-knowledge',
        };

        self::player($to, 'grant', $from->entity->name.' vous a '.$verb.'.', $url);
        self::gameMasters($campaign, 'grant', $from->entity->name.' a '.$verb.' à '.$to->entity->name.'.', $sheet, $to);
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
}
