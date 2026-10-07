<?php

namespace App\Livewire;

use App\Models\CharacterGrant;
use App\Support\Changelog;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;

/**
 * Ce que le personnage du joueur vient de recevoir (révélation, objet, document…), affiché
 * aussitôt sur la page où il se trouve, sans passer par les notifications. Fermer la fenêtre
 * marque la notification comme lue. Seuls les éléments de ses propres personnages sont montrés.
 */
class ReceivedPopup extends Component
{
    /** @return array<string, string> mises à jour en direct (Reverb) */
    public function getListeners(): array
    {
        return ['echo-private:users.'.auth()->id().',.activity' => '$refresh'];
    }

    public function dismiss(string $id): void
    {
        auth()->user()->unreadNotifications()->whereKey($id)->update(['read_at' => now()]);
    }

    public function open(string $id): void
    {
        $notification = auth()->user()->unreadNotifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        $this->redirect($notification->data['url'], navigate: true);
    }

    /**
     * La plus ancienne réception non lue dont l'élément existe encore, avec le nombre restant.
     *
     * @return array{0: ?DatabaseNotification, 1: ?CharacterGrant, 2: int}
     */
    private function next(): array
    {
        $pending = auth()->user()->unreadNotifications()
            ->where('data->kind', 'grant')
            ->whereNotNull('data->grant_id')
            ->reorder('created_at')
            ->limit(20)
            ->get();

        foreach ($pending as $notification) {
            $grant = CharacterGrant::query()
                ->with(['character.entity', 'entity', 'document', 'rule'])
                ->whereHas('character', fn ($q) => $q->where('user_id', auth()->id()))
                ->find($notification->data['grant_id']);

            if ($grant !== null) {
                return [$notification, $grant, $pending->count()];
            }

            // Élément repris ou supprimé entre-temps : rien à montrer.
            $notification->markAsRead();
        }

        return [null, null, 0];
    }

    public function render()
    {
        // La fenêtre « Quoi de neuf » passe d'abord.
        [$notification, $grant, $count] = Changelog::unseenSince(auth()->user()->last_seen_version) === [] ? $this->next() : [null, null, 0];

        return view('livewire.received-popup', compact('notification', 'grant', 'count'));
    }
}
