<?php

namespace App\Actions\Messages;

use App\Actions\Characters\GiveToCharacters;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\User;
use App\Support\Notify;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Envoie un message dans une campagne.
 *
 * - Le MJ écrit à tout le groupe, ou à un ou plusieurs personnages (un message privé par personnage).
 *   Il peut joindre une fiche, un document ou une règle publique : l'élément est révélé aux
 *   personnages destinataires, comme avec « Donner », pour qu'ils puissent l'ouvrir.
 * - Un joueur écrit, au nom de son personnage actif, au MJ en privé ou à tout le groupe,
 *   sans pièce jointe.
 */
class SendMessage
{
    public function __construct(private GiveToCharacters $give) {}

    /**
     * @param  list<int>  $characterIds  personnages destinataires ; vide = tout le groupe
     * @param  array{kind: string, id: int}|null  $reference
     * @return Collection<int, Message>
     */
    public function handle(Campaign $campaign, User $sender, array $characterIds, string $body, ?array $reference = null): Collection
    {
        $isGameMaster = $campaign->isGameMaster($sender);
        abort_unless($isGameMaster || $campaign->roleOf($sender) !== null, 403);

        $senderCharacter = null;

        if (! $isGameMaster) {
            // Le joueur écrit au nom de son personnage : au MJ (sa conversation) ou au groupe.
            abort_unless(count($characterIds) <= 1 && $reference === null, 403);
            $senderCharacter = $characterIds === []
                ? $campaign->playerCharacters()->active()->where('user_id', $sender->id)->first()
                : $campaign->playerCharacters()->whereKey($characterIds[0])->where('user_id', $sender->id)->first();
            abort_if($senderCharacter === null, 403);
        }

        $characters = $campaign->playerCharacters()->whereKey($characterIds)->pluck('id')->all();
        abort_if(count($characters) !== count(array_unique($characterIds)), 404);

        return DB::transaction(function () use ($campaign, $sender, $senderCharacter, $characters, $body, $reference) {
            if ($reference !== null) {
                // Pour le groupe : tous les personnages actifs confiés à un joueur.
                $revealTo = $characters !== [] ? $characters : $campaign->playerCharacters()->active()->whereNotNull('user_id')->pluck('id')->all();

                $this->give->handle($campaign, $revealTo, [
                    'kind' => $reference['kind'],
                    $reference['kind'].'_id' => $reference['id'],
                ]);
            }

            $targets = $characters === [] ? [null] : $characters;

            $messages = collect($targets)->map(function (?int $characterId) use ($campaign, $sender, $senderCharacter, $body, $reference) {
                $message = new Message(['body' => trim($body)]);
                $message->campaign()->associate($campaign);
                $message->sender()->associate($sender);
                $message->player_character_id = $characterId;
                $message->sender_character_id = $senderCharacter?->id;

                if ($reference !== null) {
                    $message->{$reference['kind'].'_id'} = $reference['id'];
                }

                $message->save();
                // L'expéditeur a lu son propre message.
                $message->readers()->attach($sender->id, ['read_at' => now()]);

                return $message;
            });

            $this->notify($campaign, $sender, $messages, $body);

            return $messages;
        });
    }

    /** @param  Collection<int, Message>  $messages */
    private function notify(Campaign $campaign, User $sender, Collection $messages, string $body): void
    {
        $excerpt = Notify::excerpt($body);

        if (! $campaign->isGameMaster($sender)) {
            $message = $messages->first();
            $character = $message->senderCharacter()->with('entity')->first();

            if ($message->isForGroup()) {
                $text = $character->entity->name.' au groupe : '.$excerpt;
                Notify::gameMasters($campaign, 'message', $text, route('messages.index', $campaign));
                Notify::players($campaign, 'message', $text, route('messages.index', $campaign));
            } else {
                Notify::gameMasters($campaign, 'message', $character->entity->name.' : '.$excerpt, route('messages.index', [$campaign, 'personnage' => $character->id]), $character);
            }

            return;
        }

        foreach ($messages as $message) {
            if ($message->isForGroup()) {
                Notify::players($campaign, 'message', 'Message du MJ au groupe : '.$excerpt, route('messages.index', $campaign));
            } else {
                Notify::player($message->character, 'message', 'Message du MJ : '.$excerpt, route('messages.index', $campaign));
            }
        }
    }
}
